<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-white">Budgets</h2>
        <x-primary-button x-data="" x-on:click="$dispatch('open-modal', 'create-budget')">
            Create Budget
        </x-primary-button>
    </div>

    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-medium text-white">Active Budgets</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($budgets as $budget)
                        <tr class="hover:bg-gray-750">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $budget->category }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                KES {{ number_format($budget->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                KES {{ number_format($budget->spent, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                KES {{ number_format($budget->remaining, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-700 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $budget->progress_percentage > 100 ? 'bg-red-600' : 'bg-green-600' }}"
                                         style="width: {{ min($budget->progress_percentage, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button wire:click="editBudget({{ $budget->id }})" 
                                        class="text-indigo-400 hover:text-indigo-300">
                                    Edit
                                </button>
                                <button wire:click="confirmDelete({{ $budget->id }})"
                                        class="ml-4 text-red-400 hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                No budgets found. Create one to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-700">
            {{ $budgets->links() }}
        </div>
    </div>

    <!-- Create Budget Modal -->
    <x-modal name="create-budget">
        <div class="p-6">
            <h2 class="text-lg font-medium text-white">Create New Budget</h2>
            <form wire:submit="createBudget" class="mt-6 space-y-6">
                <div>
                    <x-form-select
                        label="Category"
                        name="category"
                        wire:model="category"
                        :options="$categories"
                        required
                    />
                </div>

                <div>
                    <x-form-input
                        label="Amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        wire:model="amount"
                        required
                    />
                </div>

                <div class="flex justify-end gap-4">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Create Budget
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Edit Budget Modal -->
    <x-modal name="edit-budget">
        <div class="p-6">
            <h2 class="text-lg font-medium text-white">Edit Budget</h2>
            <form wire:submit="updateBudget" class="mt-6 space-y-6">
                <div>
                    <x-form-select
                        label="Category"
                        name="category"
                        wire:model="category"
                        :options="$categories"
                        required
                    />
                </div>

                <div>
                    <x-form-input
                        label="Amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        wire:model="amount"
                        required
                    />
                </div>

                <div class="flex justify-end gap-4">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Update Budget
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal name="confirm-delete">
        <div class="p-6">
            <h2 class="text-lg font-medium text-white">Delete Budget</h2>
            <p class="mt-4 text-sm text-gray-400">
                Are you sure you want to delete this budget? This action cannot be undone.
            </p>
            <div class="mt-6 flex justify-end gap-4">
                <x-secondary-button wire:click="cancelDelete">
                    Cancel
                </x-secondary-button>
                <x-danger-button wire:click="deleteBudget">
                    Delete Budget
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div> 