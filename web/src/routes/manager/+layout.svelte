<script lang="ts">
  import { onMount } from "svelte";
  import { goto } from "$app/navigation";
  import { auth } from "$lib/stores/auth";
  import ManagerHeader from "$lib/components/manager/manager-header.svelte";

  onMount(() => {
    auth.initialize();

    if (!$auth.isAuthenticated) {
      goto("/web/login");
    }
  });
</script>

{#if $auth.isAuthenticated}
  <ManagerHeader />
  <slot />
{/if}
