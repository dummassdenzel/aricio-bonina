<script lang="ts">
    import { api } from "$lib/services/api";
    import swal from "sweetalert2";
    import { unitsStore } from "$lib/stores/units-store";
    import { dashboardStore } from "$lib/stores/dashboard-store";
    import { tenantsStore } from "$lib/stores/tenants-store";

    // Props
    export let isOpen: boolean = false;
    export let onClose: () => void;
    export let preselectedUnit: string | null = null;
    export let onParentClose: (() => void) | null = null;

    let units: any[] = [];
    let error: string | null = null;

    function getTodayDate() {
        return new Date().toISOString().split("T")[0];
    }

    // Initial form state
    const initialFormData = {
        unit_number: preselectedUnit || "",
        move_in_date: getTodayDate(),
        start_date: getTodayDate(),
        end_date: "",
        rent_amount: "",
        tenants: [
            {
                first_name: "",
                last_name: "",
                phone_number: "",
                email: "",
                valid_id: null,
            },
        ],
    };

    interface TenantFormData {
        unit_number: string;
        move_in_date: string;
        start_date: string;
        end_date: string;
        rent_amount: string;
        tenants: Tenant[];
    }

    let formData: TenantFormData = { ...initialFormData };

    // Watch for changes to preselectedUnit
    $: if (preselectedUnit) {
        formData.unit_number = preselectedUnit;
    }

    // Reset form function
    function resetForm() {
        formData = {
            ...initialFormData,
            unit_number: preselectedUnit || "",
            move_in_date: getTodayDate(),
            start_date: getTodayDate(),
        };
    }

    // Modified close handler
    function handleClose() {
        resetForm();
        onClose();
    }

    // Watch for modal open/close
    $: if (isOpen) {
        loadUnits();
        // Set preselected unit if provided
        if (preselectedUnit) {
            formData.unit_number = preselectedUnit;
        }
    }

    function addTenant() {
        formData.tenants = [
            ...formData.tenants,
            {
                first_name: "",
                last_name: "",
                phone_number: "",
                email: "",
                valid_id: null,
            },
        ];
    }

    function removeTenant(index: number) {
        formData.tenants = formData.tenants.filter((_, i) => i !== index);
    }

    async function loadUnits() {
        try {
            const response = await api.get("units");
            units = response.payload;
        } catch (err: any) {
            error = err.message;
        }
    }

    async function handleSubmit(event: SubmitEvent) {
        event.preventDefault();

        const isValid = await validateForm();
        if (!isValid) return;

        try {
            const formDataToSend = new FormData();

            // Add basic form fields
            formDataToSend.append("unit_number", formData.unit_number);
            formDataToSend.append("move_in_date", formData.move_in_date);
            formDataToSend.append("start_date", formData.start_date);
            formDataToSend.append("end_date", formData.end_date);
            formDataToSend.append(
                "rent_amount",
                formData.rent_amount.toString(),
            );

            // Add tenant data as JSON string to maintain structure
            formData.tenants.forEach((tenant, index) => {
                // Add tenant data as individual fields
                Object.entries(tenant).forEach(([key, value]) => {
                    if (key !== "valid_id") {
                        formDataToSend.append(
                            `tenants[${index}][${key}]`,
                            value.toString(),
                        );
                    }
                });

                // Add file if it exists
                if (tenant.valid_id instanceof File) {
                    formDataToSend.append(
                        `valid_id_${tenant.first_name}`,
                        tenant.valid_id,
                    );
                }
            });

            console.log("FormData entries:");
            for (let pair of formDataToSend.entries()) {
                console.log(pair[0] + ": " + pair[1]);
            }

            const response = await api.postFormData(
                "addtenant",
                formDataToSend,
            );

            if (response.status.remarks === "success") {
                await swal.fire({
                    title: "Success!",
                    text: response.status.message,
                    icon: "success",
                    confirmButtonText: "OK",
                });

                await Promise.all([
                    unitsStore.loadUnits(),
                    tenantsStore.loadTenants(),
                    dashboardStore.loadStats(),
                ]);

                resetForm();
                onClose();
                if (onParentClose) onParentClose();
            } else {
                throw new Error(response.status.message);
            }
        } catch (err: any) {
            await swal.fire({
                title: "Error",
                text: err.message || "An error occurred",
                icon: "error",
                confirmButtonText: "OK",
            });
        }
    }

    async function validateForm(): Promise<boolean> {
        let errors = [];

        if (!formData.unit_number) {
            errors.push("Unit number is required");
        }

        if (!formData.move_in_date) {
            errors.push("Move-in date is required");
        }

        if (!formData.start_date) {
            errors.push("Lease start date is required");
        }

        if (!formData.end_date) {
            errors.push("Lease end date is required");
        }

        if (formData.start_date && formData.end_date) {
            const start = new Date(formData.start_date);
            const end = new Date(formData.end_date);
            if (end <= start) {
                errors.push("Lease end date must be after start date");
            }
        }

        if (!formData.rent_amount) {
            errors.push("Rent amount is required");
        } else if (parseFloat(formData.rent_amount) <= 0) {
            errors.push("Rent amount must be greater than 0");
        }

        if (formData.tenants.length === 0) {
            errors.push("At least one tenant is required");
        }

        formData.tenants.forEach((tenant, index) => {
            if (!tenant.first_name.trim() || !tenant.last_name.trim()) {
                errors.push(
                    `Tenant ${index + 1}: Both first and last name are required`,
                );
            }
        });

        if (errors.length > 0) {
            await swal.fire({
                title: "Validation Error",
                html: errors.map((err) => `• ${err}`).join("<br>"),
                icon: "error",
                confirmButtonText: "OK",
            });
            return false;
        }

        return true;
    }

    interface Tenant {
        first_name: string;
        last_name: string;
        phone_number: string;
        email: string;
        valid_id: File | null;
    }

    const ALLOWED_FILE_TYPES = [
        "image/jpeg",
        "image/png",
        "image/webp",
        "application/pdf",
    ];
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    function handleFileUpload(event: Event, index: number) {
        const input = event.target as HTMLInputElement;
        const file = input.files?.[0];

        if (!file) return;

        // Validate file type
        if (!ALLOWED_FILE_TYPES.includes(file.type)) {
            swal.fire({
                title: "Invalid File Type",
                text: "Please upload a JPG, PNG, WebP image or PDF file",
                icon: "error",
            });
            input.value = "";
            return;
        }

        // Validate file size
        if (file.size > MAX_FILE_SIZE) {
            swal.fire({
                title: "File Too Large",
                text: "File size should be less than 5MB",
                icon: "error",
            });
            input.value = "";
            return;
        }

        // Create a new tenant object with the updated valid_id
        const updatedTenant = {
            ...formData.tenants[index],
            valid_id: file,
        };

        // Update the tenants array
        formData.tenants[index] = updatedTenant;
    }
</script>

{#if isOpen}
    <form
        class="fixed inset-0 bg-muted bg-opacity-40 flex items-center justify-center z-[60]"
        on:submit|preventDefault={handleSubmit}
    >
        <div class="bg-white p-6 rounded-lg w-1/3 max-h-[80vh] overflow-y-auto">
            <!-- tenant form -->
            <div class="flex justify-between items-center border-b mb-6">
                <h1 class="font-inter text-midnight font-semibold text-xl mb-4">
                    Tenant Form
                </h1>
                <!-- close button -->
                <button
                    type="button"
                    class="hover:bg-drop p-2 rounded-full mb-4"
                    aria-label="close modal"
                    on:click={handleClose}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#686868"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        ><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg
                    >
                </button>
            </div>

            <!-- unit information -->
            {#if !preselectedUnit}
                <div>
                    <h1 class="font-inter text-midnight font-semibold text-sm">
                        Unit Information
                    </h1>
                    <p class="text-xs text-muted font-medium mt-2 mb-2">
                        Unit Number
                    </p>
                    <select
                        class="mb-4 appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                        id="unit_number"
                        bind:value={formData.unit_number}
                    >
                        <option value="">Select Unit</option>
                        {#each units as unit}
                            <option
                                value={unit.unit_number}
                                disabled={unit.status === "occupied"}
                                selected={unit.unit_number.toString() ===
                                    preselectedUnit}
                            >
                                Unit {unit.unit_number}
                                {unit.status === "occupied" ? "(occupied)" : ""}
                            </option>
                        {/each}
                    </select>
                </div>
            {/if}

            <div class="mb-2">
                <p class="text-xs text-muted font-medium mb-2">Move In Date</p>
                <input
                    class="font-medium appearance-none border rounded-lg text-xs w-full p-3 text-slate leading-tight focus:outline-backdrop"
                    type="date"
                    bind:value={formData.move_in_date}
                />
            </div>

            <!-- tenant information -->
            <div class="mt-6">
                <div class="flex justify-between">
                    <h1 class="font-inter text-midnight font-semibold text-sm">
                        Tenant Information
                    </h1>
                    <button
                        class="text-slate font-semibold text-xs"
                        type="button"
                        on:click={addTenant}
                    >
                        + add more
                    </button>
                </div>

                <div class=" mt-2 flex flex-col gap-2">
                    {#each formData.tenants as tenant, index}
                        <div class="bg-back p-4 rounded-lg">
                            <!-- tenant count -->
                            <div class="flex justify-between items-center">
                                <h4
                                    class="text-xs text-slate font-inter font-semibold mb-2"
                                >
                                    Tenant {index + 1}
                                </h4>
                                {#if formData.tenants.length > 1}
                                    <button
                                        class="text-xs text-slate font-semibold"
                                        type="button"
                                        on:click={() => removeTenant(index)}
                                    >
                                        - Remove
                                    </button>
                                {/if}
                            </div>
                            <!-- end of tenant count -->

                            <!-- tenant details  -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p
                                        class="text-xs text-muted font-medium mb-2"
                                    >
                                        First Name
                                    </p>
                                    <input
                                        class="appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                        type="text"
                                        bind:value={tenant.first_name}
                                    />
                                </div>

                                <div>
                                    <p
                                        class="text-xs text-muted font-medium mb-2"
                                    >
                                        Last Name
                                    </p>
                                    <input
                                        class="appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                        type="text"
                                        bind:value={tenant.last_name}
                                    />
                                </div>

                                <div class="mt-2">
                                    <p
                                        class="text-xs text-muted font-medium mb-2"
                                    >
                                        Contact Number
                                    </p>
                                    <input
                                        class="appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                        type="text"
                                        bind:value={tenant.phone_number}
                                    />
                                </div>

                                <div class="mt-2">
                                    <p
                                        class="text-xs text-muted font-medium mb-2"
                                    >
                                        Email
                                    </p>
                                    <input
                                        class="appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                        type="text"
                                        bind:value={tenant.email}
                                    />
                                </div>

                                <div class="col-span-2 mt-2">
                                    <p
                                        class="text-xs text-muted font-medium mb-2"
                                    >
                                        Valid ID (JPG, PNG, WebP, or PDF, max
                                        5MB)
                                    </p>
                                    <input
                                        class="appearance-none border rounded-lg text-xs flex items-center w-full p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                                        on:change={(e) =>
                                            handleFileUpload(e, index)}
                                    />
                                    {#if formData.tenants[index].valid_id}
                                        <p class="text-xs text-green mt-1">
                                            File selected: {formData.tenants[
                                                index
                                            ].valid_id.name}
                                        </p>
                                    {/if}
                                </div>
                            </div>
                            <!-- end of tenant details -->
                        </div>
                    {/each}
                </div>
            </div>

            <!-- lease information -->
            <div class="mt-6">
                <h1 class="font-inter text-midnight font-semibold text-sm">
                    Lease Information
                </h1>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-xs text-muted font-medium mt-2 mb-2">
                            Start of Lease
                        </p>
                        <input
                            class="font-medium appearance-none border rounded-lg text-xs w-full p-3 text-slate leading-tight focus:outline-backdrop"
                            type="date"
                            bind:value={formData.start_date}
                        />
                    </div>

                    <div>
                        <p class="text-xs text-muted font-medium mt-2 mb-2">
                            End of Lease
                        </p>
                        <input
                            class="font-medium appearance-none border rounded-lg text-xs w-full p-3 text-slate leading-tight focus:outline-backdrop"
                            type="date"
                            bind:value={formData.end_date}
                        />
                    </div>

                    <div class="mb-4 col-span-2">
                        <p class="text-xs text-muted font-medium mt-2 mb-2">
                            Rent Amount
                        </p>
                        <input
                            class="font-medium appearance-none border rounded-lg text-xs w-full p-3 text-slate leading-tight focus:outline-backdrop"
                            placeholder="PHP"
                            type="number"
                            step="0.01"
                            bind:value={formData.rent_amount}
                        />
                    </div>
                </div>
            </div>

            <!-- buttons -->
            <div class="flex justify-between gap-2 mt-6">
                <button
                    class="p-4 w-full text-xs font-inter text-red bg-red20 border border-red rounded-lg"
                    type="button"
                    on:click={handleClose}
                >
                    Cancel
                </button>

                <button
                    class="p-4 w-full text-xs font-inter text-green bg-green20 border border-green rounded-lg"
                    type="submit"
                >
                    Save
                </button>
            </div>
            <!-- end of buttons -->
        </div>
    </form>
{/if}
