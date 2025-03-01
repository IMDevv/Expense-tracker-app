<div>
    <x-button wire:click="openModal">
        <x-icon name="plus" class="w-4 h-4 mr-2" /> Add Expense
    </x-button>

    <x-modal wire:model="showModal">
        <div class="p-6">
            <h2 class="text-lg font-medium mb-4">
                {{ $isEditing ? 'Edit Expense' : 'Add New Expense' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <!-- Amount -->
                <div>
                    <x-label for="amount" value="Amount" />
                    <x-input wire:model="amount" type="number" step="0.01" class="mt-1 block w-full" required />
                    <x-input-error for="amount" class="mt-2" />
                </div>

                <!-- Category -->
                <div>
                    <x-label for="category" value="Category" />
                    <div class="mt-1 flex">
                        <x-select wire:model="category" class="flex-1">
                            <option value="">Select Category</option>
                            @foreach($predefinedCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                            <option value="custom">Custom Category</option>
                        </x-select>
                    </div>
                    @if($category === 'custom')
                        <x-input wire:model="customCategory" type="text" class="mt-2 block w-full" placeholder="Enter custom category" />
                    @endif
                    <x-input-error for="category" class="mt-2" />
                </div>

                <!-- Description -->
                <div>
                    <x-label for="description" value="Description" />
                    <x-textarea wire:model="description" class="mt-1 block w-full" rows="3" />
                    <x-input-error for="description" class="mt-2" />
                </div>

                <!-- Date -->
                <div>
                    <x-label for="date" value="Date" />
                    <x-input wire:model="date" type="date" class="mt-1 block w-full" max="{{ now()->format('Y-m-d') }}" required />
                    <x-input-error for="date" class="mt-2" />
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