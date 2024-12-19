<script lang="ts">
    import { goto } from "$app/navigation";
    import { api } from "$lib/services/api";
    import { onMount } from "svelte";
    import Swal from "sweetalert2";

    let password = "";
    let confirmPassword = "";
    let isSubmitting = false;
    let token: string | null = null;

    onMount(() => {
        // Get token from URL after component mounts
        const url = new URL(window.location.href);
        token = url.searchParams.get("token");

        if (!token) {
            Swal.fire({
                title: "Error",
                text: "Invalid reset link",
                icon: "error",
                confirmButtonText: "OK",
            }).then(() => {
                goto("/login");
            });
        }
    });

    async function handleSubmit(event: SubmitEvent) {
        event.preventDefault();

        if (!token) {
            await Swal.fire({
                title: "Error",
                text: "Invalid reset link",
                icon: "error",
                confirmButtonText: "OK",
            });
            goto("/login");
            return;
        }

        if (password !== confirmPassword) {
            await Swal.fire({
                title: "Error",
                text: "Passwords do not match",
                icon: "error",
                confirmButtonText: "OK",
            });
            return;
        }

        if (password.length < 8) {
            await Swal.fire({
                title: "Error",
                text: "Password must be at least 8 characters long",
                icon: "error",
                confirmButtonText: "OK",
            });
            return;
        }

        isSubmitting = true;

        try {
            const response = await api.post("reset-password", {
                token,
                password,
            });

            if (response.status.remarks === "success") {
                await Swal.fire({
                    title: "Success",
                    text: "Your password has been reset successfully",
                    icon: "success",
                    confirmButtonText: "OK",
                });
                goto("/login");
            }
        } catch (err: any) {
            await Swal.fire({
                title: "Error",
                text: err.message || "Failed to reset password",
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
                class="font-black text-3xl font-inter text-teal mb-6 text-center"
            >
                Set New Password
            </h1>

            <div class="flex flex-col space-y-4">
                <input
                    type="password"
                    bind:value={password}
                    required
                    placeholder="New password"
                    class="rounded-xl text-xs border-2 px-4 py-3 text-midnight placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"
                />

                <input
                    type="password"
                    bind:value={confirmPassword}
                    required
                    placeholder="Confirm new password"
                    class="rounded-xl text-xs border-2 px-4 py-3 text-midnight placeholder:text-sm focus:outline-none focus:ring-0 focus:border-slate"
                />
            </div>

            <button
                type="submit"
                class="mt-6 font-medium px-4 py-3 text-sm rounded-xl bg-midnight text-white disabled:bg-drop disabled:text-muted w-full"
                disabled={!password || !confirmPassword || isSubmitting}
            >
                {isSubmitting ? "Resetting..." : "Reset Password"}
            </button>
        </form>
    </div>
</div>
