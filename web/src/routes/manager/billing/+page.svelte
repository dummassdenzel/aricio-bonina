<script lang="ts">
    import { onMount } from "svelte";
    import { api } from "$lib/services/api";
    import { formatDate } from "$lib/pipes/date-pipe";

    let leaseHistory: Record<
        string,
        Array<{
            id: number;
            start_date: string;
            end_date: string;
            date_renewed: string | null;
            rent_amount: number;
            tenants: string;
            latest_date: string;
        }>
    > = {};

    let error: string | null = null;

    async function loadLeaseHistory() {
        try {
            const response = await api.get("lease-history");
            leaseHistory = response.payload;
        } catch (err: any) {
            error = err.message;
        }
    }

    onMount(loadLeaseHistory);

    // Helper function to sort units by their latest lease date
    function getSortedUnits(history: typeof leaseHistory) {
        return Object.entries(history).sort((a, b) => {
            const aLatestDate = a[1][0]?.latest_date || "";
            const bLatestDate = b[1][0]?.latest_date || "";
            return bLatestDate.localeCompare(aLatestDate);
        });
    }
</script>

<h1 class="text-3xl font-bold text-teal">Lease History</h1>
<section class="mt-5">
    <div class="bg-back rounded-lg p-6">
        {#if error}
            <p class="text-red-500">{error}</p>
        {:else if Object.keys(leaseHistory).length === 0}
            <p>No lease history found.</p>
        {:else}
            {#each getSortedUnits(leaseHistory) as [unitNumber, leases]}
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4">Unit {unitNumber}</h2>
                    <div class="space-y-4">
                        {#each leases as lease}
                            <div class="p-4 bg-white rounded-lg shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">
                                            {formatDate(
                                                new Date(
                                                    lease.start_date,
                                                ).toLocaleDateString(),
                                            )} -
                                            {formatDate(
                                                new Date(
                                                    lease.end_date,
                                                ).toLocaleDateString(),
                                            )}
                                        </p>
                                        <p class="text-gray-600">
                                            Tenants: {lease.tenants}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">
                                            ₱{lease.rent_amount}
                                        </p>
                                        {#if lease.date_renewed}
                                            <p class="text-sm text-gray-500">
                                                Renewed: {new Date(
                                                    lease.date_renewed,
                                                ).toLocaleDateString()}
                                            </p>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/each}
        {/if}
    </div>
</section>
