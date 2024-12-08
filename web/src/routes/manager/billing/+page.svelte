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
            date_terminated: string | null;
            rent_amount: number;
            tenants: string;
            latest_date: string;
            created_at: string;
        }>
    > = {};

    let error: string | null = null;

    // Add state for expanded units
    let expandedUnits: Set<string> = new Set();

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

    // Toggle function
    function toggleUnitHistory(unitNumber: string) {
        if (expandedUnits.has(unitNumber)) {
            expandedUnits.delete(unitNumber);
        } else {
            expandedUnits.add(unitNumber);
        }
        expandedUnits = expandedUnits; // trigger reactivity
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
                    </div>
                    <div class="space-y-4">
                        <!-- Show latest lease -->
                        {#if leases[0]}
                            <div class="p-4 bg-white rounded-lg shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">
                                            {formatDate(
                                                new Date(
                                                    leases[0].start_date,
                                                ).toLocaleDateString(),
                                            )} -
                                            {formatDate(
                                                new Date(
                                                    leases[0].end_date,
                                                ).toLocaleDateString(),
                                            )}
                                        </p>
                                        {#if leases[0].date_renewed}
                                            <p class="text-sm text-gray-500">
                                                {leases[0].date_renewed
                                                    ? `Renewed on: ${new Date(leases[0].date_renewed).toLocaleDateString()}`
                                                    : ""}
                                            </p>
                                        {/if}
                                        {#if leases[0].date_terminated}
                                            <p class="text-sm text-gray-500">
                                                {leases[0].date_terminated
                                                    ? `Terminated on: ${new Date(leases[0].date_terminated).toLocaleDateString()}`
                                                    : ""}
                                            </p>
                                        {/if}
                                        {#if !leases[0].date_terminated && !leases[0].date_renewed}
                                            <p class="text-sm text-gray-500">
                                                Current Lease Term
                                            </p>
                                        {/if}
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">
                                            ₱{leases[0].rent_amount}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Created At: {formatDate(
                                                new Date(
                                                    leases[0].created_at,
                                                ).toLocaleDateString(),
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <!-- Show toggle button if there are more leases -->
                        {#if leases.length > 1}
                            <button
                                class="text-sm text-teal hover:text-teal-600 mt-2"
                                on:click={() => toggleUnitHistory(unitNumber)}
                            >
                                {expandedUnits.has(unitNumber)
                                    ? "Show Less"
                                    : `+ ${leases.length - 1} more`}
                            </button>

                            <!-- Show older leases if expanded -->
                            {#if expandedUnits.has(unitNumber)}
                                {#each leases.slice(1) as lease}
                                    <div
                                        class="p-4 bg-white rounded-lg shadow opacity-75"
                                    >
                                        <div
                                            class="flex justify-between items-start"
                                        >
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
                                                {#if lease.date_renewed}
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {lease.date_renewed
                                                            ? `Renewed on: ${new Date(lease.date_renewed).toLocaleDateString()}`
                                                            : ""}
                                                    </p>
                                                {/if}
                                                {#if lease.date_terminated}
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {lease.date_terminated
                                                            ? `Terminated on: ${new Date(lease.date_terminated).toLocaleDateString()}`
                                                            : ""}
                                                    </p>
                                                {/if}
                                                {#if !lease.date_terminated && !lease.date_renewed}
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Current Lease Term
                                                    </p>
                                                {/if}
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold">
                                                    ₱{lease.rent_amount}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    Created At: {formatDate(
                                                        new Date(
                                                            lease.created_at,
                                                        ).toLocaleDateString(),
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            {/if}
                        {/if}
                    </div>
                </div>
            {/each}
        {/if}
    </div>
</section>
