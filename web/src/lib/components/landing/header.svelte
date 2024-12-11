<script lang="ts">
  import { goto } from "$app/navigation";
  let isMenuOpen = false;
</script>

<header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md">
  <div class="flex justify-between items-center p-4 md:p-6 max-w-7xl mx-auto">
    <!-- logo -->
    <div class="flex items-center gap-2.5">
      <div class="w-2 sm:w-3 h-7 sm:h-9 bg-teal rounded-full"></div>
      <h1
        class="font-semibold text-midnight text-[10px] sm:text-xs flex flex-col"
      >
        aricio <span class="text-teal">real estate</span>
      </h1>
    </div>

    <!-- navigation bar -->
    <nav class="hidden lg:flex items-center gap-10">
      <a
        href="/"
        class="font-semibold text-slate text-xs hover:text-midnight transition-colors"
      >
        Home
      </a>
      <a
        href="#about"
        class="font-semibold text-slate text-xs hover:text-midnight transition-colors"
      >
        About
      </a>
      <a
        href="#features"
        class="font-semibold text-slate text-xs hover:text-midnight transition-colors"
      >
        Features
      </a>
      <a
        href="#contact"
        class="font-semibold text-slate text-xs hover:text-midnight transition-colors"
      >
        Contact
      </a>
    </nav>

    <!-- login button -->
    <button
      on:click={() => goto("/login")}
      class="hidden lg:flex text-xs text-slate font-semibold border-2 border-slate p-2 w-32 rounded-full text-center justify-center cursor-pointer hover:bg-slate hover:text-white hover:font-medium transition-all"
    >
      Log In
    </button>

    <!-- hamburger menu -->
    <div class="lg:hidden flex items-center">
      <button
        on:click={() => (isMenuOpen = !isMenuOpen)}
        class="text-teal p-2 hover:bg-slate/5 rounded-lg transition-colors"
        aria-label="Toggle menu"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="#495F76"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>
    </div>
  </div>

  <!-- mobile menu -->
  {#if isMenuOpen}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
      class="lg:hidden fixed inset-0 bg-black/20 backdrop-blur-sm"
      on:click={() => (isMenuOpen = false)}
    >
      <div
        class="absolute right-0 top-0 h-screen w-64 bg-white shadow-lg p-6"
        on:click|stopPropagation
      >
        <!-- close button -->
        <!-- svelte-ignore a11y_consider_explicit_label -->
        <button
          class="absolute top-4 right-4 p-2 hover:bg-slate/5 rounded-lg transition-colors"
          on:click={() => (isMenuOpen = false)}
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="#495F76"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <!-- mobile menu items -->
        <nav class="flex flex-col gap-4 mt-8">
          <a
            href="/"
            class="font-semibold text-slate text-sm py-2 hover:text-midnight transition-colors"
            on:click={() => (isMenuOpen = false)}
          >
            Home
          </a>
          <a
            href="#about"
            class="font-semibold text-slate text-sm py-2 hover:text-midnight transition-colors"
            on:click={() => (isMenuOpen = false)}
          >
            About
          </a>
          <a
            href="#features"
            class="font-semibold text-slate text-sm py-2 hover:text-midnight transition-colors"
            on:click={() => (isMenuOpen = false)}
          >
            Features
          </a>
          <a
            href="#contact"
            class="font-semibold text-slate text-sm py-2 hover:text-midnight transition-colors"
            on:click={() => (isMenuOpen = false)}
          >
            Contact
          </a>

          <button
            on:click={() => {
              goto("/login");
              isMenuOpen = false;
            }}
            class="mt-4 text-sm text-slate font-semibold border-2 border-slate p-2 rounded-full text-center hover:bg-slate hover:text-white hover:font-medium transition-all"
          >
            Log In
          </button>
        </nav>
      </div>
    </div>
  {/if}
</header>
