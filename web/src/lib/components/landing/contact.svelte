<section id="contact" class="p-24">
    <div class="flex justify-center gap-20 bg-back rounded-2xl">
        <!-- left side -->
        <div class="flex gap-2 items-center">
            <!-- call us -->
            <div class="flex flex-col gap-2">
                <p class="text-slate font-bold">Call Us</p>
                <p class="text-muted text-xs">Call our team Mon-Fri from 8am to 5pm.</p>
                <div class="flex gap-2 items-center mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <p class="text-xs text-slate font-semibold">+63 987 6543 210</p>
                </div>
                <!-- chat with us -->
                <div class="mt-5 flex flex-col gap-2">
                    <p class="text-slate font-bold">Chat with Us</p>
                    <p class="text-muted text-xs">Speak to our friendly team via email.</p>
                    <div class="flex gap-2 items-center mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <p class="text-xs text-slate font-semibold">aricio.bonina@sample.com</p>
                    </div>
                    <div class="flex gap-2 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        <p class="text-xs text-slate font-semibold">Sea Pearl Residences</p>
                    </div>
                </div>
                <!-- visit  us -->
                <div class="flex flex-col gap-2 mt-10">
                    <p class="text-slate font-bold">Visit Us</p>
                    <p class="text-muted text-xs">Chat to us in person at our Sea Pearl HQ.</p>
                    <div class="flex gap-2 items-center mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#495F76" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-navigation"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                        <p class="text-xs text-slate font-semibold">69 Gallagher Street, East Tapinac, Olongapo City</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- right side -->
        <div class="flex justify-center rounded-2xl">
        <form class="flex flex-col gap-5 p-10 w-100">
            <!-- name -->
            <div class="flex gap-3">
                <!-- first name -->
                <div class="flex flex-col gap-1 w-full">
                    <label for="firstname" class="block text-xs font-medium text-slate">First name</label>
                    <input type="text" id="firstname" placeholder="First name" required class="rounded-lg text-xs p-2 border-2 text-slate font-medium focus:outline-slate"/>
                </div>
                <!-- last name -->
                <div class="flex flex-col gap-1 w-full">
                    <label for="lastname" class="block text-xs font-medium text-slate">Last name</label>
                    <input type="text" id="lastname" placeholder="Last name" required class="rounded-lg text-xs p-2 border-2 text-slate font-medium focus:outline-slate"/>
                </div>
            </div>
            <!-- email -->
            <div class="flex flex-col gap-1">
                <label for="email" class="block text-xs font-medium text-slate">Email</label>
                <input type="email" id="email" placeholder="your@email.com" required class="rounded-lg text-xs p-2 border-2 text-slate font-medium focus:outline-slate"/>
            </div>
            <!-- phone number -->
            <div class="flex flex-col gap-1">
                <label for="number" class="block text-xs font-medium text-slate">Phone number</label>
                <input type="text" id="number" placeholder="+63" required class="rounded-lg text-xs p-2 border-2 text-slate font-medium focus:outline-slate"/>
            </div>
            <!-- message -->
            <div class="flex flex-col gap-1">
                <label for="textarea" class="block text-xs font-medium text-slate">Message</label>
                <textarea id="message" placeholder="Leave us a message..." required class="rounded-lg text-xs p-2 border-2 text-slate font-medium focus:outline-slate"></textarea>
            </div>
            <!-- submit button -->
            <div class="flex flex-col gap-1">
                <button class="text-xs bg-slate p-3 rounded-lg text-white">Send message</button>
            </div>
        </form>
        </div>
    </div>
</section>
  