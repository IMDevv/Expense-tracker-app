<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex space-x-4">
            <!-- Search -->
            <div>
                <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="Search expenses..." />
            </div>

            <!-- Category Filter -->
            <div>
                <x-select wire:model.live="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                            {{ $cat }}
                            @php
                                $budget = auth()->user()->budgets()
                                    ->where('category', $cat)
                                    ->where('period_start', '<=', now())
                                    ->where('period_end', '>=', now())
                                    ->first();
                                $remaining = $budget ? $budget->remaining : 0;
                            @endphp
                            (Remaining: KES {{ number_format($remaining, 2) }})
                        </option>
                    @endforeach
                </x-select>
            </div>

            <!-- Date Range -->
            <div>
                <x-input wire:model.live="dateRange" type="text" placeholder="Date range" x-data x-init="flatpickr($el, {mode: 'range'})" />
            </div>
        </div>

        <!-- Single Add Expense Button -->
        <button 
            x-data=""
            x-on:click="$dispatch('open-modal', 'add-expense')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Expense
        </button>
    </div>

    <!-- Expenses Table -->
    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">Expense List</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($expenses as $expense)
                        <tr wire:key="{{ $expense->id }}" class="hover:bg-gray-750">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $expense->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $expense->category }}
                                @php
                                    $budget = auth()->user()->budgets()
                                        ->where('category', $expense->category)
                                        ->where('period_start', '<=', $expense->date)
                                        ->where('period_end', '>=', $expense->date)
                                        ->first();
                                @endphp
                                @if($budget)
                                    <div class="mt-1">
                                        <div class="w-full bg-gray-700 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full {{ $budget->getRemainingPercentage() <= 10 ? 'bg-red-600' : ($budget->getRemainingPercentage() <= 30 ? 'bg-yellow-600' : 'bg-green-600') }}"
                                                 style="width: {{ 100 - $budget->getRemainingPercentage() }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs {{ $budget->getRemainingPercentage() <= 10 ? 'text-red-400' : ($budget->getRemainingPercentage() <= 30 ? 'text-yellow-400' : 'text-green-400') }}">
                                            {{ number_format($budget->getRemainingPercentage(), 1) }}% remaining
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                KES{{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $expense->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button wire:click="edit({{ $expense->id }})" class="text-indigo-400 hover:text-indigo-300">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $expense->id }})" wire:confirm="Are you sure you want to delete this expense?" class="ml-4 text-red-400 hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-400">
                                No expenses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-700">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- Add Expense Modal -->
    <x-modal name="add-expense" :show="false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-white">Add New Expense</h2>
            
            <form wire:submit="save" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="amount" value="Amount" />
                        <x-text-input
                            id="amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            wire:model="amount"
                            required
                        />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category" value="Category" />
                        <x-select
                            id="category"
                            class="mt-1 block w-full"
                            wire:model="category"
                            required
                        >
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                                    {{ $cat }}
                                    @php
                                        $budget = auth()->user()->budgets()
                                            ->where('category', $cat)
                                            ->where('period_start', '<=', now())
                                            ->where('period_end', '>=', now())
                                            ->first();
                                        $remaining = $budget ? $budget->remaining : 0;
                                    @endphp
                                    (Remaining: KES {{ number_format($remaining, 2) }})
                                </option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-textarea
                        id="description"
                        class="mt-1 block w-full"
                        wire:model="description"
                        rows="3"
                    />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="date" value="Date" />
                    <x-text-input
                        id="date"
                        type="date"
                        class="mt-1 block w-full"
                        wire:model="date"
                        required
                    />
                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>

                    <x-primary-button type="submit">
                        <span wire:loading.remove wire:target="save">
                            Add Expense
                        </span>
                        <span wire:loading wire:target="save">
                            Adding...
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div> 