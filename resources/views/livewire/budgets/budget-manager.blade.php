<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Budget Management</h2>
        <livewire:budgets.budget-form />
    </div>

    <!-- Current Budget Status -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Current Budget</h3>
            @if($currentBudget)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            KES{{ number_format($currentBudget->amount, 2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $currentBudget->start_date->format('M d, Y') }} - 
                            {{ $currentBudget->end_date->format('M d, Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm font-semibold">
                            @php
                                $percentage = ($currentBudget->spent / $currentBudget->amount) * 100;
                            @endphp
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 rounded-full {{ $percentage > 100 ? 'bg-red-500' : 'bg-green-500' }}"
                                             style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                </div>
                                <span class="ml-2 {{ $percentage > 100 ? 'text-red-500' : 'text-green-500' }}">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                            </div>
                        </dd>
                    </div>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">No active budget found.</p>
            @endif
        </div>
    </div>

    <!-- Budget History -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Budget History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Spent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($budgets as $budget)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${{ number_format($budget->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    ${{ number_format($budget->spent, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $percentage = ($budget->spent / $budget->amount) * 100;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full">
                                            <div class="h-2 rounded-full {{ $percentage > 100 ? 'bg-red-500' : 'bg-green-500' }}"
                                                 style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="ml-2 {{ $percentage > 100 ? 'text-red-500' : 'text-green-500' }}">
                                            {{ number_format($percentage, 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="$dispatch('edit-budget', { budget: {{ $budget->id }} })"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No budgets found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 