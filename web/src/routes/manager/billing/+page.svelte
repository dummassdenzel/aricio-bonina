<script lang="ts">
    import { onMount } from "svelte";
    import { api } from "$lib/services/api";

    let tenants: any[] = [];

    let error: string | null = null;
    let success: string | null = null;

    async function loadTenants() {
        try {
            const response = await api.get("tenants");
            tenants = response.payload;
        } catch (err: any) {
            error = err.message;
        }
    }

    onMount(async () => {
        loadTenants();
    });
</script>

<h1 class="text-3xl font-bold text-teal">Billing</h1>
<section class="mt-5">
    <div class="bg-back rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Recent:</h2>
        {#if error}
            <p class="text-red-500">{error}</p>
        {:else if tenants.length === 0}
            <p>No tenants found.</p>
        {:else}
            <ul class="space-y-2">
                {#each tenants as tenant}
                    <li class="p-3 bg-white rounded-lg shadow">
                        {tenant.first_name}
                        {tenant.last_name}
                    </li>
                {/each}
            </ul>
        {/if}
    </div>
</section>
