<script lang="ts">
    import { goto } from "$app/navigation";
    import { api } from "$lib/services/api";
    import Swal from "sweetalert2";

    let email = "";
    let isSubmitting = false;

    async function handleSubmit(event: SubmitEvent) {
        event.preventDefault();
        isSubmitting = true;

        try {
            const response = await api.post("forgot-password", { email });

            if (response.status.remarks === "success") {
                await Swal.fire({
                    title: "Check your email",
                    text: "We've sent you instructions to reset your password",
                    icon: "success",
                    confirmButtonText: "OK",
                });
                goto("/login");
            }
        } catch (err: any) {
            await Swal.fire({
                title: "Error",
                text: err.message || "Failed to send reset instructions",
                icon: "error",
                confirmButtonText: "OK",
            });
        } finally {
            isSubmitting = false;
        }
    }
</script>

<!-- header -->
<div class="flex justify-between">
    <!-- logo -->
    <div class="flex items-center gap-2.5">
        <!-- svelte-ignore element_invalid_self_closing_tag -->
        <div class="w-3 h-9 bg-teal rounded-full" />
        <h1 class="font-semibold text-midnight text-xs flex flex-col">
            aricio<span class="text-teal">real estate</span>
        </h1>
    </div>

    <!-- back to login -->
    <button
        class="text-xs text-slate font-semibold border-2 border-slate p-2 w-32 rounded-full text-center flex justify-center cursor-pointer hover:bg-slate hover:text-white hover:font-medium transition-all"
        on:click={() => goto("/login")}
    >
        Back to Login
    </button>
</div>

<!-- main content -->
<div class="flex justify-center items-center min-h-[calc(100vh-200px)]">
    <div class="w-full max-w-md px-4">
        <form class="flex flex-col w-full p-12" on:submit={handleSubmit}>
            <h1
                class="font-black text-3xl font-inter text-teal mb-2 text-center"
            >
                Reset Password
            </h1>
            <p class="text-slate text-sm text-center mb-6">
                Enter your email address and we'll send you instructions to
                reset your password.
            </p>

            <input
                type="email"
                bind:value={email}
                required
                placeholder="your@email.com"
                class="rounded-xl text-xs border-2 px-4 py-3 text-midnight placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"
            />

            <button
                type="submit"
                class="mt-4 font-medium px-4 py-3 text-sm rounded-xl bg-midnight text-white disabled:bg-drop disabled:text-muted w-full"
                disabled={!email || isSubmitting}
            >
                {isSubmitting ? "Sending..." : "Send Reset Instructions"}
            </button>
        </form>
    </div>
</div>
