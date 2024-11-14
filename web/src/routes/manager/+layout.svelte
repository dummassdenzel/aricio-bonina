<script lang="ts">
  import { onMount } from "svelte";
  import { goto } from "$app/navigation";
  import { auth } from "$lib/stores/auth";
  import Navigation from "$lib/components/manager/Navigation.svelte";

  onMount(() => {
    auth.initialize();

    if (!$auth.isAuthenticated) {
      goto("/login");
    }
  });
</script>

{#if $auth.isAuthenticated}
  <Navigation />
  <slot />
{/if}
