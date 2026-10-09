<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ESP32 - DHT11 & LEDs</title>
    <!-- Tailwind CSS (CDN para rápida integración) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Monitoreo ESP32 + DHT11</h1>
            <p class="text-sm text-gray-500 mt-1">Últimas 100 lecturas del dispositivo</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="window.location.reload()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200">
                Actualizar
            </button>
        </div>
    </div>

    <!-- Seccion de Graficas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Gráfica de Temperatura -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-3 h-3 bg-red-500 rounded-full inline-block"></span> Evolución de Temperatura (°C)
            </h2>
            <div class="relative h-64">
                <canvas id="temperatureChart"></canvas>
            </div>
        </div>

        <!-- Gráfica de Humedad -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full inline-block"></span> Evolución de Humedad (%)
            </h2>
            <div class="relative h-64">
                <canvas id="humidityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Histórico de Lecturas</h2>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-medium">
                    Total: {{ count($records ?? []) }} registros
                </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs tracking-wider border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Fecha y Hora</th>
                    <th class="px-6 py-3">Dispositivo</th>
                    <th class="px-6 py-3">Temperatura</th>
                    <th class="px-6 py-3">Humedad</th>
                    <th class="px-6 py-3">Estado Sensor</th>
                    <th class="px-6 py-3">LED 01</th>
                    <th class="px-6 py-3">LED 02</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($records ?? [] as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">#{{ $row->id }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            {{ $row->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4">
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded font-mono text-xs">
                                        {{ $row->device }}
                                    </span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-red-600">
                            {{ number_format($row->temperature, 1) }} °C
                        </td>
                        <td class="px-6 py-4 font-semibold text-blue-600">
                            {{ $row->humidity }} %
                        </td>
                        <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ strtolower($row->status_read_dht11) === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $row->status_read_dht11 }}
                                    </span>
                        </td>
                        <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ in_array(strtolower($row->status_led_01), ['on', '1', 'active']) ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $row->status_led_01 }}
                                    </span>
                        </td>
                        <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ in_array(strtolower($row->status_led_02), ['on', '1', 'active']) ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $row->status_led_02 }}
                                    </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                            No hay lecturas registradas aún.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script para renderizar Chart.js -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Revertimos la colección de Blade para que el eje X se grafique en orden cronológico (pasado -> presente)
        const chartData = @json(($records)->reverse()->values());

        const labels = chartData.map(item => {
            const date = new Date(item.created_at);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        });

        const temperatures = chartData.map(item => item.temperature);
        const humidities = chartData.map(item => item.humidity);

        // Configuración común de estilo
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxRotation: 45, minRotation: 0 }
                },
                y: {
                    grid: { color: '#f3f4f6' }
                }
            }
        };

        // Chart de Temperatura
        new Chart(document.getElementById('temperatureChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Temperatura (°C)',
                    data: temperatures,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: commonOptions
        });

        // Chart de Humedad
        new Chart(document.getElementById('humidityChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humedad (%)',
                    data: humidities,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: commonOptions
        });
    });
</script>
</body>
</html>
