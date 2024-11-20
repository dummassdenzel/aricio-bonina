<script lang="ts">
  import { formatDate } from "$lib/pipes/date-pipe";
  export let unitNumber: string;
  export let floor: string;
  export let tenantName: string;
  export let leaseEndDate: string;

  $: isOccupied = tenantName && leaseEndDate;
</script>

<!-- sample card -->
<div class="bg-back p-5 rounded-2xl border border-backdrop flex flex-col gap-8">
  <div>
    <h3 class="text-lg font-bold text-slate">Unit {unitNumber}</h3>
    <p class="text-xs font-medium text-muted">
      {floor ? "Floor " + floor : "Unavailable"}
    </p>
  </div>
  <div class="flex flex-col gap-2">
    {#if isOccupied}
      <div class="flex gap-2 items-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#314A60"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-user-round"
          ><circle cx="12" cy="8" r="5" /><path
            d="M20 21a8 8 0 0 0-16 0"
          /></svg
        >
        <p class="text-xs text-teal font-semibold">
          {tenantName ? tenantName : "Unavailable"}
        </p>
      </div>
      <div class="flex gap-2 items-center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#314A60"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="lucide lucide-calendar"
          ><path d="M8 2v4" /><path d="M16 2v4" /><rect
            width="18"
            height="18"
            x="3"
            y="4"
            rx="2"
          /><path d="M3 10h18" /></svg
        >
        <p class="text-xs text-teal font-semibold">
          Until {formatDate(leaseEndDate ? leaseEndDate : "Not set")}
        </p>
      </div>
    {:else}
      <p class="text-xs text-green-500 font-semibold">Vacant</p>
    {/if}
  </div>
</div>
