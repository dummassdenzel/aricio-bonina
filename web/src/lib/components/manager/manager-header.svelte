<script lang="ts">
    import { auth } from "$lib/stores/auth";
    import { goto } from "$app/navigation";
  
    function handleLogout() {
      auth.logout();
      goto("/web/login"); // suggestion: redirect to landing page.
    }
  </script>
  
  <!-- <nav>
    {#if $auth.isAuthenticated}
      <div class="user-info">
        Welcome, {$auth.user?.first_name}!
      </div>
      <button on:click={handleLogout}>Logout</button>
    {:else}
      <a href="/web/login">Login</a>
    {/if}
  </nav> -->

<header class="flex justify-between">
    <!-- logo of aricio bonina real estate leasing -->
    <div class="flex items-center gap-2.5">
        <div class="w-3 h-9 bg-teal rounded-full"></div>
        <h1 class="font-semibold text-midnight text-xs flex flex-col">aricio<span class="text-teal">real estate</span></h1>
    </div>

    <!-- navigation bar -->
    <nav class="flex items-center gap-12 bg-back rounded-full p-3 px-10">
        <!-- dashboard-->
        <a href="/web/manager/dashboard" class="font-semibold text-slate text-xs flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#314A60" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>Dashboard
        </a>

        <!-- room -->
        <a href="/web/manager/unit" class="font-semibold text-slate text-xs flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#314A60" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bed-single"><path d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8"/><path d="M5 10V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4"/><path d="M3 18h18"/></svg>Unit
        </a>

        <!-- tenants -->
        <a href="/web/manager/tenant" class="font-semibold text-slate text-xs flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#314A60" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>Tenants
        </a>

        <!-- billing -->
        <a href="/web/manager/billing" class="font-semibold text-slate text-xs flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#314A60" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>Billing
        </a>
    </nav>

    <!-- user profile -->
    <div class="flex gap-3">
        <!-- notification bell -->
        <!-- <button><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#686868" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell-dot"><path d="M19.4 14.9C20.2 16.4 21 17 21 17H3s3-2 3-9c0-3.3 2.7-6 6-6 .7 0 1.3.1 1.9.3"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><circle cx="18" cy="8" r="3"/></svg></button> -->
        
        <!-- actions: profile, settings, log out. create dropdown function (to be implemented) -->
        <div class="flex justify-center align-middle text-center gap-3 items-center">
            <div class="w-8 h-8 bg-lightteal rounded-full text-center flex justify-center items-center text-teal text-xs font-bold">d</div>
            <button class="user-info text-teal text-xs font-semibold hover:text-red-500" on:click={handleLogout}>{$auth.user?.first_name}</button>
        </div>
    </div>
</header>