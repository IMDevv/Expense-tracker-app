<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" 
                         alt="{{ auth()->user()->name }}"
                         class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-700" />
                @else
                    <div class="h-16 w-16 rounded-full bg-gray-700 flex items-center justify-center ring-2 ring-gray-600">
                        <span class="text-2xl font-medium text-gray-300">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-semibold text-white">
                        Welcome back, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-gray-400">Here's your expense overview</p>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Expenses Card -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-white mb-2">Total Expenses</h3>
                        <p class="text-2xl font-bold text-white">
                            KES {{ number_format($totalExpenses, 2) }}
                        </p>
                    </div>

                    <!-- Monthly Expenses Card -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-white mb-2">This Month</h3>
                        <p class="text-2xl font-bold text-white">
                            KES {{ number_format($monthlyExpenses, 2) }}
                        </p>
                    </div>

                    <!-- Budget Card -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-white mb-2">Monthly Budget</h3>
                        <p class="text-2xl font-bold text-white">
                            KES {{ number_format($currentBudget, 2) }}
                        </p>
                    </div>

                    <!-- Remaining Budget Card -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-white mb-2">Remaining Budget</h3>
                        <p class="text-2xl font-bold {{ $remainingBudget < 0 ? 'text-red-500' : 'text-green-500' }}">
                            KES {{ number_format($remainingBudget, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Active Budgets -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-white mb-4">Active Budgets</h3>
                    <div class="space-y-4">
                        @forelse($budgets as $budget)
                            <div class="bg-gray-750 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-white font-medium">{{ $budget['category'] }}</h4>
                                    <span class="text-gray-400">
                                        KES {{ number_format($budget['spent'], 2) }} / {{ number_format($budget['amount'], 2) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $budget['progress'] > 100 ? 'bg-red-600' : 'bg-green-600' }}"
                                         style="width: {{ min($budget['progress'], 100) }}%"></div>
                                </div>
                                <div class="mt-2 text-sm text-gray-400">
                                    Remaining: KES {{ number_format($budget['remaining'], 2) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400">No active budgets found.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Category Distribution -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-white mb-4">Category Distribution</h3>
                        <div class="aspect-square">
                            <canvas x-data x-init="
                                new Chart($el, {
                                    type: 'doughnut',
                                    data: {
                                        labels: {{ Js::from($categoryTotals->pluck('category')) }},
                                        datasets: [{
                                            data: {{ Js::from($categoryTotals->pluck('total')) }},
                                            backgroundColor: [
                                                'rgb(99, 102, 241)',  // Indigo
                                                'rgb(16, 185, 129)',  // Green
                                                'rgb(245, 158, 11)',  // Orange
                                                'rgb(239, 68, 68)',   // Red
                                                'rgb(147, 51, 234)',  // Purple
                                                'rgb(236, 72, 153)',  // Pink
                                                'rgb(34, 211, 238)',  // Cyan
                                            ]
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    color: 'rgba(255, 255, 255, 0.7)',
                                                    padding: 20,
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        let label = context.label || '';
                                                        let value = context.parsed || 0;
                                                        return `${label}: KES ${value.toLocaleString()}`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                })
                            "></canvas>
                        </div>
                    </div>

                    <!-- Monthly Trend -->
                    <div class="bg-gray-800 rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-white">Monthly Trend</h3>
                            <div class="text-sm text-gray-400">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Last 6 months spending pattern
                                </span>
                            </div>
                        </div>
                        
                        <div class="relative" style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-400">
                            <p>This chart shows your monthly spending trends over the last 6 months, broken down by category. 
                               Use this to identify spending patterns and adjust your budgets accordingly.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-white mb-4">Recent Expenses</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse($recentExpenses as $expense)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            {{ $expense->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            {{ $expense->category }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            KES{{ number_format($expense->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-400">
                                            No recent expenses
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const darkMode = {
            grid: {
                color: 'rgba(255, 255, 255, 0.1)'
            },
            ticks: {
                color: 'rgba(255, 255, 255, 0.7)'
            }
        };

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryTotals->pluck('category')),
                    datasets: [{
                        data: @json($categoryTotals->pluck('total')),
                        backgroundColor: [
                            'rgb(99, 102, 241)',  // Indigo
                            'rgb(16, 185, 129)',  // Green
                            'rgb(245, 158, 11)',  // Orange
                            'rgb(239, 68, 68)',   // Red
                            'rgb(147, 51, 234)',  // Purple
                            'rgb(236, 72, 153)',  // Pink
                            'rgb(34, 211, 238)',  // Cyan
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: 'rgba(255, 255, 255, 0.7)',
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    return `${label}: KES ${value.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($monthlyTrend->pluck('month')),
                    datasets: [{
                        label: 'Monthly Expenses',
                        data: @json($monthlyTrend->pluck('total')),
                        borderColor: 'rgb(99, 102, 241)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ...darkMode,
                            ticks: {
                                ...darkMode.ticks,
                                callback: function(value) {
                                    return 'KES ' + value.toLocaleString();
                                }
                            }
                        },
                        x: darkMode
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush 