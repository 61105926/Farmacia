<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Ventas – {{ $period }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

    /* ── Header ── */
    .header { background: linear-gradient(135deg, #1e40af 0%, #0f766e 100%); color: #fff; padding: 20px 28px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-logo { width: 70px; text-align: left; vertical-align: middle; }
    .header-logo img { max-height: 54px; max-width: 64px; }
    .header-title { text-align: left; vertical-align: middle; padding-left: 14px; }
    .header-title h1 { font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
    .header-title p  { font-size: 12px; margin-top: 3px; opacity: 0.88; }
    .header-meta { text-align: right; vertical-align: middle; font-size: 10px; opacity: 0.9; line-height: 1.6; }

    /* ── Section titles ── */
    .section-title {
        background: #1e40af; color: #fff; font-size: 11px; font-weight: 700;
        padding: 6px 12px; margin-top: 18px; margin-bottom: 8px;
        text-transform: uppercase; letter-spacing: 0.6px;
    }
    .section-title.green  { background: #0f766e; }
    .section-title.purple { background: #7c3aed; }

    /* ── Summary boxes ── */
    .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .summary-cell { width: 33.33%; padding: 4px 6px; vertical-align: top; }
    .summary-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; text-align: center; }
    .summary-card .s-label { font-size: 9.5px; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 6px; }
    .summary-card .s-value { font-size: 22px; font-weight: 800; color: #1e293b; }
    .summary-card .s-sub { font-size: 9.5px; color: #94a3b8; margin-top: 4px; }

    /* ── Monthly table ── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
    .data-table thead tr { background: #1e40af; color: #fff; }
    .data-table thead th { padding: 7px 10px; text-align: left; font-weight: 600; font-size: 10px; }
    .data-table thead th.right { text-align: right; }
    .data-table thead th.center { text-align: center; }
    .data-table tbody tr:nth-child(even) { background: #f1f5f9; }
    .data-table tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
    .data-table tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
    .data-table tbody td.center { text-align: center; }
    .data-table tfoot tr { background: #0f172a; color: #fff; font-weight: 700; }
    .data-table tfoot td { padding: 8px 10px; }
    .data-table tfoot td.right { text-align: right; }

    /* ── Mini bar ── */
    .bar-cell { padding: 7px 10px; width: 90px; vertical-align: middle; }
    .bar-bg { background: #e2e8f0; height: 8px; border-radius: 4px; }
    .bar-fill { background: #1e40af; height: 8px; border-radius: 4px; }
    .bar-fill.cobros { background: #0f766e; }

    /* ── Two columns ── */
    .two-col { width: 100%; border-collapse: collapse; }
    .col-l { width: 50%; padding-right: 10px; vertical-align: top; }
    .col-r { width: 50%; padding-left: 10px; vertical-align: top; }

    /* ── Top list ── */
    .top-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
    .top-table thead tr { background: #7c3aed; color: #fff; }
    .top-table thead th { padding: 6px 10px; text-align: left; font-size: 10px; }
    .top-table thead th.right { text-align: right; }
    .top-table tbody tr:nth-child(even) { background: #f5f3ff; }
    .top-table tbody td { padding: 6px 10px; border-bottom: 1px solid #ede9fe; }
    .top-table tbody td.right { text-align: right; }

    /* ── Rank badge ── */
    .rank { font-weight: 800; color: #7c3aed; width: 22px; text-align: center; }
    .rank-1 { color: #b45309; }
    .rank-2 { color: #94a3b8; }
    .rank-3 { color: #c2410c; }

    /* ── Footer ── */
    .footer { margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #94a3b8; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

{{-- ══ HEADER ══ --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(!empty($system['logo_path']) && file_exists($system['logo_path']))
                    <img src="{{ $system['logo_path'] }}" alt="Logo">
                @else
                    <div style="width:54px;height:54px;background:rgba(255,255,255,0.2);border-radius:8px;text-align:center;line-height:54px;font-size:20px;font-weight:700;">
                        {{ strtoupper(substr($system['name'],0,1)) }}
                    </div>
                @endif
            </td>
            <td class="header-title">
                <h1>{{ $system['name'] }}</h1>
                <p>Reporte de Ventas &amp; Cobranzas — {{ $period }}</p>
            </td>
            <td class="header-meta">
                <strong>Período:</strong> {{ $period }}<br>
                <strong>Generado:</strong> {{ $generatedAt }}<br>
                <strong>Por:</strong> {{ $generatedBy }}
            </td>
        </tr>
    </table>
</div>

{{-- ══ SUMMARY CARDS ══ --}}
<div class="section-title">Resumen del Período</div>
<table class="summary-table">
    <tr>
        <td class="summary-cell">
            <div class="summary-card">
                <div class="s-label">Total Ventas</div>
                <div class="s-value" style="color:#1e40af;">{{ number_format($totals['ventas'],2,',',' ') }}</div>
                <div class="s-sub">{{ $period }}</div>
            </div>
        </td>
        <td class="summary-cell">
            <div class="summary-card">
                <div class="s-label">Total Cobros</div>
                <div class="s-value" style="color:#0f766e;">{{ number_format($totals['cobros'],2,',',' ') }}</div>
                <div class="s-sub">pagos recibidos</div>
            </div>
        </td>
        <td class="summary-cell">
            <div class="summary-card">
                <div class="s-label">Cantidad de Ventas</div>
                <div class="s-value" style="color:#7c3aed;">{{ number_format($totals['cantidad'],0,',','.') }}</div>
                <div class="s-sub">transacciones</div>
            </div>
        </td>
    </tr>
</table>

{{-- ══ MONTHLY TABLE ══ --}}
<div class="section-title">Detalle Mensual</div>
@php $maxVentas = collect($monthly)->max('ventas') ?: 1; @endphp
<table class="data-table">
    <thead>
        <tr>
            <th>Mes</th>
            <th class="right">Ventas (S/)</th>
            <th style="width:80px;">Progreso</th>
            <th class="center">Cantidad</th>
            <th class="right">Cobros (S/)</th>
            <th style="width:80px;">Cobros</th>
            <th class="right">Promedio x Venta</th>
        </tr>
    </thead>
    <tbody>
        @foreach($monthly as $m)
        <tr>
            <td><strong>{{ $m['label'] }}</strong></td>
            <td class="right">{{ number_format($m['ventas'],2,',',' ') }}</td>
            <td class="bar-cell">
                <div class="bar-bg">
                    <div class="bar-fill" style="width:{{ min(100, $maxVentas > 0 ? round($m['ventas']/$maxVentas*100) : 0) }}%;"></div>
                </div>
            </td>
            <td class="center">{{ $m['cantidad'] }}</td>
            <td class="right">{{ number_format($m['cobros'],2,',',' ') }}</td>
            <td class="bar-cell">
                <div class="bar-bg">
                    <div class="bar-fill cobros" style="width:{{ min(100, $maxVentas > 0 ? round($m['cobros']/$maxVentas*100) : 0) }}%;"></div>
                </div>
            </td>
            <td class="right" style="color:#64748b;">
                {{ $m['cantidad'] > 0 ? number_format($m['ventas']/$m['cantidad'],2,',',' ') : '—' }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="right"><strong>{{ number_format($totals['ventas'],2,',',' ') }}</strong></td>
            <td></td>
            <td class="center"><strong>{{ $totals['cantidad'] }}</strong></td>
            <td class="right"><strong>{{ number_format($totals['cobros'],2,',',' ') }}</strong></td>
            <td></td>
            <td class="right">
                <strong>{{ $totals['cantidad'] > 0 ? number_format($totals['ventas']/$totals['cantidad'],2,',',' ') : '—' }}</strong>
            </td>
        </tr>
    </tfoot>
</table>

{{-- ══ TOP 10 CLIENTS + TOP 10 PRODUCTS (two-column) ══ --}}
<div class="page-break"></div>

<div class="header" style="padding:12px 28px;">
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h1 style="font-size:15px;">{{ $system['name'] }} · Rankings del Período</h1>
                <p>{{ $period }} · Continuación del reporte de ventas</p>
            </td>
            <td class="header-meta">
                <strong>Generado:</strong> {{ $generatedAt }}<br>
                <strong>Por:</strong> {{ $generatedBy }}
            </td>
        </tr>
    </table>
</div>

<table class="two-col" style="margin-top:10px;">
<tr>
<td class="col-l">

    <div class="section-title purple" style="margin-top:0;">Top 10 Clientes por Ventas</div>
    @php $maxClient = collect($topClients)->max('total') ?: 1; @endphp
    <table class="top-table">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Cliente</th>
                <th class="right">Total (S/)</th>
                <th class="right">Pedidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topClients as $i => $c)
            <tr>
                <td class="rank {{ $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : '')) }}">
                    {{ $i + 1 }}
                </td>
                <td>
                    {{ $c['name'] }}
                    @if($i < 3)
                        @php $stars = ['★★★','★★','★']; @endphp
                        <span style="color:#f59e0b;font-size:9px;">{{ $stars[$i] }}</span>
                    @endif
                </td>
                <td class="right"><strong>{{ number_format($c['total'],2,',',' ') }}</strong></td>
                <td class="right" style="color:#64748b;">{{ $c['count'] }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3" style="padding:2px 10px 6px;">
                    <div class="bar-bg">
                        <div class="bar-fill" style="background:#7c3aed;width:{{ min(100, round($c['total']/$maxClient*100)) }}%;height:6px;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</td>
<td class="col-r">

    <div class="section-title green" style="margin-top:0;">Top 10 Productos más Vendidos</div>
    @php $maxQty = collect($topProducts)->max('qty') ?: 1; @endphp
    <table class="top-table">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Producto</th>
                <th class="right">Cant.</th>
                <th class="right">Total (S/)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $i => $p)
            <tr>
                <td class="rank {{ $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : '')) }}">
                    {{ $i + 1 }}
                </td>
                <td>
                    <div style="font-weight:600;">{{ $p->name }}</div>
                    <div style="font-size:9px;color:#94a3b8;">{{ $p->code ?? '' }}</div>
                </td>
                <td class="right"><strong>{{ number_format($p->qty,0,',','.') }}</strong></td>
                <td class="right">{{ number_format($p->total,2,',',' ') }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3" style="padding:2px 10px 6px;">
                    <div class="bar-bg">
                        <div class="bar-fill cobros" style="width:{{ min(100, round($p->qty/$maxQty*100)) }}%;height:6px;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</td>
</tr>
</table>

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="text-align:left;">{{ $system['name'] }} · Reporte de Ventas · {{ $period }}</td>
            <td style="text-align:right;">Generado por {{ $generatedBy }} el {{ $generatedAt }}</td>
        </tr>
    </table>
</div>

</body>
</html>
