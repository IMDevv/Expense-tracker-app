<div
    x-data="{ 
        notifications: [],
        remove(notification) {
            this.notifications = this.notifications.filter(n => n.id !== notification.id)
        },
        addNotification(newNotification) {
            // Ensure newNotification is an object
            if (typeof newNotification !== 'object') {
                newNotification = { message: newNotification };
            }

            const notification = {
                id: Date.now(),
                type: newNotification.type || 'success',
                message: newNotification.message
            }
            this.notifications.push(notification)
            setTimeout(() => this.remove(notification), 5000)
            
            // Handle modal closing if specified
            if (newNotification.close) {
                $dispatch('close-modal', newNotification.close)
            }
        }
    }"
    @notify.window="addNotification($event.detail)"
    @expense-created.window="addNotification($event.detail)"
    @budget-created.window="addNotification($event.detail)"
    class="fixed top-4 right-4 z-50 w-96 space-y-4"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="true"
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-green-600': notification.type === 'success',
                'bg-yellow-500': notification.type === 'warning',
                'bg-red-600': notification.type === 'error'
            }"
            class="p-4 rounded-lg shadow-xl"
        >
            <div class="flex items-center">
                <!-- Success Icon -->
                <template x-if="notification.type === 'success'">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>

                <!-- Warning Icon -->
                <template x-if="notification.type === 'warning'">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>

                <!-- Error Icon -->
                <template x-if="notification.type === 'error'">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </template>

                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-white" x-text="notification.message"></p>
                </div>

                <div class="ml-4">
                    <button 
                        @click="remove(notification)" 
                        class="inline-flex rounded-md p-1.5 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-current transition-colors"
                    >
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div> 