<script lang="ts">
  import { api } from "$lib/services/api";
  let error: string | null = null;
  let success: string | null = null;

  let showModal = false;
  function openModal() {
    showModal = true;
  }
  function closeModal() {
    showModal = false;
  }

  let formData = {
    first_name: "",
    last_name: "",
    unit_number: "",
    move_in_date: "",
    start_date: "",
    end_date: "",
    rent_amount: "",
  };

  async function handleSubmit(event: SubmitEvent) {
    event.preventDefault();

    console.log(formData);
    try {
      const response = await api.post("addtenant", formData);

      if (response.status.remarks === "success") {
        success = response.status.message;
        closeModal();
      } else {
        error = response.status.message;
      }
    } catch (err: any) {
      error = err.message;
    }
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
        <div
          class="bg-white p-6 rounded-lg shadow-lg max-h-[80vh] overflow-y-auto"
        >
          <h2 class="text-xl font-bold mb-4">Add New Tenant:</h2>

          <form on:submit|preventDefault={handleSubmit}>
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-3">Tenant Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="first_name"
                  >
                    First Name
                  </label>
                  <input
                    type="text"
                    id="first_name"
                    bind:value={formData.first_name}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>

                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="last_name"
                  >
                    Last Name
                  </label>
                  <input
                    type="text"
                    id="last_name"
                    bind:value={formData.last_name}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>

                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="unit_number"
                  >
                    Unit Number
                  </label>
                  <input
                    type="text"
                    id="unit_number"
                    bind:value={formData.unit_number}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>

                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="move_in_date"
                  >
                    Move-in Date
                  </label>
                  <input
                    type="date"
                    id="move_in_date"
                    bind:value={formData.move_in_date}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>
              </div>
            </div>

            <!-- Lease Information Section -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-3">Lease Information</h3>
              <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="start_date"
                  >
                    Lease Start Date
                  </label>
                  <input
                    type="date"
                    id="start_date"
                    bind:value={formData.start_date}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>

                <div class="mb-4">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="end_date"
                  >
                    Lease End Date
                  </label>
                  <input
                    type="date"
                    id="end_date"
                    bind:value={formData.end_date}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>

                <div class="mb-4 col-span-2">
                  <label
                    class="block text-gray-700 text-sm font-bold mb-2"
                    for="rent_amount"
                  >
                    Rent Amount
                  </label>
                  <input
                    type="number"
                    id="rent_amount"
                    step="0.01"
                    bind:value={formData.rent_amount}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  />
                </div>
              </div>
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
