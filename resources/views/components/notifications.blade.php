<div
    x-data="{ show: false, message: '' }"
    x-on:profile-updated.window="show = true; message = 'Profile updated successfully!'"
    x-on:password-updated.window="show = true; message = 'Password updated successfully!'"
    x-show="show"
    x-transition
    x-init="setTimeout(() => show = false, 2000)"
    class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg"
    style="display: none;"
>
    <p x-text="message"></p>
</div> 