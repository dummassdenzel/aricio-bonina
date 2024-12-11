<script lang="ts">
  import { goto } from "$app/navigation";
  import { onMount } from "svelte";

  let scrollToFeatures = () => {
    const featuresSection = document.getElementById("features");
    if (featuresSection) {
      featuresSection.scrollIntoView({ behavior: "smooth" });
    }
  };

  let scrollToContact = () => {
    const contactSection = document.getElementById("contact");
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: "smooth" });
    }
  };

  onMount(() => {});
</script>

<section class="p-4 sm:p-8 md:p-14">
  <div
    class="flex flex-col gap-3 sm:gap-5 justify-center items-center align-middle p-4 sm:p-6 md:p-10"
  >
    <!-- hero text -->
    <div class="text-center flex flex-col gap-4 sm:gap-7">
      <h1
        class="font-black font-inter text-4xl sm:text-5xl md:text-6xl text-midnight"
      >
        Aricio Bonina<br />Real Estate Leasing
      </h1>
      <p class="text-muted text-sm sm:text-base">
        Explore our available units and find the perfect space to call home.
      </p>
    </div>

    <!-- action buttons -->
    <div
      class="flex flex-col sm:flex-row justify-center gap-3 mt-8 sm:mt-12 md:mt-20 w-full sm:w-auto"
    >
      <button
        class="animation font-semibold text-slate text-xs border-2 border-slate rounded-full p-3 sm:p-4 w-full sm:w-40 hover:bg-slate hover:text-white hover:font-medium"
        on:click={scrollToFeatures}>View Features</button
      >
      <button
        class="bg-slate font-medium text-white text-xs rounded-full p-3 sm:p-4 w-full sm:w-40 hover:bg-transparent hover:text-slate hover:border-2 hover:border-slate hover:font-medium"
        on:click={() => goto("/login")}>Login</button
      >
    </div>
  </div>
</section>
