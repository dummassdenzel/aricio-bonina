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

    let leaseDuration = 1;
    let durationType: "months" | "years" = "months";

    function calculateEndDate() {
        const startDate = new Date(formData.start_date);
        const endDate = new Date(startDate);

        if (durationType === "months") {
            endDate.setMonth(endDate.getMonth() + leaseDuration);
        } else {
            endDate.setFullYear(endDate.getFullYear() + leaseDuration);
        }

        formData.end_date = endDate.toISOString().split("T")[0];
    }

    $: if (formData.start_date && (leaseDuration || durationType)) {
        calculateEndDate();
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
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60]"
    >
        <div
            class="bg-white rounded-xl w-[95%] sm:w-[90%] md:w-[85%] lg:w-[75%] xl:w-2/3 max-h-[90vh] overflow-y-auto mx-4"
        >
            <div class="p-4 sm:p-6">
                <!-- header -->
                <div class="flex justify-between items-center border-b mb-6">
                    <h1
                        class="font-inter text-midnight font-semibold text-lg sm:text-xl mb-4"
                    >
                        Tenant Form
                    </h1>
                    <!-- close button -->
                    <button
                        type="button"
                        class="hover:bg-drop p-2 rounded-full mb-4 transition-colors"
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
                        >
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <form on:submit|preventDefault={handleSubmit} class="space-y-6">
                    <!-- unit information -->
                    {#if !preselectedUnit}
                        <div>
                            <h2
                                class="font-inter text-midnight font-semibold text-sm sm:text-base mb-4"
                            >
                                Unit Information
                            </h2>
                            <div class="space-y-2">
                                <p class="text-xs text-muted font-medium">
                                    Unit Number
                                </p>
                                <select
                                    class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                    bind:value={formData.unit_number}
                                >
                                    <option value="">Select Unit</option>
                                    {#each units as unit}
                                        <option
                                            value={unit.unit_number}
                                            disabled={unit.status ===
                                                "occupied"}
                                            selected={unit.unit_number.toString() ===
                                                preselectedUnit}
                                        >
                                            Unit {unit.unit_number}
                                            {unit.status === "occupied"
                                                ? "(occupied)"
                                                : ""}
                                        </option>
                                    {/each}
                                </select>
                            </div>
                        </div>
                    {/if}

                    <div>
                        <p class="text-xs text-muted font-medium mb-2">
                            Move In Date
                        </p>
                        <input
                            type="date"
                            bind:value={formData.move_in_date}
                            class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                        />
                    </div>

                    <!-- tenant information -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2
                                class="font-inter text-midnight font-semibold text-sm sm:text-base"
                            >
                                Tenant Information
                            </h2>
                            <button
                                type="button"
                                class="text-slate font-semibold text-xs hover:text-teal transition-colors"
                                on:click={addTenant}
                            >
                                + add more
                            </button>
                        </div>

                        <div class="space-y-4">
                            {#each formData.tenants as tenant, index}
                                <div class="bg-back p-4 rounded-lg">
                                    <div
                                        class="flex justify-between items-center mb-4"
                                    >
                                        <h3
                                            class="text-xs text-slate font-inter font-semibold"
                                        >
                                            Tenant {index + 1}
                                        </h3>
                                        {#if formData.tenants.length > 1}
                                            <button
                                                type="button"
                                                class="text-xs text-red font-semibold hover:text-red/80 transition-colors"
                                                on:click={() =>
                                                    removeTenant(index)}
                                            >
                                                Remove
                                            </button>
                                        {/if}
                                    </div>

                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium mb-2"
                                            >
                                                First Name
                                            </p>
                                            <input
                                                type="text"
                                                bind:value={tenant.first_name}
                                                class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                            />
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium mb-2"
                                            >
                                                Last Name
                                            </p>
                                            <input
                                                type="text"
                                                bind:value={tenant.last_name}
                                                class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                            />
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium mb-2"
                                            >
                                                Contact Number
                                            </p>
                                            <input
                                                type="text"
                                                bind:value={tenant.phone_number}
                                                class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                            />
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium mb-2"
                                            >
                                                Email
                                            </p>
                                            <input
                                                type="email"
                                                bind:value={tenant.email}
                                                class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                            />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <p
                                                class="text-xs text-muted font-medium mb-2"
                                            >
                                                Valid ID (JPG, PNG, WebP, or
                                                PDF, max 5MB)
                                            </p>
                                            <input
                                                type="file"
                                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                                on:change={(e) =>
                                                    handleFileUpload(e, index)}
                                                class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                            />
                                            {#if formData.tenants[index].valid_id}
                                                <p
                                                    class="text-xs text-green mt-1"
                                                >
                                                    File selected: {formData
                                                        .tenants[index].valid_id
                                                        .name}
                                                </p>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>

                    <!-- lease information -->
                    <div>
                        <h2
                            class="font-inter text-midnight font-semibold text-sm sm:text-base mb-4"
                        >
                            Lease Information
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-muted font-medium mb-2">
                                    Start of Lease
                                </p>
                                <input
                                    type="date"
                                    bind:value={formData.start_date}
                                    class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                />
                            </div>

                            <div>
                                <p class="text-xs text-muted font-medium mb-2">
                                    Lease Duration
                                </p>
                                <div class="flex gap-3">
                                    <input
                                        type="number"
                                        min="1"
                                        bind:value={leaseDuration}
                                        class="w-24 border p-2 rounded-lg text-sm font-medium text-teal font-inter"
                                    />
                                    <select
                                        bind:value={durationType}
                                        class="border p-2 rounded-lg text-sm font-medium text-teal font-inter"
                                    >
                                        <option value="months">Month(s)</option>
                                        <option value="years">Year(s)</option>
                                    </select>
                                </div>
                                <p class="text-xs text-muted mt-2">
                                    Lease will end on: <span
                                        class="font-medium text-teal"
                                        >{formData.end_date}</span
                                    >
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs text-muted font-medium mb-2">
                                    Rent Amount
                                </p>
                                <input
                                    type="number"
                                    placeholder="PHP"
                                    step="0.01"
                                    bind:value={formData.rent_amount}
                                    class="w-full appearance-none border rounded-lg text-xs p-3 text-slate font-medium leading-tight focus:outline-backdrop"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- action buttons -->
                    <div
                        class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t"
                    >
                        <button
                            type="button"
                            class="w-full sm:w-auto px-8 py-3 text-xs font-inter text-red bg-red20 border border-red rounded-lg hover:bg-red10 transition-colors"
                            on:click={handleClose}
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="w-full sm:w-auto px-8 py-3 text-xs font-inter text-green bg-green20 border border-green rounded-lg hover:bg-green10 transition-colors"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/if}
