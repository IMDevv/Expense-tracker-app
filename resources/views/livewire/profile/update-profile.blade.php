<div class="space-y-6">
    <x-notifications />

    <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <header class="flex items-center gap-4">
                <div class="relative">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" 
                             alt="Profile picture" 
                             class="w-16 h-16 rounded-full object-cover">
                        <button wire:click="deleteAvatar" 
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-medium text-white">
                        {{ __('Profile Information') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ __("Update your account's profile information and email address.") }}
                    </p>
                </div>
            </header>

            <form wire:submit="updateProfile" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="avatar" :value="__('Profile Picture')" />
                    <input wire:model="avatar" type="file" id="avatar" 
                           accept="image/jpeg,image/png"
                           class="mt-1 block w-full text-sm text-gray-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-600 file:text-white
                                  hover:file:bg-indigo-700"/>
                    <div class="mt-1 text-sm text-gray-400">
                        Accepted formats: JPG, PNG. Max size: 1MB. Min dimensions: 100x100px
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>

                <div wire:loading wire:target="avatar" class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center rounded-full">
                    <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @if(!auth()->user()->google_id)
    <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <header>
                <h2 class="text-lg font-medium text-white">
                    {{ __('Update Password') }}
                </h2>

                <p class="mt-1 text-sm text-gray-400">
                    {{ __('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </header>

            <form wire:submit="updatePassword" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="current_password" :value="__('Current Password')" />
                    <x-text-input wire:model="current_password" id="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('New Password')" />
                    <x-text-input wire:model="password" id="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <header>
                <h2 class="text-lg font-medium text-red-500">
                    {{ __('Delete Account') }}
                </h2>
                <p class="mt-1 text-sm text-gray-400">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>
            </header>

            <form wire:submit.prevent="confirmAccountDeletion" class="mt-6 space-y-6">
                <div>
                    <x-input-label for="deleteConfirmPassword" :value="__('Password')" />
                    <x-text-input wire:model="deleteConfirmPassword" 
                                 id="deleteConfirmPassword" 
                                 type="password" 
                                 class="mt-1 block w-full" 
                                 placeholder="{{ __('Enter your password to confirm') }}" />
                    <x-input-error :messages="$errors->get('deleteConfirmPassword')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-danger-button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="confirmAccountDeletion"
                    >
                        <span wire:loading.remove wire:target="confirmAccountDeletion">
                            {{ __('Delete Account') }}
                        </span>
                        <span wire:loading wire:target="confirmAccountDeletion">
                            {{ __('Deleting...') }}
                        </span>
                    </x-danger-button>
                </div>
            </form>
        </div>
    </div>
</div> 