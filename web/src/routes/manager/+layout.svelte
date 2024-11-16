<script lang="ts">
  import { onMount } from "svelte";
  import { goto } from "$app/navigation";
  import { auth } from "$lib/stores/auth";
  import Navbar from "$lib/components/manager/navbar.svelte";

  onMount(() => {
    auth.initialize();

    if (!$auth.isAuthenticated) {
      goto("/login");
    }
  });
</script>

{#if $auth.isAuthenticated}
  <Navbar />
  <slot />
{/if}
