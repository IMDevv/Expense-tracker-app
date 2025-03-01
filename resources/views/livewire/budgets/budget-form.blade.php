<div>
    <x-button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700">
        <x-icon name="plus" class="w-4 h-4 mr-2" /> Set Budget
    </x-button>

    <x-modal wire:model="showModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $isEditing ? 'Edit Budget' : 'Set New Budget' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <!-- Amount -->
                <div>
                    <x-label for="amount" value="Budget Amount" />
                    <x-input wire:model="amount" type="number" step="0.01" class="mt-1 block w-full" required />
                    <x-input-error for="amount" class="mt-2" />
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="start_date" value="Start Date" />
                        <x-input wire:model="start_date" type="date" class="mt-1 block w-full" required />
                        <x-input-error for="start_date" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="end_date" value="End Date" />
                        <x-input wire:model="end_date" type="date" class="mt-1 block w-full" required />
                        <x-input-error for="end_date" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button wire:click="$set('showModal', false)" wire:loading.attr="disabled">
                        Cancel
                    </x-secondary-button>
                    <x-button type="submit" wire:loading.attr="disabled">
                        {{ $isEditing ? 'Update' : 'Save' }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>
</div> 