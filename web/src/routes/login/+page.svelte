<script lang="ts">
  import { goto } from "$app/navigation";
  import { api } from "$lib/services/api";
  import { auth } from "$lib/stores/auth";

  let error: string | null = null;
  let formData = {
    email: "",
    password: "",
  };

  let isFormFilled = false;
  $: isFormFilled =
    formData.email.trim().length > 0 && formData.password.trim().length > 0;

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    error = null;

    try {
      const response = await api.post("login", formData);

      if (response.status.remarks === "success" && response.payload) {
        const { token, user } = response.payload;
        await auth.login(token, user);
        goto("/manager/dashboard");
      } else {
        error = response.status.message || "Login failed";
      }
    } catch (err: any) {
      error = err.message;
    }
  }
</script>

<!-- header -->
<div class="flex justify-between">
  <!-- logo of aricio bonina real estate leasing -->
  <div class="flex items-center gap-2.5">
    <div class="w-3 h-9 bg-teal rounded-full"></div>
    <h1 class="font-semibold text-midnight text-xs flex flex-col">
      aricio<span class="text-teal">real estate</span>
    </h1>
  </div>

  <!-- back to home -->
  <button
    class="text-xs text-slate font-semibold border-2 border-slate p-2 w-32 rounded-full text-center flex justify-center cursor-pointer hover:bg-slate hover:text-white hover:font-medium animation"
    style="transition: all 0.5s ease-in-out;"
    on:click={() => goto("/")}>Return Home</button
  >
</div>

<!-- main content -->
<div class="flex justify-center items-center min-h-[calc(100vh-200px)]">
  <div class="w-full max-w-md px-4">
    <!-- error message -->
    {#if error}
      <p
        class="text-red-500 text-sm mt-3 bg-red-100 px-4 py-2 w-full text-center rounded"
      >
        {error}
      </p>
    {/if}

    <!-- form -->
    <form class="flex flex-col w-full p-12" on:submit={handleSubmit}>
      <!-- login title -->
      <h1 class="font-black text-3xl font-inter text-teal mb-6 text-center">
        Login
      </h1>

      <!-- input fields -->
      <div class="flex flex-col space-y-2">
        <input
          type="email"
          id="email"
          bind:value={formData.email}
          required
          placeholder="your@email.com"
          class="rounded-xl text-xs border-2 px-4 py-3 text-midnight placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"
        />
        <input
          type="password"
          id="password"
          bind:value={formData.password}
          required
          placeholder="password"
          class="rounded-xl text-xs border-2 px-4 py-3 placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"
        />
        <div class="flex justify-end mt-2">
          <a
            href="/forgot-password"
            class="text-xs text-slate hover:text-teal transition-colors"
          >
            Forgot Password?
          </a>
        </div>
      </div>

      <!-- login button -->
      <button
        type="submit"
        class="mt-4 font-medium px-4 py-3 text-sm rounded-xl bg-midnight text-white disabled:bg-drop disabled:text-muted w-full"
        disabled={!isFormFilled}>Continue →</button
      >
    </form>
  </div>
</div>
