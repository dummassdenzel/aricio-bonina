<script lang="ts">
  import { formatDate } from "$lib/pipes/date-pipe";
  export let unitNumber: string;
  export let floor: string;
  export let tenantName: string;
  export let leaseEndDate: string;

  $: isOccupied = tenantName && leaseEndDate;
</script>

<!-- unit card -->
<div class="bg-white p-5 rounded-2xl border border-backdrop flex flex-col gap-4">

  <!-- floor number, unit number, unit status -->
  <div class="flex flex-col">
    <p class="text-xs font-medium text-muted">
      {floor ? floor : "Unavailable"}
    </p>
    <h3 class="text-lg font-bold font-inter text-teal text-center">Unit {unitNumber}</h3>
  </div>

  {#if isOccupied}
  <!-- unit status -->
  <button class="flex items-center gap-2 justify-center bg-lightteal p-2 px-10 rounded-xl">
    <div class="w-2 h-2 bg-teal  rounded-full"></div>
    <p class="text-xs text-teal font-medium">occupied</p> <!-- placeholder -->
  </button>

  <!-- tenant details  -->
  <div class="flex flex-col gap-2">
    <!-- T E N A N T -->
    <p class="text-muted text-xs">Tenant Details</p>
    <!-- tenant name  -->
    <div class="flex items-center gap-2">
      <!-- <div class="bg-lightteal text-teal text-xs font-bold rounded-full w-5 h-5 items-center text-center justify-center flex"></div> -->
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
      <p class="text-teal text-xs font-medium">{tenantName ? tenantName : "Unavailable"}</p>
    </div>
    
    <!-- lease end date -->
    <div class="flex gap-2 items-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
      <p class="text-teal text-xs font-medium">
        {formatDate(leaseEndDate ? leaseEndDate : "Not set")}
      </p>
    </div>

  </div>

  {:else}
      <button class="flex items-center gap-2 justify-center bg-green20 p-2 px-10 rounded-xl">
        <div class="w-2 h-2 bg-green  rounded-full"></div>
        <p class="text-xs text-green font-medium">available</p> <!-- placeholder -->
      </button>
  {/if}
</div>


    <!-- status -->
    <!-- <div class="flex justify-center mt-2"> -->
      <!-- available status -->
      <!-- <button class="flex items-center gap-2 justify-center bg-green20 p-2 px-10 rounded-xl">
        <div class="w-2 h-2 bg-green  rounded-full"></div>
        <p class="text-xs text-green">available</p>
      </button> -->

      <!-- occupied status -->
      <!-- <button class="flex items-center gap-2 justify-center bg-red20 p-2 px-10 rounded-xl">
        <div class="w-2 h-2 bg-red  rounded-full"></div>
        <p class="text-xs text-red">occupied</p>
      </button> -->

      <!-- <div class="flex flex-col gap-2"> -->
        <!-- <div class="flex items-center gap-2">
          <div class="bg-lightteal text-teal text-xs font-bold rounded-full px-2 py-1">L</div>
          <p class="text-teal text-xs font-semibold">Lee Parker Parantar</p>
        </div>

        <div>
          <p class="text-muted text-center text-xs font-medium">Payment Due</p>
          <p class="text-teal text-center text-sm font-semibold">25 November 2024</p>
        </div> -->
<!-- 
        <div>
          <p class="text-muted text-center text-xs font-medium mb-2">Payment Status</p>
          <button class="flex items-center gap-2 justify-center bg-orange20 p-2 px-10 rounded-xl">
            <div class="w-2 h-2 bg-orange  rounded-full"></div>
            <p class="text-xs text-orange">pending</p>
          </button>
        </div> -->
        
      <!-- </div> -->

      

    <!-- </div> -->

  <!-- <div class="flex flex-col gap-2">
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
  </div> -->
<!-- </div> -->
