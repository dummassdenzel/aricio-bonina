<script lang="ts">
    import { onMount } from 'svelte';
  
    let scrollToFeatures = () => {
      const featuresSection = document.getElementById('features');
      if (featuresSection) {
        featuresSection.scrollIntoView({ behavior: 'smooth'   
   });
      }
    };

    let scrollToContact = () => {
      const contactSection = document.getElementById('contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth'   
   });
      }
    };
  
    onMount(() => {
      
    });
</script>

<section class="p-14">
  <div class="flex flex-col gap-5 justify-center items-center align-middle p-10">
      <!-- hero text -->
      <div class="text-center flex flex-col gap-7">
          <h1 class="font-black font-inter text-6xl text-midnight">Aricio Bonina<br>Real Estate Leasing</h1>
          <p class="text-muted">Explore our available units and find the perfect space to call home.</p>
      </div>

      <!-- action buttons -->
      <div class="flex justify-center gap-3 mt-20">
          <button class="animation font-semibold text-slate text-xs border-2 border-slate rounded-full p-4 w-40 hover:bg-slate hover:text-white hover:font-medium" on:click={scrollToFeatures}>View Features</button>
          <button class="bg-slate font-medium text-white text-xs rounded-full p-4 w-40 hover:bg-transparent hover:text-slate hover:border-2 hover:border-slate hover:font-medium" on:click={scrollToContact}>Inquire</button>
      </div>
  </div>
</section>