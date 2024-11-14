<script lang="ts">
  import { auth } from "$lib/stores/auth";
  import { goto } from "$app/navigation";

  function handleLogout() {
    auth.logout();
    goto("/web/login");
  }
</script>

<nav>
  {#if $auth.isAuthenticated}
    <div class="user-info">
      Welcome, {$auth.user?.first_name}!
    </div>
    <button on:click={handleLogout}>Logout</button>
  {:else}
    <a href="/web/login">Login</a>
  {/if}
</nav>
