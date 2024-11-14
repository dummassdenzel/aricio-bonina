<script lang="ts">
  import { onMount } from "svelte";
  import { apiGet, apiPost } from "$lib/services/auth";

  let userData: any;

  let error: string | null = null;
  let success: string | null = null;

  let formData = {
    email: "",
    password: "",
  };

  onMount(async () => {
    try {
      const response = await apiGet("users");
      if (response.status.remarks === "success") {
        userData = response.payload[0];
      } else {
        throw new Error(response.status.message);
      }
    } catch (err: any) {
      error = err.message;
    }
  });

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    error = null;
    success = null;

    // if (something_wrong_happens) {
    //   error = "Passwords do not match";
    //   return;
    // }

    try {
      const res = await apiPost("login", formData);
      if (res.status.remarks === "success") {
        success = res.status.message;
      } else {
        throw new Error(res.status.message);
      }
    } catch (err: any) {
      error = err.message;
    }
  }
</script>

<section>
  <h1>Register</h1>

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

    <button type="submit">Register</button>
  </form>
</section>
