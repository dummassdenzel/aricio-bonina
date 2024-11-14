<script lang="ts">
  import { onMount } from "svelte";
  import { goto } from "$app/navigation";
  import { api } from "$lib/services/api";
  import { auth } from "$lib/stores/auth";

  let error: string | null = null;
  let success: string | null = null;

  let formData = {
    email: "",
    password: "",
  };

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    error = null;
    success = null;

    try {
      const response = await api.post("login", formData);
      
      if (response.status.remarks === "success") {
        auth.login(response.payload.token, response.payload.user);
        success = response.status.message;
        goto("/web/manager/dashboard");
      } else {
        error = response.status.message;
      }
    } catch (err: any) {
      error = err.message;
    }
  }
</script>

<section>
  {#if error}
    <p class="text-red-500">{error}</p>
  {/if}

  {#if success}
    <p class="text-green-500">{success}</p>
  {/if}

  <form on:submit={handleSubmit}>
    <div>
      <label for="email">Email:</label>
      <input
        type="email"
        id="email"
        bind:value={formData.email}
        required
        class="rounded border px-2 text-black"
      />
    </div>

    <div>
      <label for="password">Password:</label>
      <input
        type="password"
        id="password"
        bind:value={formData.password}
        required
        class="rounded border px-2 text-black"
      />
    </div>

    <button type="submit">Login</button>
  </form>
</section>
