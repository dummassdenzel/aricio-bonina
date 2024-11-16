<script lang="ts">
  let showModal = false;
  function openModal() {
    showModal = true;
  }
  function closeModal() {
    showModal = false;
  }
  function handleSubmit() {
    closeModal();
  }
</script>

<section>
  <div
    class="flex text-center text-muted py-5 font-medium align-middle justify-center bg-back rounded-full mt-10"
  >
    <!-- OPEN NEW TENANT FORM -->
    <button on:click={openModal} class="bg-muted text-white py-2 px-4 rounded">
      Add New Tenant
    </button>
    <!-- POPUP FOR NEW TENANT FORM -->
    {#if showModal}
      <div
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
          <h2 class="text-xl font-bold mb-4">Add New Tenant</h2>

          <form on:submit|preventDefault={handleSubmit}>
            <div class="mb-4">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="name"
              >
                Full Name
              </label>
              <input
                type="text"
                id="name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              />
            </div>

            <div class="mb-4">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="email"
              >
                Email
              </label>
              <input
                type="email"
                id="email"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              />
            </div>

            <div class="mb-4">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="phone"
              >
                Phone Number
              </label>
              <input
                type="tel"
                id="phone"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              />
            </div>

            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                on:click={closeModal}
              >
                Cancel
              </button>
              <button
                type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    {/if}
  </div>
</section>
