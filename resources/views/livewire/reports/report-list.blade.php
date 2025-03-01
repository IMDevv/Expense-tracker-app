<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-white">Reports</h2>
        <div class="flex items-center gap-4">
            <div class="w-72">
                <x-form-input
                    wire:model.live="dateRange"
                    placeholder="Select date range"
                    x-data
                    x-init="
                        flatpickr($el, {
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            defaultDate: ['{{ $startDate->format('Y-m-d') }}', '{{ $endDate->format('Y-m-d') }}']
                        })
                    "
                />
            </div>
            <x-secondary-button wire:click="exportToCsv">
                Export CSV
            </x-secondary-button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-white mb-2">Total Expenses</h3>
            <p class="text-2xl font-bold text-white">
                KES {{ number_format($totalExpenses, 2) }}
            </p>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-white mb-2">Daily Average</h3>
            <p class="text-2xl font-bold text-white">
                KES {{ number_format($averageDaily, 2) }}
            </p>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-white mb-2">Categories</h3>
            <p class="text-2xl font-bold text-white">
                {{ $categoryBreakdown->count() }}
            </p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category Distribution -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-white mb-4">Category Distribution</h3>
            <div class="aspect-square">
                <canvas x-data="{ 
                    chart: null,
                    init() {
                        this.initChart();
                        this.$wire.on('initCharts', ({categoryData}) => {
                            if (this.chart) {
                                this.chart.data.labels = categoryData.labels;
                                this.chart.data.datasets[0].data = categoryData.data;
                                this.chart.update();
                            } else {
                                this.initChart();
                            }
                        });
                    },
                    initChart() {
                        this.chart = new Chart(this.$el, {
                            type: 'doughnut',
                            data: {
                                labels: {{ Js::from($categoryBreakdown->pluck('category')) }},
                                datasets: [{
                                    data: {{ Js::from($categoryBreakdown->pluck('total')) }},
                                    backgroundColor: [
                                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                                        '#8B5CF6', '#EC4899', '#6366F1'
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
                                            color: '#fff'
                                        }
                                    }
                                }
                            }
                        });
                    }
                }" x-init="init()"></canvas>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-white mb-4">Monthly Trend</h3>
            <div class="aspect-square">
                <canvas x-data="{ 
                    chart: null,
                    init() {
                        this.initChart();
                        this.$wire.on('initCharts', ({trendData}) => {
                            if (this.chart) {
                                this.chart.data.labels = trendData.labels;
                                this.chart.data.datasets[0].data = trendData.data;
                                this.chart.update();
                            } else {
                                this.initChart();
                            }
                        });
                    },
                    initChart() {
                        this.chart = new Chart(this.$el, {
                            type: 'line',
                            data: {
                                labels: {{ Js::from($monthlyTrends->pluck('month')) }},
                                datasets: [{
                                    label: 'Expenses',
                                    data: {{ Js::from($monthlyTrends->pluck('total')) }},
                                    borderColor: '#3B82F6',
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: '#fff' },
                                        grid: { color: '#374151' }
                                    },
                                    x: {
                                        ticks: { color: '#fff' },
                                        grid: { color: '#374151' }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        labels: { color: '#fff' }
                                    }
                                }
                            }
                        });
                    }
                }" x-init="init()"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Expenses -->
    <div class="bg-gray-800 rounded-lg overflow-hidden">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-medium text-white">Top Expenses</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @foreach($topExpenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $expense->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $expense->category }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                {{ $expense->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 text-right">
                                KES {{ number_format($expense->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach($this->getBudgetsByMonth() as $month => $budgets)
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-200">{{ $month }}</h3>
            
            @foreach($budgets as $budget)
                <div class="mt-4 bg-gray-800 rounded-lg p-4">
                    <h4 class="text-md font-medium text-gray-300">
                        {{ $budget->category }} Budget
                    </h4>
                    <div class="mt-2 grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-400">Allocated</span>
                            <p class="text-gray-200">KES {{ number_format($budget->amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-400">Spent</span>
                            <p class="text-gray-200">KES {{ number_format($budget->spent, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-400">Remaining</span>
                            <p class="text-gray-200">KES {{ number_format($budget->remaining, 2) }}</p>
                        </div>
                    </div>
                    
                    <!-- Associated Expenses -->
                    @if($budget->expenses->count() > 0)
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-400">Associated Expenses</h5>
                            <div class="mt-2 space-y-2">
                                @foreach($budget->expenses as $expense)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-300">{{ $expense->date->format('M d, Y') }}</span>
                                        <span class="text-gray-300">{{ $expense->description }}</span>
                                        <span class="text-gray-200">KES {{ number_format($expense->amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div> 