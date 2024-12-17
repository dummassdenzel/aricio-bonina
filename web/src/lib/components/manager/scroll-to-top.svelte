<script lang="ts">
    import { onMount } from "svelte";

    let showButton = false;

    function checkScroll() {
        showButton = window.scrollY > 200;
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    }

    onMount(() => {
        window.addEventListener("scroll", checkScroll);
        return () => window.removeEventListener("scroll", checkScroll);
    });
</script>

{#if showButton}
    <button
        on:click={scrollToTop}
        class="fixed bottom-5 left-5 bg-teal text-white p-2 rounded-full shadow-lg hover:bg-teal transition-all duration-300 z-50"
        aria-label="Scroll to top"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="lucide lucide-arrow-up"
            ><path d="m5 12 7-7 7 7" /><path d="M12 19V5" /></svg
        >
    </button>
{/if}

<style>
    button {
        opacity: 0.4;
    }
    button:hover {
        opacity: 1;
        transform: translateY(-2px);
    }
</style>
