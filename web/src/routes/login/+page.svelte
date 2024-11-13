<script lang="ts">
  import { onMount } from "svelte";
  import { apiGet } from "$lib/services/auth";

  let userData: any;
  let error: any;

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
</script>

<section>
  {#if error}
    <p style="color: red">Error: {error}</p>
  {:else if userData}
    <ul>
      <li><strong>ID:</strong> {userData.id}</li>
      <li><strong>First Name:</strong> {userData.first_name}</li>
      <li><strong>Last Name:</strong> {userData.last_name}</li>
      <li><strong>Email:</strong> {userData.email}</li>
      <li><strong>Role:</strong> {userData.role}</li>
      <li>
        <strong>Activation Status:</strong>
        {userData.activation ? "Active" : "Inactive"}
      </li>
      <li><strong>Created At:</strong> {userData.created_at}</li>
    </ul>
  {:else}
    <p>Loading...</p>
  {/if}
</section>

<section>
  <h1>Login</h1>
  <form>
    <div>
      <label for="name">Email:</label>
      <input
        type="email"
        id="email"
        required
        class=" rounded border px-2 text-black"
      />
    </div>
    <div>
      <label for="email">Password:</label>
      <input
        type="password"
        id="password"
        required
        class=" rounded border px-2 text-black"
      />
    </div>
  </form>
</section>
