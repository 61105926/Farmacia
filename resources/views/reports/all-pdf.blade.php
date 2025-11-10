<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General del Sistema</title>
    <style>
        @media print {
            @page {
                margin: 1cm;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            border-left: 4px solid #333;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE GENERAL DEL SISTEMA</h1>
        <p>Farmacia - Sistema de Gestión</p>
        <p>Generado el: {{ $generatedAt }}</p>
    </div>

    <!-- Estadísticas Generales -->
    <div class="section">
        <div class="section-title">ESTADÍSTICAS GENERALES</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Ventas</div>
                <div class="stat-value">Bs {{ number_format($stats['totalSales'], 2, ',', '.') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Pedidos</div>
                <div class="stat-value">{{ $stats['totalOrders'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Clientes</div>
                <div class="stat-value">{{ $stats['totalClients'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Productos</div>
                <div class="stat-value">{{ $stats['totalProducts'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pagos Pendientes</div>
                <div class="stat-value">Bs {{ number_format($stats['pendingPayments'], 2, ',', '.') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Facturas Vencidas</div>
                <div class="stat-value">{{ $stats['overdueInvoices'] }}</div>
            </div>
        </div>
    </div>

    <!-- Ventas por Mes -->
    <div class="section">
        <div class="section-title">VENTAS POR MES (ÚLTIMOS 12 MESES)</div>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Año</th>
                    <th class="text-right">Cantidad Facturas</th>
                    <th class="text-right">Total Ventas</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                @endphp
                @forelse($monthlySales as $sale)
                <tr>
                    <td>{{ $months[$sale->month - 1] ?? 'Mes' }}</td>
                    <td>{{ $sale->year }}</td>
                    <td class="text-right">{{ $sale->count }}</td>
                    <td class="text-right">Bs {{ number_format($sale->total, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No hay datos disponibles</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Top Productos -->
    <div class="section">
        <div class="section-title">TOP PRODUCTOS MÁS VENDIDOS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th class="text-right">Cantidad Vendida</th>
                    <th class="text-right">Total Ventas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="text-right">{{ number_format($product->total_quantity, 2, ',', '.') }} unidades</td>
                    <td class="text-right">Bs {{ number_format($product->total_amount, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No hay datos disponibles</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Top Clientes -->
    <div class="section">
        <div class="section-title">TOP CLIENTES POR VENTAS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th class="text-right">Cantidad Facturas</th>
                    <th class="text-right">Total Ventas</th>
                    <th class="text-right">Total Pagado</th>
                    <th class="text-right">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topClients as $index => $client)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $client->client_name }}</td>
                    <td class="text-right">{{ $client->invoice_count }}</td>
                    <td class="text-right">Bs {{ number_format($client->total_amount, 2, ',', '.') }}</td>
                    <td class="text-right">Bs {{ number_format($client->paid_amount, 2, ',', '.') }}</td>
                    <td class="text-right">Bs {{ number_format($client->balance_amount, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay datos disponibles</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema de gestión de farmacia.</p>
        <p>Para imprimir como PDF, use la función de impresión de su navegador (Ctrl+P) y seleccione "Guardar como PDF"</p>
    </div>

    <script>
        // Auto-imprimir cuando se carga la página
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

