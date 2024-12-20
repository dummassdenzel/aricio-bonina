<script lang="ts">
    import { auth } from "$lib/stores/auth";
    import { goto } from "$app/navigation";

    let dropdownOpen = false;
    let mobileMenuOpen = false;
    let activeLink = "dashboard";

    function toggleDropdown() {
        dropdownOpen = !dropdownOpen;
    }

    function toggleMobileMenu() {
        mobileMenuOpen = !mobileMenuOpen;
    }

    function handleLogout() {
        auth.logout();
        goto("/");
    }

    function setActiveLink(link: string) {
        activeLink = link;
        mobileMenuOpen = false;
    }
</script>

<!-- svelte-ignore a11y_click_events_have_key_events -->
<header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md">
    <div class="flex justify-between items-center p-4 md:p-6">
        <!-- logo -->
        <div class="flex items-center gap-2">
            <div class="w-2 sm:w-3 h-7 sm:h-9 bg-teal rounded-full"></div>
            <h1
                class="font-semibold text-midnight text-[10px] sm:text-xs flex flex-col"
            >
                aricio<span class="text-teal">real estate</span>
            </h1>
        </div>

        <!-- desktop navigation -->
        <nav
            class="hidden lg:flex items-center gap-8 bg-back rounded-full p-3 px-8"
        >
            <!-- dashboard -->
            <a
                href="/manager/dashboard"
                class="font-semibold text-xs flex items-center gap-2 transition-colors"
                on:click={() => setActiveLink("dashboard")}
                class:text-teal={activeLink === "dashboard"}
                class:text-muted={activeLink !== "dashboard"}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={activeLink === "dashboard" ? "#314A60" : "#686868"}
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect width="7" height="9" x="3" y="3" rx="1" />
                    <rect width="7" height="5" x="14" y="3" rx="1" />
                    <rect width="7" height="9" x="14" y="12" rx="1" />
                    <rect width="7" height="5" x="3" y="16" rx="1" />
                </svg>
                Dashboard
            </a>

            <!-- unit -->
            <a
                href="/manager/unit"
                class="font-semibold text-xs flex items-center gap-2 transition-colors"
                on:click={() => setActiveLink("unit")}
                class:text-teal={activeLink === "unit"}
                class:text-muted={activeLink !== "unit"}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={activeLink === "unit" ? "#314A60" : "#686868"}
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M3 20v-8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8" />
                    <path d="M5 10V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v4" />
                    <path d="M3 18h18" />
                </svg>
                Unit
            </a>

            <!-- tenants -->
            <a
                href="/manager/tenant"
                class="font-semibold text-xs flex items-center gap-2 transition-colors"
                on:click={() => setActiveLink("tenant")}
                class:text-teal={activeLink === "tenant"}
                class:text-muted={activeLink !== "tenant"}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={activeLink === "tenant" ? "#314A60" : "#686868"}
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M18 21a8 8 0 0 0-16 0" />
                    <circle cx="10" cy="8" r="5" />
                    <path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3" />
                </svg>
                Tenants
            </a>

            <!-- billing -->
            <a
                href="/manager/billing"
                class="font-semibold text-xs flex items-center gap-2 transition-colors"
                on:click={() => setActiveLink("billing")}
                class:text-teal={activeLink === "billing"}
                class:text-muted={activeLink !== "billing"}
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke={activeLink === "billing" ? "#314A60" : "#686868"}
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"
                    />
                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                </svg>
                History
            </a>
        </nav>

        <!-- user profile and mobile menu button -->
        <div class="flex items-center gap-4">
            <!-- user profile -->
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 bg-lightteal rounded-full flex items-center justify-center text-teal text-xs font-bold"
                >
                    {#if $auth.user?.first_name}
                        {$auth.user.first_name[0].toUpperCase()}
                    {/if}
                </div>
                <button
                    class="hidden sm:block text-teal text-xs font-semibold hover:text-slate transition-colors"
                    on:click={toggleDropdown}
                >
                    {$auth.user?.first_name}
                </button>
            </div>

            <!-- mobile menu button -->
            <button
                class="lg:hidden p-2 hover:bg-slate/5 rounded-lg transition-colors"
                on:click={toggleMobileMenu}
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

    <!-- mobile navigation menu -->
    {#if mobileMenuOpen}
        <!-- svelte-ignore a11y_no_static_element_interactions -->
        <div
            class="lg:hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-50"
            on:click={() => (mobileMenuOpen = false)}
        >
            <div
                class="absolute right-0 top-0 h-screen w-64 bg-white shadow-lg p-6"
                on:click|stopPropagation
            >
                <!-- close button -->
                <!-- svelte-ignore a11y_consider_explicit_label -->
                <button
                    class="absolute top-4 right-4 p-2 hover:bg-slate/5 rounded-lg transition-colors"
                    on:click={() => (mobileMenuOpen = false)}
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
                        href="/manager/dashboard"
                        class="font-semibold text-sm py-2 flex items-center gap-2 transition-colors"
                        class:text-teal={activeLink === "dashboard"}
                        class:text-muted={activeLink !== "dashboard"}
                        on:click={() => setActiveLink("dashboard")}
                    >
                        Dashboard
                    </a>
                    <a
                        href="/manager/unit"
                        class="font-semibold text-sm py-2 flex items-center gap-2 transition-colors"
                        class:text-teal={activeLink === "unit"}
                        class:text-muted={activeLink !== "unit"}
                        on:click={() => setActiveLink("unit")}
                    >
                        Unit
                    </a>
                    <a
                        href="/manager/tenant"
                        class="font-semibold text-sm py-2 flex items-center gap-2 transition-colors"
                        class:text-teal={activeLink === "tenant"}
                        class:text-muted={activeLink !== "tenant"}
                        on:click={() => setActiveLink("tenant")}
                    >
                        Tenants
                    </a>
                    <a
                        href="/manager/billing"
                        class="font-semibold text-sm py-2 flex items-center gap-2 transition-colors"
                        class:text-teal={activeLink === "billing"}
                        class:text-muted={activeLink !== "billing"}
                        on:click={() => setActiveLink("billing")}
                    >
                        Billing
                    </a>

                    <button
                        on:click={handleLogout}
                        class="mt-4 text-sm text-red font-semibold p-2 rounded-lg text-center hover:bg-red/5 transition-colors"
                    >
                        Log Out
                    </button>
                </nav>
            </div>
        </div>
    {/if}

    <!-- user dropdown -->
    {#if dropdownOpen}
        <div
            class="absolute right-12 mt-2 w-28 bg-white shadow-lg border rounded-lg"
        >
            <button
                class="w-full px-4 py-2 text-red text-xs font-medium hover:bg-red/5 transition-colors text-left"
                on:click={handleLogout}
            >
                Log Out
            </button>
        </div>
    {/if}
</header>
