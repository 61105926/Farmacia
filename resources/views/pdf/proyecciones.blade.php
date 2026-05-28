<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Proyecciones – {{ $period }}</title>
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
    .section-title.green { background: #0f766e; }

    /* ── KPI cards ── */
    .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .kpi-cell { width: 25%; padding: 4px 6px; vertical-align: top; }
    .kpi-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; background: #f8fafc; }
    .kpi-card .label { font-size: 9.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
    .kpi-card .value { font-size: 20px; font-weight: 700; color: #1e293b; }
    .kpi-card .growth { font-size: 10px; margin-top: 4px; }
    .up   { color: #16a34a; }
    .down { color: #dc2626; }
    .neutral { color: #94a3b8; }

    /* ── Generic table ── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
    .data-table thead tr { background: #1e40af; color: #fff; }
    .data-table thead th { padding: 7px 10px; text-align: left; font-weight: 600; font-size: 10px; }
    .data-table thead th.right { text-align: right; }
    .data-table tbody tr:nth-child(even) { background: #f1f5f9; }
    .data-table tbody tr:hover { background: #e0f2fe; }
    .data-table tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
    .data-table tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
    .data-table tbody td.center { text-align: center; }
    .data-table tfoot tr { background: #0f172a; color: #fff; font-weight: 700; }
    .data-table tfoot td { padding: 8px 10px; }
    .data-table tfoot td.right { text-align: right; }

    /* ── Projection bar ── */
    .proj-bar-bg { background: #e2e8f0; height: 10px; border-radius: 5px; width: 100%; }
    .proj-bar-fill { background: #1e40af; height: 10px; border-radius: 5px; }
    .proj-bar-fill.green { background: #0f766e; }

    /* ── Alert box ── */
    .alert-box { border-left: 4px solid #dc2626; background: #fef2f2; padding: 10px 14px; margin-top: 10px; font-size: 10.5px; color: #7f1d1d; }
    .alert-box.green { border-color: #16a34a; background: #f0fdf4; color: #14532d; }

    /* ── Footer ── */
    .footer { margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #94a3b8; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .footer-left { text-align: left; }
    .footer-right { text-align: right; }

    /* ── Badge ── */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 9px; font-weight: 600; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef9c3; color: #92400e; }
    .badge-green { background: #dcfce7; color: #166534; }
    .badge-blue { background: #dbeafe; color: #1e40af; }

    /* ── Spacing ── */
    .mt-4 { margin-top: 4px; }
    .mt-8 { margin-top: 8px; }
    .mb-0 { margin-bottom: 0; }
    .two-col-table { width: 100%; border-collapse: collapse; }
    .two-col-left { width: 50%; padding-right: 10px; vertical-align: top; }
    .two-col-right { width: 50%; padding-left: 10px; vertical-align: top; }

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
                <p>Reporte de Proyecciones &amp; Análisis Predictivo</p>
            </td>
            <td class="header-meta">
                <strong>Período:</strong> {{ $period }}<br>
                <strong>Generado:</strong> {{ $generatedAt }}<br>
                <strong>Por:</strong> {{ $generatedBy }}
            </td>
        </tr>
    </table>
</div>

{{-- ══ KPIs ══ --}}
<div class="section-title">Indicadores Clave del Mes</div>
<table class="kpi-table">
    <tr>
        <td class="kpi-cell">
            <div class="kpi-card">
                <div class="label">Ventas del Mes</div>
                <div class="value">{{ number_format($kpis['ventas_mes'],2,',',' ') }}</div>
                <div class="growth {{ $kpis['ventas_growth'] >= 0 ? 'up' : 'down' }}">
                    {{ $kpis['ventas_growth'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['ventas_growth']) }}% vs mes anterior
                </div>
            </div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-card">
                <div class="label">Cobros del Mes</div>
                <div class="value">{{ number_format($kpis['cobros_mes'],2,',',' ') }}</div>
                <div class="growth {{ $kpis['cobros_growth'] >= 0 ? 'up' : 'down' }}">
                    {{ $kpis['cobros_growth'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['cobros_growth']) }}% vs mes anterior
                </div>
            </div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-card">
                <div class="label">Por Cobrar (total)</div>
                <div class="value" style="color:#0f766e;">{{ number_format($kpis['por_cobrar'],2,',',' ') }}</div>
                <div class="growth neutral">saldo pendiente activo</div>
            </div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-card">
                <div class="label">Cartera Vencida</div>
                <div class="value" style="color:#dc2626;">{{ number_format($kpis['vencido'],2,',',' ') }}</div>
                <div class="growth down">requiere gestión urgente</div>
            </div>
        </td>
    </tr>
</table>

{{-- Mes anterior row --}}
<table class="kpi-table" style="margin-top:2px;">
    <tr>
        <td class="kpi-cell">
            <div style="font-size:9.5px;color:#64748b;padding:4px 6px;">
                Mes anterior: <strong>{{ number_format($kpis['ventas_ant'],2,',',' ') }}</strong>
            </div>
        </td>
        <td class="kpi-cell">
            <div style="font-size:9.5px;color:#64748b;padding:4px 6px;">
                Mes anterior: <strong>{{ number_format($kpis['cobros_ant'],2,',',' ') }}</strong>
            </div>
        </td>
        <td class="kpi-cell"></td>
        <td class="kpi-cell"></td>
    </tr>
</table>

{{-- ══ TWO COLUMNS: Sales Projection + Cobros Projection ══ --}}
<table class="two-col-table" style="margin-top:6px;">
<tr>
<td class="two-col-left">

    <div class="section-title" style="margin-top:0;">Proyección de Ventas</div>
    @php $maxEst = collect($salesProjection)->max('estimated') ?: 1; @endphp
    @foreach($salesProjection as $i => $p)
    <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
        <tr>
            <td style="width:90px;font-size:10px;font-weight:600;color:#1e293b;padding-bottom:3px;">{{ $p['label'] }}</td>
            <td style="text-align:right;font-size:10px;font-weight:700;color:#1e40af;padding-bottom:3px;">{{ number_format($p['estimated'],2,',',' ') }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="proj-bar-bg">
                    <div class="proj-bar-fill {{ $i == 0 ? '' : ($i == 1 ? 'green' : '') }}"
                         style="width:{{ min(100, round($p['estimated']/$maxEst*100)) }}%;"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:9px;color:#64748b;padding-top:2px;">
                Tendencia: {{ $p['trend_pct'] >= 0 ? '+' : '' }}{{ $p['trend_pct'] }}% mensual
            </td>
        </tr>
    </table>
    @endforeach

    @if($kpis['ventas_growth'] < -10)
    <div class="alert-box" style="margin-top:6px;">
        ⚠ Las ventas cayeron {{ abs($kpis['ventas_growth']) }}% respecto al mes anterior. Revisar causas.
    </div>
    @elseif($kpis['ventas_growth'] >= 10)
    <div class="alert-box green" style="margin-top:6px;">
        ✓ Ventas con crecimiento de {{ $kpis['ventas_growth'] }}% respecto al mes anterior.
    </div>
    @endif

</td>
<td class="two-col-right">

    <div class="section-title" style="margin-top:0;">Proyección de Cobros Futuros</div>
    @php $maxCob = collect($cobrosProjection)->max('amount') ?: 1; @endphp
    @foreach($cobrosProjection as $w)
    <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
        <tr>
            <td style="font-size:10px;font-weight:600;color:#1e293b;padding-bottom:3px;">{{ $w['label'] }}</td>
            <td style="text-align:right;font-size:10px;font-weight:700;color:#0f766e;">{{ number_format($w['amount'],2,',',' ') }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="proj-bar-bg">
                    <div class="proj-bar-fill green"
                         style="width:{{ min(100, $maxCob > 0 ? round($w['amount']/$maxCob*100) : 0) }}%;"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:9px;color:#64748b;padding-top:2px;">
                {{ $w['count'] }} cuentas en este rango
            </td>
        </tr>
    </table>
    @endforeach

    @php $totalProj = collect($cobrosProjection)->sum('amount'); @endphp
    <table style="width:100%;border-collapse:collapse;background:#0f172a;color:#fff;border-radius:4px;">
        <tr>
            <td style="padding:8px 10px;font-size:10.5px;font-weight:600;">Total proyectado (90 días)</td>
            <td style="padding:8px 10px;text-align:right;font-size:13px;font-weight:700;">{{ number_format($totalProj,2,',',' ') }}</td>
        </tr>
    </table>

</td>
</tr>
</table>

{{-- ══ CHURNED CLIENTS ══ --}}
@if(!empty($churnedClients))
<div class="section-title" style="background:#b45309;">Clientes sin Actividad Reciente (últimos 60 días)</div>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th class="right">Última Compra</th>
            <th class="right">Monto Última Venta</th>
            <th class="right">Días Sin Comprar</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($churnedClients as $i => $c)
        <tr>
            <td class="center" style="color:#94a3b8;">{{ $i+1 }}</td>
            <td><strong>{{ $c['name'] }}</strong></td>
            <td class="right">{{ $c['last_date'] ?? '—' }}</td>
            <td class="right">{{ number_format($c['last_amount'],2,',',' ') }}</td>
            <td class="right">
                @if($c['days'] !== null)
                    <strong style="color:{{ $c['days'] > 90 ? '#dc2626' : '#b45309' }};">{{ $c['days'] }} días</strong>
                @else
                    —
                @endif
            </td>
            <td>
                @if(($c['days'] ?? 0) > 90)
                    <span class="badge badge-red">Crítico</span>
                @else
                    <span class="badge badge-yellow">En riesgo</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="alert-box green" style="margin-top:10px;">
    ✓ Sin clientes inactivos detectados en los últimos 60 días.
</div>
@endif

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <table class="footer-table">
        <tr>
            <td class="footer-left">{{ $system['name'] }} · Reporte de Proyecciones · {{ $period }}</td>
            <td class="footer-right">Generado por {{ $generatedBy }} el {{ $generatedAt }}</td>
        </tr>
    </table>
</div>

</body>
</html>
