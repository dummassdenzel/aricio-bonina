<script lang="ts">
  import { goto } from "$app/navigation";
  let isMenuOpen = false; 
</script>

<header class="flex justify-between items-center p-4">
  <!-- logo of Aricio Bonina Real Estate Leasing -->
  <div class="flex items-center gap-2.5">
    <div class="w-3 h-9 bg-teal rounded-full"></div>
    <h1 class="font-semibold text-midnight text-sm flex flex-col">
      aricio <span class="text-teal">real estate</span>
    </h1>
  </div>

  <!-- navigation bar -->
  <nav class="hidden lg:flex items-center gap-10">
    <a href="/" class="font-semibold text-slate text-xs hover:text-midnight">Home</a>
    <a href="#about" class="font-semibold text-slate text-xs hover:text-midnight">About</a>
    <a href="#features" class="font-semibold text-slate text-xs hover:text-midnight">Features</a>
    <a href="#contact" class="font-semibold text-slate text-xs hover:text-midnight">Contact</a>    
  </nav>

  <!-- login button -->
  <button on:click={() => goto("/web/login")} class="hidden lg:flex text-xs text-slate font-semibold border-2 border-slate p-2 w-32 rounded-full text-center justify-center cursor-pointer hover:bg-slate hover:text-white hover:font-medium">
    Log In
  </button>

  <!-- hamburger menu -->
  <div class="lg:hidden flex items-center">
    <!-- svelte-ignore a11y_consider_explicit_label -->
    <button on:click={() => isMenuOpen = !isMenuOpen} class="text-teal">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="#495F76">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>
  </div>
</header>

<!-- dropdown menu -->
{#if isMenuOpen}
  <div class="lg:hidden bg-transparent backdrop-blur-md border rounded-lg absolute top-24 right-10 w-32 p-4">
    <a href="/" class="block font-semibold text-slate text-xs py-2 hover:text-midnight">Home</a>
    <a href="#about" class="block font-semibold text-slate text-xs py-2 hover:text-midnight">About</a>
    <a href="#features" class="block font-semibold text-slate text-xs py-2 hover:text-midnight">Features</a>
    <a href="#contact" class="block font-semibold text-slate text-xs py-2 hover:text-midnight">Contact</a>

    <button on:click={() => goto("/web/login")} class="block font-semibold text-slate text-xs py-2 hover:text-midnight">
      Log In
    </button>
  </div>
{/if}
