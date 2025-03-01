<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Period Selector -->
            <div class="mb-6">
                <x-label for="period" value="Time Period" />
                <select wire:model.live="period" id="period" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Expense Trend -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Expense Trend</h3>
                    <div class="h-64">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Category Distribution -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Category Distribution</h3>
                    <div class="h-64">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            let trendChart = null;
            let categoryChart = null;

            function initCharts() {
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                const categoryCtx = document.getElementById('categoryChart').getContext('2d');

                // Trend Chart
                trendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData->pluck('date')),
                        datasets: [{
                            label: 'Daily Expenses',
                            data: @json($chartData->pluck('total')),
                            borderColor: 'rgb(99, 102, 241)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Category Chart
                categoryChart = new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryData->pluck('label')),
                        datasets: [{
                            data: @json($categoryData->pluck('value')),
                            backgroundColor: [
                                'rgb(99, 102, 241)',
                                'rgb(251, 146, 60)',
                                'rgb(147, 51, 234)',
                                'rgb(236, 72, 153)',
                                'rgb(34, 211, 238)',
                                'rgb(16, 185, 129)',
                                'rgb(245, 158, 11)',
                                'rgb(239, 68, 68)',
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Initialize charts
            initCharts();

            // Update charts when data changes
            Livewire.on('updateCharts', (data) => {
                trendChart.data.labels = data.chartData.map(item => item.date);
                trendChart.data.datasets[0].data = data.chartData.map(item => item.total);
                trendChart.update();

                categoryChart.data.labels = data.categoryData.map(item => item.label);
                categoryChart.data.datasets[0].data = data.categoryData.map(item => item.value);
                categoryChart.update();
            });
        });
    </script>
    @endpush
</div> 