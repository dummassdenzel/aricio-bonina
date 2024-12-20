<script lang="ts">
    import { formatDate } from "$lib/pipes/date-pipe";
    import { api } from "$lib/services/api";
    import Swal from "sweetalert2";
    import { unitsStore } from "$lib/stores/units-store";
    import { dashboardStore } from "$lib/stores/dashboard-store";
    import TenantFormModal from "./tenant-form-modal.svelte";

    export let isOpen: boolean = false;
    export let onClose: () => void;
    export let unitNumber: number;

    type Tenant = {
        first_name: string;
        last_name: string;
        move_in_date: string;
    };

    type Lease = {
        id: number;
        date_renewed: string | null;
        start_date: string;
        end_date: string;
        rent_amount: string;
        tenants: Tenant[];
    };

    type Unit = {
        id: number;
        unit_number: number;
        floor: number;
        status: string;
        price: string;
        unit_description: string;
        unit_furniture: string;
        image_url: string;
        max_occupants: number;
        current_lease?: Lease;
    };

    let unit: Unit | null = null;
    let loading = false;
    let error: string | null = null;
    let formData = {
        lease_id: null as number | null,
        start_date: new Date().toISOString().split("T")[0],
        end_date: "",
        rent_amount: 0,
    };
    let isSubmitting = false;

    $: if (unit && unit.current_lease) {
        formData = {
            lease_id: unit.current_lease.id,
            start_date: new Date().toISOString().split("T")[0],
            end_date: "",
            rent_amount: 0,
        };
    }

    $: if (isOpen) {
        loadUnit();
    }

    async function loadUnit() {
        loading = true;
        error = null;
        try {
            const response = await api.get(`units/${unitNumber}`);
            unit = response.payload[0];
            console.log("Loaded unit:", unit);
        } catch (err: any) {
            error = err.message;
        } finally {
            loading = false;
        }
    }

    const handleKeydown = (e: KeyboardEvent) => {
        if (e.key === "Escape") onClose();
    };

    const validateLeaseDates = (): string[] => {
        const errors = [];
        const start = new Date(formData.start_date);
        const end = new Date(formData.end_date);

        if (!formData.start_date) {
            errors.push("Start date is required");
        }
        if (!formData.end_date) {
            errors.push("End date is required");
        }
        if (start >= end) {
            errors.push("End date must be after start date");
        }
        if (formData.rent_amount <= 0) {
            errors.push("Rent amount must be greater than 0");
        }

        return errors;
    };

    const renewLease = async () => {
        const validationErrors = validateLeaseDates();
        if (validationErrors.length > 0) {
            await Swal.fire({
                title: "Validation Error",
                html: validationErrors.join("<br>"),
                icon: "error",
            });
            return;
        }

        isSubmitting = true;

        try {
            const response = await api.post("renewlease", formData);

            await Swal.fire({
                title: "Success!",
                text: "Lease has been renewed successfully",
                icon: "success",
            });

            await Promise.all([
                unitsStore.loadUnits(),
                dashboardStore.loadStats(),
            ]);

            await loadUnit();
            closeRenewLeaseModal();
        } catch (error: any) {
            await Swal.fire({
                title: "Error",
                text: error.message || "Failed to renew lease",
                icon: "error",
            });
            console.error("Error renewing lease:", error);
        } finally {
            isSubmitting = false;
        }
    };

    let isRenewLeaseOpen: boolean = false;

    const openRenewLeaseModal = () => {
        if (unit?.current_lease) {
            formData = {
                lease_id: unit.current_lease.id,
                start_date: new Date().toISOString().split("T")[0],
                end_date: "",
                rent_amount: parseFloat(unit.current_lease.rent_amount) || 0,
            };
        }
        isRenewLeaseOpen = true;
    };

    const closeRenewLeaseModal = () => {
        isRenewLeaseOpen = false;
        isSubmitting = false;
    };

    const endLease = async () => {
        const result = await Swal.fire({
            title: "Are you sure?",
            text: "This will permanently remove the lease and all tenant information. This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, end lease",
        });

        if (!result.isConfirmed) return;

        try {
            console.log("Ending lease:", unit?.current_lease?.id);
            const response = await api.post("endlease", {
                lease_id: unit?.current_lease?.id,
            });

            await Swal.fire({
                title: "Success!",
                text: "Lease has been ended successfully",
                icon: "success",
            });

            await Promise.all([
                unitsStore.loadUnits(),
                dashboardStore.loadStats(),
            ]);

            onClose();
        } catch (error: any) {
            await Swal.fire({
                title: "Error",
                text: error.message || "Failed to end lease",
                icon: "error",
            });
            console.error("Error ending lease:", error);
        }
    };

    let showTenantForm = false;

    const openTenantForm = () => {
        showTenantForm = true;
    };

    const closeTenantForm = () => {
        showTenantForm = false;
    };

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

        // Subtract one day to get the last day of the lease
        endDate.setDate(endDate.getDate() - 1);

        formData.end_date = endDate.toISOString().split("T")[0];
    }

    $: if (formData.start_date && (leaseDuration || durationType)) {
        calculateEndDate();
    }
</script>

<svelte:window on:keydown={handleKeydown} />

{#if isOpen}
    <div class="fixed inset-0 z-50">
        <!-- svelte-ignore element_invalid_self_closing_tag -->
        <button
            class="absolute inset-0 w-full h-full bg-black/60 backdrop-blur-sm"
            aria-label="close modal"
            on:click={onClose}
        />

        {#if loading}
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white"
            >
                Loading...
            </div>
        {:else if error}
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-red p-4 bg-red20 rounded-lg"
            >
                {error}
            </div>
        {:else if unit}
            <!-- unit modal -->
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[95%] sm:w-[90%] md:w-[85%] max-w-xl mx-auto rounded-xl shadow-lg overflow-y-auto max-h-[90vh]"
            >
                <div class="p-4 sm:p-6">
                    <!-- header section -->
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p
                                class="font-semibold text-xs sm:text-sm text-slate font-inter"
                            >
                                F{unit.floor}
                            </p>
                            <h2
                                id="modal-title"
                                class="text-xl sm:text-2xl font-bold font-inter text-teal"
                            >
                                Unit {unit.unit_number}
                            </h2>
                        </div>
                        <button
                            type="button"
                            class="p-2 hover:bg-drop rounded-full transition-colors"
                            aria-label="close modal"
                            on:click={onClose}
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

                    <hr class="mb-6" />

                    <!-- lease information -->
                    {#if unit.current_lease}
                        <div class="mb-6">
                            {#if new Date(unit.current_lease.end_date) < new Date()}
                                <div
                                    class="flex justify-center flex-col text-center bg-red20 rounded-md p-2 mb-6"
                                >
                                    <p class="text-red text-xs sm:text-sm">
                                        Notice: This lease is overdue!
                                    </p>
                                </div>
                            {/if}
                            <h3
                                class="text-lg sm:text-xl font-inter font-bold mb-6 text-center text-slate"
                            >
                                Lease Information
                            </h3>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-10 bg-back p-4 rounded-lg"
                            >
                                <div>
                                    <p class="text-xs text-muted font-medium">
                                        Start Date
                                    </p>
                                    <p
                                        class="text-sm text-teal font-semibold font-inter"
                                    >
                                        {formatDate(
                                            unit.current_lease.start_date,
                                        )}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted font-medium">
                                        End Date
                                    </p>
                                    <p
                                        class="text-sm text-teal font-semibold font-inter"
                                    >
                                        {formatDate(
                                            unit.current_lease.end_date,
                                        )}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted font-medium">
                                        Monthly Rent
                                    </p>
                                    <p
                                        class="text-sm text-teal font-semibold font-inter"
                                    >
                                        {unit.current_lease.rent_amount
                                            ? `₱${unit.current_lease.rent_amount.toLocaleString()}`
                                            : "Not specified"}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- tenant information -->
                        <div class="border-t pt-6">
                            <h3
                                class="text-lg sm:text-xl font-inter font-bold mb-6 text-slate text-center"
                            >
                                Tenants
                            </h3>
                            <div class="space-y-3">
                                {#each unit.current_lease.tenants as tenant}
                                    <div
                                        class="bg-back p-4 rounded-lg grid grid-cols-1 sm:grid-cols-2 gap-3"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium"
                                            >
                                                Name
                                            </p>
                                            <p
                                                class="text-sm text-teal font-semibold font-inter"
                                            >
                                                {tenant.first_name}
                                                {tenant.last_name}
                                            </p>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs text-muted font-medium"
                                            >
                                                Tenant Since
                                            </p>
                                            <p
                                                class="text-sm text-teal font-semibold font-inter"
                                            >
                                                {formatDate(
                                                    tenant.move_in_date,
                                                )}
                                            </p>
                                        </div>
                                    </div>
                                {/each}
                            </div>

                            <!-- action buttons -->
                            <div
                                class="flex flex-col sm:flex-row justify-between gap-3 mt-6 pt-6 border-t"
                            >
                                <button
                                    class="text-xs font-medium bg-green20 text-green p-3 sm:p-4 font-inter w-full sm:w-72 rounded-lg hover:bg-lightteal hover:text-teal transition-colors"
                                    on:click={openRenewLeaseModal}
                                >
                                    Renew Lease
                                </button>

                                <button
                                    class="text-xs font-medium bg-red20 text-red p-3 sm:p-4 font-inter w-full sm:w-72 rounded-lg hover:bg-lightteal hover:text-teal transition-colors"
                                    on:click={endLease}
                                >
                                    End Lease
                                </button>
                            </div>
                        </div>
                    {:else}
                        <div class="text-center">
                            <p class="text-muted font-medium text-sm mb-4">
                                This unit is currently vacant.
                            </p>
                            <button
                                class="text-xs font-medium bg-green20 text-green p-3 sm:p-4 font-inter w-full max-w-sm rounded-lg hover:bg-lightteal hover:text-teal transition-colors"
                                on:click={openTenantForm}
                            >
                                Add Tenant
                            </button>
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
{/if}

<!-- renew lease modal -->
{#if isRenewLeaseOpen}
    <div class="fixed inset-0 z-50">
        <!-- svelte-ignore element_invalid_self_closing_tag -->
        <button
            class="absolute inset-0 w-full h-full bg-black/60 backdrop-blur-sm"
            aria-label="close modal"
            on:click={closeRenewLeaseModal}
            disabled={isSubmitting}
        />

        <div
            role="dialog"
            aria-modal="true"
            class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[95%] sm:w-[90%] max-w-md mx-auto rounded-xl shadow-lg p-4 sm:p-6"
        >
            <h2
                class="text-xl sm:text-2xl font-inter font-bold mb-6 text-slate text-center"
            >
                Renew Lease
            </h2>

            <form on:submit|preventDefault={renewLease} class="space-y-4">
                <div>
                    <p class="text-xs text-muted font-medium mb-2">
                        Start Date
                    </p>
                    <input
                        type="date"
                        bind:value={formData.start_date}
                        min={new Date().toISOString().split("T")[0]}
                        disabled={isSubmitting}
                        class="w-full border p-2 rounded-lg text-sm font-medium text-teal font-inter"
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
                        Lease will end on: <span class="font-medium text-teal"
                            >{formData.end_date}</span
                        >
                    </p>
                </div>

                <div>
                    <p class="text-xs text-muted font-medium mb-2">
                        Rent Amount
                    </p>
                    <input
                        type="number"
                        bind:value={formData.rent_amount}
                        min="0"
                        step="0.01"
                        disabled={isSubmitting}
                        class="w-full border p-2 rounded-lg text-sm font-medium text-teal font-inter"
                    />
                </div>

                <div
                    class="flex flex-col sm:flex-row justify-between gap-3 pt-4"
                >
                    <button
                        type="submit"
                        class="text-xs font-medium bg-green20 text-green p-3 sm:p-4 font-inter w-full sm:w-72 rounded-lg hover:bg-lightteal hover:text-teal transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? "Renewing..." : "Renew Lease"}
                    </button>
                    <button
                        type="button"
                        class="text-xs font-medium bg-red20 text-red p-3 sm:p-4 font-inter w-full sm:w-72 rounded-lg hover:bg-lightteal hover:text-teal transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        on:click={closeRenewLeaseModal}
                        disabled={isSubmitting}
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}

<!-- Add tenant form modal -->
{#if showTenantForm}
    <TenantFormModal
        isOpen={showTenantForm}
        onClose={closeTenantForm}
        preselectedUnit={unitNumber.toString()}
        onParentClose={onClose}
    />
{/if}
