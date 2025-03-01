<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-white">Category Management</h2>
        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'create-category')">
            Create Category
        </x-primary-button>
    </div>

    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @foreach($categories as $category)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            {{ $category->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            {{ $category->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button wire:click="toggleStatus({{ $category->id }})"
                                    class="px-2 py-1 rounded text-xs font-medium {{ $category->is_active ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button wire:click="editCategory({{ $category->id }})"
                                    class="text-indigo-400 hover:text-indigo-300">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create Category Modal -->
    <x-modal name="create-category">
        <div class="p-6">
            <h2 class="text-lg font-medium text-white">Create New Category</h2>
            <form wire:submit="createCategory" class="mt-6 space-y-6">
                <div>
                    <x-form-input
                        label="Name"
                        wire:model="name"
                        required
                    />
                </div>

                <div>
                    <x-form-input
                        label="Description"
                        wire:model="description"
                    />
                </div>

                <div class="flex justify-end gap-4">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Create Category
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div> 