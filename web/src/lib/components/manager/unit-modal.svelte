<script lang="ts">
    import { formatDate } from "$lib/pipes/date-pipe";
    import { api } from "$lib/services/api";

    export let isOpen: boolean = false;
    export let onClose: () => void;
    export let unit: {
        unit_number: number;
        floor: number;
        current_lease?: {
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
        lease_id: 0,
        start_date: "",
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
</script>

<svelte:window on:keydown={handleKeydown} />

{#if isOpen}
    <div class="fixed inset-0 z-50">
        <!-- Backdrop -->
        <button
            class="absolute inset-0 w-full h-full bg-black bg-opacity-50"
            on:click={onClose}
            aria-label="Close modal"
        ></button>

        <!-- Modal Content -->
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg p-6 w-full max-w-2xl mx-4"
        >
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 id="modal-title" class="text-2xl font-bold text-teal">
                    Unit {unit.unit_number} Details
                </h2>
                <button
                    type="button"
                    on:click={onClose}
                    class="text-gray hover:text-teal transition-colors"
                    aria-label="Close modal"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            <!-- Unit Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2 text-teal">
                    Unit Information
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray">Unit Number</p>
                        <p class="font-medium">{unit.unit_number}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray">Floor</p>
                        <p class="font-medium">{unit.floor}</p>
                    </div>
                </div>
            </div>

            <!-- Lease Information -->
            {#if unit.current_lease}
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2 text-teal">
                        Lease Information
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray">Start Date</p>
                            <p class="font-medium">
                                {formatDate(unit.current_lease.start_date)}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray">End Date</p>
                            <p class="font-medium">
                                {formatDate(unit.current_lease.end_date)}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray">Monthly Rent</p>
                            <p class="font-medium">
                                {unit.current_lease.rent_amount
                                    ? `₱${unit.current_lease.rent_amount.toLocaleString()}`
                                    : "Not specified"}
                            </p>
                        </div>
                    </div>
                    {#if new Date(unit.current_lease.end_date) < new Date()}
                        <div class="mb-6 flex justify-center text-center">
                            <div class="p-4 rounded bg-red-100">
                                <p class="text-red-600 font-semibold">
                                    This lease is overdue!
                                </p>
                                <button
                                    on:click={renewLease}
                                    class="bg-teal-500 bg-white text-black p-2 rounded-lg"
                                >
                                    Renew Lease
                                </button>
                                <button
                                    on:click={renewLease}
                                    class="bg-teal-500 bg-white text-black p-2 rounded-lg"
                                >
                                    End Lease
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Tenant Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-teal">
                        Tenants
                    </h3>
                    <div class="space-y-4">
                        {#each unit.current_lease.tenants as tenant}
                            <div
                                class="bg-back p-4 rounded-lg grid grid-cols-2 gap-4"
                            >
                                <div>
                                    <p class="text-sm text-gray">Name:</p>
                                    <p class="font-medium">
                                        {tenant.first_name}
                                        {tenant.last_name}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray">
                                        Tenant Since:
                                    </p>
                                    <p class="font-medium">
                                        {formatDate(tenant.move_in_date)}
                                    </p>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {:else}
                <p class="text-gray">This unit is currently vacant</p>
            {/if}

            <!-- UI for renewing lease -->
            <!-- <div>
                <h2>Renew Lease</h2>
                <input
                    type="number"
                    bind:value={formData.lease_id}
                    placeholder="Lease ID"
                />
                <input
                    type="date"
                    bind:value={formData.start_date}
                    placeholder="Start Date"
                />
                <input
                    type="date"
                    bind:value={formData.end_date}
                    placeholder="End Date"
                />
                <input
                    type="number"
                    bind:value={formData.rent_amount}
                    placeholder="Rent Amount"
                />
                <button on:click={renewLease}>Renew Lease</button>
            </div> -->
        </div>
    </div>
{/if}
