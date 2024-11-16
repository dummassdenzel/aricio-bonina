<script lang="ts">
  import { goto } from "$app/navigation";
</script>

<header class="flex justify-between">
  <!-- logo of aricio bonina real estate leasing -->
  <div class="flex items-center gap-2.5">
    <div class="w-3 h-9 bg-teal rounded-full"></div>
    <h1 class="font-semibold text-midnight text-xs flex flex-col">aricio<span class="text-teal">real estate</span></h1>
  </div>

  <!-- navigation bar -->
  <nav class="flex items-center gap-10">
    <a href="/" class="font-semibold text-slate text-xs hover:text-midnight">Home</a>
    <a href="" class="font-semibold text-slate text-xs hover:text-midnight">About</a>
    <a href="" class="font-semibold text-slate text-xs hover:text-midnight">Features</a>
    <a href="" class="font-semibold text-slate text-xs hover:text-midnight">Contact</a>
  </nav>

  <!-- login button -->
  <div class="text-xs text-slate font-semibold border-2 border-slate p-2 w-32 rounded-full text-center flex justify-center cursor-pointer hover:bg-slate hover:text-white hover:font-medium">
    <button on:click={() => goto("/web/login")} class="animation" style="transition: all 0.5s ease-in-out;">Get Started</button> <!-- redirect to login -->
  </div>
</header>
