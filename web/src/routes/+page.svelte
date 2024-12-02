<script lang="ts">
  import Header from "$lib/components/landing/header.svelte";
  import Hero from "$lib/components/landing/hero.svelte";
  import About from "$lib/components/landing/about.svelte";
  import Features from "$lib/components/landing/features.svelte";
  import Contact from "$lib/components/landing/contact.svelte";
  import Footer from "$lib/components/landing/footer.svelte";
</script>

<section>
  <div class="flex flex-col gap-20 justify-center">
    <Header />
    <Hero />
    <About />
    <Features />
    <Contact />
    <Footer />
  </div>
</section>
