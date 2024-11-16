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

  let isFormFilled = false;
  $: isFormFilled = formData.email.trim().length > 0 && formData.password.trim().length > 0;
  

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

<!-- logo of aricio bonina real estate leasing -->
<div class="flex items-center gap-2.5">
  <div class="w-3 h-9 bg-teal rounded-full"></div>
  <h1 class="font-semibold text-midnight text-xs flex flex-col">aricio<span class="text-teal">real estate</span></h1>
</div>

<div class="flex flex-col justify-center items-center mt-60">
  <!-- login title -->
  <h1 class="font-black text-4xl font-inter text-teal mb-1 text-center">bonjour!</h1>

  {#if error} <!-- error message -->
  <p class="text-red-500 text-sm mt-3 bg-red-100 px-4 py-2 w-60 text-center rounded">{error}</p>
  {/if}

  {#if success} <!-- success message -->
    <p class="text-green-500 text-sm bg-green-100 px-4 py-2 w-60 text-center rounded">{success}</p>
  {/if} 

  <form class="flex flex-col w-80 p-6" on:submit={handleSubmit}>
    <!-- input field -->
    <div class="flex flex-col space-y-2">
      <!-- email -->
      <input type="email" id="email" bind:value={formData.email} required placeholder="your@email.com" class="rounded-xl text-xs border-2 px-4 py-3 text-midnight placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"/>
      <!-- password -->
      <input type="password" id="password" bind:value={formData.password} required placeholder="password" class="rounded-xl text-xs border-2 px-4 py-3 placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"/>
    </div>

    <!-- login button -->
    <button type="submit" class="mt-4 font-medium px-4 py-3 text-sm rounded-xl bg-midnight text-white disabled:bg-drop disabled:text-muted" disabled={!isFormFilled}>Continue →</button>

    <!-- back to home -->
    <a href="/" class="text-sm text-teal font-medium mt-4 text-center">Back to Home</a> 
  </form>
</div> 

