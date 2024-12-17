<script lang="ts">
  import { onMount } from "svelte";
  import { goto } from "$app/navigation";
  import { auth } from "$lib/stores/auth";
  import ManagerHeader from "$lib/components/manager/manager-header.svelte";
  import ScrollToTop from "$lib/components/manager/scroll-to-top.svelte";

  onMount(() => {
    auth.initialize();

    if (!$auth.isAuthenticated) {
      goto("/login");
    }
  });
</script>

{#if $auth.isAuthenticated}
  <ManagerHeader />
  <div class="mt-10">
    <slot />
  </div>
  <ScrollToTop />
{/if}
