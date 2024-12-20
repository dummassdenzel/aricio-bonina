<script lang="ts">
    import { onMount } from "svelte";
    import { leaseHistoryStore } from "$lib/stores/lease-history-store";
    import { formatDate } from "$lib/pipes/date-pipe";

    let error: string | null = null;
    let expandedUnits: Set<string> = new Set();
    let searchQuery: string = "";
    let sortBy: "latest" | "unit" = "latest";
    let filterStatus: "all" | "active" | "terminated" = "all";

    $: leaseHistory = $leaseHistoryStore.leases;

    // Debounce search
    let searchTimeout: NodeJS.Timeout;
    $: {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                await leaseHistoryStore.loadLeaseHistory({
                    search: searchQuery,
                    status: filterStatus !== "all" ? filterStatus : undefined,
                });
            } catch (err: any) {
                error = err.message;
            }
        }, 300);
    }

    onMount(() => {
        leaseHistoryStore.loadLeaseHistory();
    });

    function getSortedUnits(history: Record<string, any[]>) {
        return Object.entries(history).sort((a, b) => {
            if (sortBy === "latest") {
                const aLatestDate = a[1][0]?.latest_date || "";
                const bLatestDate = b[1][0]?.latest_date || "";
                return bLatestDate.localeCompare(aLatestDate);
            } else {
                return parseInt(a[0]) - parseInt(b[0]);
            }
        });
    }

    function toggleUnitHistory(unitNumber: string) {
        if (expandedUnits.has(unitNumber)) {
            expandedUnits.delete(unitNumber);
        } else {
            expandedUnits.add(unitNumber);
        }
        expandedUnits = expandedUnits;
    }
</script>

<div class="">
    <div class="flex flex-col gap-6">
        <!-- Header Section -->
        <div
            class="flex items-center justify-between flex-col sm:flex-row gap-4"
        >
            <h1 class="text-2xl sm:text-3xl font-bold text-teal">
                Lease History
            </h1>

            <!-- Stats Summary -->
            <div
                class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4 w-full sm:w-auto"
            >
                <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                    <p class="text-[10px] sm:text-xs text-muted mb-1">
                        Total Units
                    </p>
                    <p class="text-base sm:text-lg font-bold text-teal">
                        {Object.keys(leaseHistory).length}
                    </p>
                </div>
                <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                    <p class="text-[10px] sm:text-xs text-muted mb-1">
                        Total Leases
                    </p>
                    <p class="text-base sm:text-lg font-bold text-teal">
                        {Object.values(leaseHistory).reduce(
                            (acc, leases) => acc + leases.length,
                            0,
                        )}
                    </p>
                </div>
                <div class="bg-white rounded-lg p-3 sm:p-4 shadow-sm">
                    <p class="text-[10px] sm:text-xs text-muted mb-1">
                        Active Leases
                    </p>
                    <p class="text-base sm:text-lg font-bold text-teal">
                        {Object.values(leaseHistory).reduce(
                            (acc, leases) =>
                                acc +
                                leases.filter((lease) => !lease.date_terminated)
                                    .length,
                            0,
                        )}
                    </p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row gap-4 justify-between">
            <!-- Search -->
            <div class="relative flex-grow sm:flex-grow-0">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="#989898"
                    stroke-width="1"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="absolute left-3 top-1/2 transform -translate-y-1/2"
                >
                    <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                </svg>
                <input
                    type="text"
                    placeholder="Search by unit or tenant name"
                    bind:value={searchQuery}
                    class="w-full sm:w-72 pl-10 text-xs text-dmSans text-muted rounded-2xl p-3.5 bg-back focus:text-teal focus:outline-backdrop"
                />
            </div>

            <!-- Filters -->
            <div class="flex gap-3">
                <select
                    bind:value={filterStatus}
                    class="bg-back rounded-2xl px-4 py-2 text-xs text-slate"
                >
                    <option value="all">All Leases</option>
                    <option value="active">Active Only</option>
                    <option value="terminated">Terminated Only</option>
                </select>

                <select
                    bind:value={sortBy}
                    class="bg-back rounded-2xl px-4 py-2 text-xs text-slate"
                >
                    <option value="latest">Sort by Latest Date</option>
                    <option value="unit">Sort by Unit</option>
                </select>
            </div>
        </div>

        <!-- Lease History List -->
        <div class="bg-back rounded-lg p-5 border">
            {#if error}
                <div class="bg-red20 text-red p-4 rounded-xl">
                    <p class="text-sm">{error}</p>
                </div>
            {:else if Object.keys(leaseHistory).length === 0}
                <div class="text-center py-8">
                    <p class="text-sm text-muted">No lease history found.</p>
                </div>
            {:else}
                <div class="space-y-6">
                    {#each getSortedUnits(leaseHistory) as [unitNumber, leases]}
                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-xl font-bold text-teal">
                                        Unit {unitNumber}:
                                    </h2>
                                    <h3 class="text-sm text-slate">
                                        {leases[0].tenants}
                                    </h3>
                                </div>

                                <span
                                    class="text-xs px-2 py-1 rounded-full {leases[0]
                                        .date_terminated
                                        ? 'bg-red20 text-red'
                                        : 'bg-green20 text-green'}"
                                >
                                    {leases[0].date_terminated
                                        ? "Terminated"
                                        : "Active"}
                                </span>
                            </div>

                            <!-- Latest Lease -->
                            {#if leases[0]}
                                <div class="border-l-4 border-teal pl-4 py-2">
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div>
                                            <p class="font-semibold text-slate">
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
                                                <p
                                                    class="text-xs text-green mt-1"
                                                >
                                                    Renewed: {formatDate(
                                                        new Date(
                                                            leases[0].date_renewed,
                                                        ).toLocaleDateString(),
                                                    )}
                                                </p>
                                            {/if}
                                            {#if leases[0].date_terminated}
                                                <p
                                                    class="text-xs text-red mt-1"
                                                >
                                                    Terminated: {formatDate(
                                                        new Date(
                                                            leases[0].date_terminated,
                                                        ).toLocaleDateString(),
                                                    )}
                                                </p>
                                            {/if}
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <p class="font-bold text-teal">
                                                ₱{leases[0].rent_amount.toLocaleString()}
                                            </p>
                                            <p
                                                class="text-end text-xs text-gray-500"
                                            >
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

                            <!-- History Toggle -->
                            {#if leases.length > 1}
                                <button
                                    class="mt-4 text-sm text-teal hover:text-teal/80 transition-colors flex items-center gap-2"
                                    on:click={() =>
                                        toggleUnitHistory(unitNumber)}
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        {#if expandedUnits.has(unitNumber)}
                                            <path d="m18 15-6-6-6 6" />
                                        {:else}
                                            <path d="m6 9 6 6 6-6" />
                                        {/if}
                                    </svg>
                                    {expandedUnits.has(unitNumber)
                                        ? "Hide History"
                                        : `Show History (${leases.length - 1} more)`}
                                </button>

                                {#if expandedUnits.has(unitNumber)}
                                    <div class="mt-4 space-y-4 pl-4">
                                        {#each leases.slice(1) as lease}
                                            <div
                                                class="border-l-4 border-slate/20 pl-4 py-2"
                                            >
                                                <div
                                                    class="flex justify-between items-start"
                                                >
                                                    <div>
                                                        <p
                                                            class="font-semibold text-slate/80"
                                                        >
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
                                                                class="text-xs text-green mt-1"
                                                            >
                                                                Renewed: {formatDate(
                                                                    new Date(
                                                                        lease.date_renewed,
                                                                    ).toLocaleDateString(),
                                                                )}
                                                            </p>
                                                        {/if}
                                                        {#if lease.date_terminated}
                                                            <p
                                                                class="text-xs text-red mt-1"
                                                            >
                                                                Terminated: {formatDate(
                                                                    new Date(
                                                                        lease.date_terminated,
                                                                    ).toLocaleDateString(),
                                                                )}
                                                            </p>
                                                        {/if}
                                                    </div>
                                                    <div
                                                        class="flex flex-col items-end"
                                                    >
                                                        <p
                                                            class="font-bold text-teal"
                                                        >
                                                            ₱{leases[0].rent_amount.toLocaleString()}
                                                        </p>
                                                        <p
                                                            class="text-xs text-end text-gray-500"
                                                        >
                                                            Created At: {formatDate(
                                                                new Date(
                                                                    leases[0].created_at,
                                                                ).toLocaleDateString(),
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}
                            {/if}
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Add this after the lease history list -->
        <div class="flex justify-between items-center mt-6 border-t pt-6">
            <p class="text-xs text-muted">
                Showing {($leaseHistoryStore.pagination.currentPage - 1) *
                    $leaseHistoryStore.pagination.itemsPerPage +
                    1}
                to {Math.min(
                    $leaseHistoryStore.pagination.currentPage *
                        $leaseHistoryStore.pagination.itemsPerPage,
                    $leaseHistoryStore.pagination.totalItems,
                )}
                of {$leaseHistoryStore.pagination.totalItems} entries
            </p>

            <div class="flex gap-2">
                <button
                    class="p-2 text-xs font-medium rounded-lg transition-colors disabled:opacity-50"
                    class:text-teal={$leaseHistoryStore.pagination.currentPage >
                        1}
                    class:text-muted={$leaseHistoryStore.pagination
                        .currentPage === 1}
                    disabled={$leaseHistoryStore.pagination.currentPage === 1}
                    on:click={() => {
                        leaseHistoryStore.loadLeaseHistory({
                            search: searchQuery,
                            status:
                                filterStatus !== "all"
                                    ? filterStatus
                                    : undefined,
                            page: $leaseHistoryStore.pagination.currentPage - 1,
                        });
                    }}
                >
                    Previous
                </button>

                {#each Array($leaseHistoryStore.pagination.totalPages) as _, i}
                    <button
                        class="w-8 h-8 rounded-lg text-xs font-medium transition-colors"
                        class:bg-lightteal={$leaseHistoryStore.pagination
                            .currentPage ===
                            i + 1}
                        class:text-teal={$leaseHistoryStore.pagination
                            .currentPage ===
                            i + 1}
                        class:text-muted={$leaseHistoryStore.pagination
                            .currentPage !==
                            i + 1}
                        on:click={() => {
                            leaseHistoryStore.loadLeaseHistory({
                                search: searchQuery,
                                status:
                                    filterStatus !== "all"
                                        ? filterStatus
                                        : undefined,
                                page: i + 1,
                            });
                        }}
                    >
                        {i + 1}
                    </button>
                {/each}

                <button
                    class="p-2 text-xs font-medium rounded-lg transition-colors disabled:opacity-50"
                    class:text-teal={$leaseHistoryStore.pagination.currentPage <
                        $leaseHistoryStore.pagination.totalPages}
                    class:text-muted={$leaseHistoryStore.pagination
                        .currentPage ===
                        $leaseHistoryStore.pagination.totalPages}
                    disabled={$leaseHistoryStore.pagination.currentPage ===
                        $leaseHistoryStore.pagination.totalPages}
                    on:click={() => {
                        leaseHistoryStore.loadLeaseHistory({
                            search: searchQuery,
                            status:
                                filterStatus !== "all"
                                    ? filterStatus
                                    : undefined,
                            page: $leaseHistoryStore.pagination.currentPage + 1,
                        });
                    }}
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</div>
