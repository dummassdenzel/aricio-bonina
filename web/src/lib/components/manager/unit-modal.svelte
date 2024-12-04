<script lang="ts">
    import { formatDate } from "$lib/pipes/date-pipe";
    import { api } from "$lib/services/api";

    export let isOpen: boolean = false;
    export let onClose: () => void;
    export let unit: {
        unit_number: number;
        floor: number;
        current_lease?: {
            id: number;
            tenants: Array<{
                first_name: string;
                last_name: string;
                move_in_date: string;
            }>;
            start_date: string;
            end_date: string;
            rent_amount: number;
        };
    };

    let formData = {
        lease_id: unit?.current_lease?.id,
        start_date: new Date().toISOString().split("T")[0],
        end_date: "",
        rent_amount: 0,
    };

    const handleKeydown = (e: KeyboardEvent) => {
        if (e.key === "Escape") onClose();
    };

    const renewLease = async () => {
        try {
            const response = await api.post("renewlease", formData);
            console.log("Lease renewed successfully:", response);
        } catch (error) {
            console.error("Error renewing lease:", error);
        }
    };

    let isRenewLeaseOpen: boolean = false;

    const openRenewLeaseModal = () => {
        isRenewLeaseOpen = true;
    };

    const closeRenewLeaseModal = () => {
        isRenewLeaseOpen = false;
    };
</script>

<svelte:window on:keydown={handleKeydown} />

{#if isOpen}
    <div class="fixed inset-0 z-50">
        <button
            class="absolute inset-0 w-full h-full bg-black bg-opacity-60"
            aria-label="close modal"
            on:click={onClose}
        >
        </button>

        <!-- unit modal -->
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white backdrop-blur-xs rounded-xl p-6 w-full max-w-xl mx-4"
        >
            <!-- unit number and floor number, close button -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="font-semibold text-sm text-slate font-inter">
                        F{unit.floor}
                    </p>
                    <h2
                        id="modal-title"
                        class="text-2xl font-bold font-inter text-teal"
                    >
                        Unit {unit.unit_number}
                    </h2>
                </div>
                <button
                    type="button"
                    class="hover:bg-drop p-2 rounded-full"
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
                        <path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg
                    >
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
                            <p class="text-red text-sm">
                                Notice: This lease is expired!
                            </p>
                        </div>
                    {/if}
                    <h3
                        class="text-xl font-inter font-bold mb-6 text-center text-slate"
                    >
                        Lease Information
                    </h3>
                    <div class="grid grid-cols-3 gap-10 bg-back p-4 rounded-lg">
                        <div>
                            <p class="text-xs text-muted font-medium">
                                Start Date
                            </p>
                            <p
                                class="text-sm text-teal font-semibold font-inter"
                            >
                                {formatDate(unit.current_lease.start_date)}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted font-medium">
                                End Date
                            </p>
                            <p
                                class="text-sm text-teal font-semibold font-inter"
                            >
                                {formatDate(unit.current_lease.end_date)}
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
                <div class="border-t">
                    <h3
                        class="text-xl font-inter font-bold mb-6 mt-6 text-slate text-center"
                    >
                        Tenants
                    </h3>
                    <div class="space-y-2">
                        {#each unit.current_lease.tenants as tenant}
                            <div
                                class="bg-back p-4 rounded-lg grid grid-cols-2 gap-4"
                            >
                                <div>
                                    <p class="text-xs text-muted font-medium">
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
                                    <p class="text-xs text-muted font-medium">
                                        Tenant Since
                                    </p>
                                    <p
                                        class="text-sm text-teal font-semibold font-inter"
                                    >
                                        {formatDate(tenant.move_in_date)}
                                    </p>
                                </div>
                            </div>
                        {/each}
                    </div>

                    {#if new Date(unit.current_lease.end_date) < new Date()}
                        <div
                            class="flex justify-between gap-2 mt-6 items-center"
                        >
                            <button
                                class="mt-8 text-xs font-medium bg-green20 text-green p-4 font-inter w-72 rounded-lg hover:bg-lightteal hover:text-teal"
                                on:click={openRenewLeaseModal}
                            >
                                Renew Lease
                            </button>

                            <button
                                class="mt-8 text-xs font-medium bg-red20 text-red p-4 font-inter w-72 rounded-lg hover:bg-lightteal hover:text-teal"
                                on:click={renewLease}
                            >
                                End Lease
                            </button>
                        </div>
                    {/if}
                </div>
            {:else}
                <p class="text-muted text-sm font-medium">
                    This unit is currently vacant.
                </p>
            {/if}
        </div>
    </div>
{/if}

<!-- renew lease modal -->
{#if isRenewLeaseOpen}
    <div class="fixed inset-0 z-50">
        <button
            class="absolute inset-0 w-full h-full bg-black bg-opacity-50"
            aria-label="close modal"
            on:click={closeRenewLeaseModal}
        >
        </button>

        <div
            role="dialog"
            aria-modal="true"
            class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-6 w-full max-w-md mx-4"
        >
            <h2
                class="text-2xl font-inter font-bold mb-6 mt-6 text-slate text-center"
            >
                Renew Lease
            </h2>

            <p class="text-xs text-muted font-medium mb-2">Start Date</p>
            <input
                type="date"
                bind:value={formData.start_date}
                placeholder="Start Date"
                class="border p-2 mb-4 w-full rounded-lg text-sm font-medium text-teal font-inter"
            />

            <p class="text-xs text-muted font-medium mb-2">End Date</p>
            <input
                type="date"
                bind:value={formData.end_date}
                placeholder="End Date"
                class="border p-2 mb-4 w-full rounded-lg text-sm font-medium text-teal font-inter"
            />

            <p class="text-xs text-muted font-medium mb-2">Rent Amount</p>
            <input
                type="number"
                bind:value={formData.rent_amount}
                placeholder="Rent Amount"
                class="border p-2 mb-4 w-full rounded-lg text-sm font-medium text-teal font-inter"
            />

            <div class="flex justify-between gap-2 items-center">
                <button
                    class="mt-8 text-xs font-medium bg-green20 text-green p-4 font-inter w-72 rounded-lg hover:bg-lightteal hover:text-teal"
                    on:click={renewLease}
                >
                    Renew Lease
                </button>
                <button
                    class="mt-8 text-xs font-medium bg-red20 text-red p-4 font-inter w-72 rounded-lg hover:bg-lightteal hover:text-teal"
                    on:click={closeRenewLeaseModal}
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
{/if}
