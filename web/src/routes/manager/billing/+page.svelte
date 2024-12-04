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
            created_at: string;
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

    function getSortedUnits(history: typeof leaseHistory) {
        return Object.entries(history).sort((a, b) => {
            const aLatestDate = a[1][0]?.latest_date || "";
            const bLatestDate = b[1][0]?.latest_date || "";
            return bLatestDate.localeCompare(aLatestDate);
        });
    }
</script>

<h1 class="text-3xl font-bold text-teal">Billing History</h1>
<section class="mt-8">
    <div class="bg-back rounded-lg p-5 border">
        {#if error}
            <p class="text-red-500">{error}</p>
        {:else if Object.keys(leaseHistory).length === 0}
            <p class="text-sm text-muted">No lease history found.</p>
        {:else}
            {#each getSortedUnits(leaseHistory) as [unitNumber, leases]}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <h2 class="text-xl font-bold">Unit {unitNumber}</h2>
                        <p class="text-sm text-gray-600">
                            Current Tenants: {leases[0]?.tenants || "None"}
                        </p>
                    </div>
                    <div class="space-y-4">
                        {#each leases as lease}
                            <div class="p-4 bg-white rounded-lg shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">
                                            {formatDate(
                                                new Date(
                                                    lease.created_at,
                                                ).toLocaleDateString(),
                                            )} -
                                            {formatDate(
                                                new Date(
                                                    lease.end_date,
                                                ).toLocaleDateString(),
                                            )}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Created At: {formatDate(
                                                new Date(
                                                    lease.created_at,
                                                ).toLocaleDateString(),
                                            )}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">
                                            ₱{lease.rent_amount}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {lease.date_renewed
                                                ? `Renewed: ${new Date(lease.date_renewed).toLocaleDateString()}`
                                                : "Current Latest Lease Term"}
                                        </p>
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
