<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cartera de Cobranzas</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

    /* ── Header ── */
    .header { background: linear-gradient(135deg, #0f766e 0%, #1e40af 100%); color: #fff; padding: 20px 28px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-logo { width: 70px; text-align: left; vertical-align: middle; }
    .header-logo img { max-height: 54px; max-width: 64px; }
    .header-title { text-align: left; vertical-align: middle; padding-left: 14px; }
    .header-title h1 { font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
    .header-title p  { font-size: 12px; margin-top: 3px; opacity: 0.88; }
    .header-meta { text-align: right; vertical-align: middle; font-size: 10px; opacity: 0.9; line-height: 1.6; }

    /* ── Aging summary ── */
    .aging-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .aging-cell { width: 25%; padding: 4px 6px; vertical-align: top; }
    .aging-card { border-radius: 6px; padding: 12px 14px; text-align: center; }
    .aging-card .a-label { font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; font-weight: 600; }
    .aging-card .a-value { font-size: 18px; font-weight: 800; }
    .aging-card .a-pct { font-size: 10px; margin-top: 4px; opacity: 0.85; }

    .card-green  { background: #dcfce7; border: 1px solid #86efac; color: #14532d; }
    .card-yellow { background: #fef9c3; border: 1px solid #fde047; color: #713f12; }
    .card-orange { background: #ffedd5; border: 1px solid #fb923c; color: #7c2d12; }
    .card-red    { background: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d; }
    .card-dark   { background: #1e293b; border: 1px solid #475569; color: #f8fafc; }

    /* ── Section titles ── */
    .section-title {
        background: #0f766e; color: #fff; font-size: 11px; font-weight: 700;
        padding: 6px 12px; margin-top: 18px; margin-bottom: 8px;
        text-transform: uppercase; letter-spacing: 0.6px;
    }
    .section-title.blue { background: #1e40af; }
    .section-title.red  { background: #dc2626; }

    /* ── Data table ── */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .data-table thead tr { background: #0f766e; color: #fff; }
    .data-table thead th { padding: 7px 10px; text-align: left; font-weight: 600; font-size: 9.5px; }
    .data-table thead th.right { text-align: right; }
    .data-table thead th.center { text-align: center; }
    .data-table tbody tr.overdue-row { background: #fff1f2; }
    .data-table tbody tr:not(.overdue-row):nth-child(even) { background: #f0fdf4; }
    .data-table tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
    .data-table tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
    .data-table tbody td.center { text-align: center; }
    .data-table tfoot tr { background: #0f172a; color: #fff; font-weight: 700; }
    .data-table tfoot td { padding: 8px 10px; }
    .data-table tfoot td.right { text-align: right; }

    /* ── Status badges ── */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 9px; font-weight: 600; }
    .badge-pending  { background: #dbeafe; color: #1e40af; }
    .badge-partial  { background: #fef9c3; color: #92400e; }
    .badge-overdue  { background: #fee2e2; color: #991b1b; }

    /* ── Days indicator ── */
    .days-ok  { color: #16a34a; font-weight: 700; }
    .days-w1  { color: #b45309; font-weight: 700; }
    .days-w2  { color: #ea580c; font-weight: 700; }
    .days-bad { color: #dc2626; font-weight: 700; }

    /* ── Mini progress bar for aging ── */
    .aging-bar-bg   { background: #e2e8f0; height: 6px; border-radius: 3px; margin-top: 4px; }
    .aging-bar-fill { height: 6px; border-radius: 3px; }

    /* ── Alert ── */
    .alert-box { border-left: 4px solid #dc2626; background: #fef2f2; padding: 10px 14px; margin-top: 10px; font-size: 10.5px; color: #7f1d1d; }

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
                <p>Cartera de Cobranzas · Análisis de Antigüedad de Saldos</p>
            </td>
            <td class="header-meta">
                <strong>Fecha de corte:</strong> {{ $generatedAt }}<br>
                <strong>Generado por:</strong> {{ $generatedBy }}
            </td>
        </tr>
    </table>
</div>

{{-- ══ AGING SUMMARY ══ --}}
<div class="section-title blue" style="margin-top:14px;">Resumen de Antigüedad de Saldos</div>
@php $total = $aging['total'] ?: 1; @endphp
<table class="aging-table">
    <tr>
        <td class="aging-cell">
            <div class="aging-card card-green">
                <div class="a-label">Al Día</div>
                <div class="a-value">{{ number_format($aging['al_dia'],2,',',' ') }}</div>
                <div class="a-pct">{{ $total > 0 ? round($aging['al_dia']/$total*100,1) : 0 }}% del total</div>
                <div class="aging-bar-bg"><div class="aging-bar-fill" style="background:#16a34a;width:{{ $total > 0 ? min(100,round($aging['al_dia']/$total*100)) : 0 }}%;"></div></div>
            </div>
        </td>
        <td class="aging-cell">
            <div class="aging-card card-yellow">
                <div class="a-label">Vencido 1-30 días</div>
                <div class="a-value">{{ number_format($aging['venc_30'],2,',',' ') }}</div>
                <div class="a-pct">{{ $total > 0 ? round($aging['venc_30']/$total*100,1) : 0 }}% del total</div>
                <div class="aging-bar-bg"><div class="aging-bar-fill" style="background:#ca8a04;width:{{ $total > 0 ? min(100,round($aging['venc_30']/$total*100)) : 0 }}%;"></div></div>
            </div>
        </td>
        <td class="aging-cell">
            <div class="aging-card card-orange">
                <div class="a-label">Vencido 31-60 días</div>
                <div class="a-value">{{ number_format($aging['venc_60'],2,',',' ') }}</div>
                <div class="a-pct">{{ $total > 0 ? round($aging['venc_60']/$total*100,1) : 0 }}% del total</div>
                <div class="aging-bar-bg"><div class="aging-bar-fill" style="background:#ea580c;width:{{ $total > 0 ? min(100,round($aging['venc_60']/$total*100)) : 0 }}%;"></div></div>
            </div>
        </td>
        <td class="aging-cell">
            <div class="aging-card card-red">
                <div class="a-label">Vencido +60 días</div>
                <div class="a-value">{{ number_format($aging['venc_mas60'],2,',',' ') }}</div>
                <div class="a-pct">{{ $total > 0 ? round($aging['venc_mas60']/$total*100,1) : 0 }}% del total</div>
                <div class="aging-bar-bg"><div class="aging-bar-fill" style="background:#dc2626;width:{{ $total > 0 ? min(100,round($aging['venc_mas60']/$total*100)) : 0 }}%;"></div></div>
            </div>
        </td>
    </tr>
</table>

{{-- Total row --}}
<table style="width:100%;border-collapse:collapse;margin-top:2px;">
    <tr>
        <td style="padding:10px 14px;background:#0f172a;color:#fff;border-radius:4px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="font-size:12px;font-weight:600;">TOTAL CARTERA ACTIVA</td>
                    <td style="text-align:right;font-size:18px;font-weight:800;">{{ number_format($aging['total'],2,',',' ') }}</td>
                    <td style="text-align:right;padding-left:20px;font-size:10px;opacity:0.7;">
                        Vencido total: {{ number_format($aging['venc_30']+$aging['venc_60']+$aging['venc_mas60'],2,',',' ') }}
                        ({{ $total > 0 ? round(($aging['venc_30']+$aging['venc_60']+$aging['venc_mas60'])/$total*100,1) : 0 }}%)
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if($aging['venc_mas60'] > 0)
<div class="alert-box">
    ⚠ Existen <strong>{{ number_format($aging['venc_mas60'],2,',',' ') }}</strong> en cartera vencida superior a 60 días.
    Se recomienda acción de cobranza inmediata.
</div>
@endif

{{-- ══ DETAIL TABLE ══ --}}
<div class="section-title" style="margin-top:16px;">Detalle de Cuentas por Cobrar</div>

@if(!empty($detail))
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th class="right">Vencimiento</th>
            <th class="right">Monto Total</th>
            <th class="right">Saldo Pendiente</th>
            <th class="center">Estado</th>
            <th class="center">Días</th>
        </tr>
    </thead>
    <tbody>
        @php
            $overduePart = collect($detail)->where('overdue', true);
            $currentPart = collect($detail)->where('overdue', false);
        @endphp

        {{-- Vencidas primero --}}
        @if($overduePart->count() > 0)
        <tr>
            <td colspan="7" style="background:#dc2626;color:#fff;font-size:9.5px;font-weight:700;padding:5px 10px;text-transform:uppercase;letter-spacing:0.4px;">
                ⚠ Cuentas Vencidas ({{ $overduePart->count() }})
            </td>
        </tr>
        @foreach($overduePart as $i => $row)
        <tr class="overdue-row">
            <td class="center" style="color:#dc2626;font-weight:700;font-size:9px;">{{ $i+1 }}</td>
            <td><strong>{{ $row['client'] }}</strong></td>
            <td class="right">{{ $row['due_date'] ?? '—' }}</td>
            <td class="right">{{ number_format($row['amount'],2,',',' ') }}</td>
            <td class="right"><strong style="color:#dc2626;">{{ number_format($row['balance'],2,',',' ') }}</strong></td>
            <td class="center">
                <span class="badge badge-overdue">Vencido</span>
            </td>
            <td class="center">
                @php $d = abs($row['days']); @endphp
                <span class="{{ $d > 60 ? 'days-bad' : ($d > 30 ? 'days-w2' : 'days-w1') }}">
                    {{ $d }} días
                </span>
            </td>
        </tr>
        @endforeach
        @endif

        {{-- Al día --}}
        @if($currentPart->count() > 0)
        <tr>
            <td colspan="7" style="background:#0f766e;color:#fff;font-size:9.5px;font-weight:700;padding:5px 10px;text-transform:uppercase;letter-spacing:0.4px;">
                ✓ Cuentas al Día / Pendientes ({{ $currentPart->count() }})
            </td>
        </tr>
        @foreach($currentPart as $i => $row)
        <tr>
            <td class="center" style="color:#94a3b8;font-size:9px;">{{ $i+1 }}</td>
            <td>{{ $row['client'] }}</td>
            <td class="right">{{ $row['due_date'] ?? '—' }}</td>
            <td class="right">{{ number_format($row['amount'],2,',',' ') }}</td>
            <td class="right"><strong style="color:#0f766e;">{{ number_format($row['balance'],2,',',' ') }}</strong></td>
            <td class="center">
                @if($row['status'] === 'partial')
                    <span class="badge badge-partial">Parcial</span>
                @else
                    <span class="badge badge-pending">Pendiente</span>
                @endif
            </td>
            <td class="center">
                <span class="days-ok">+{{ abs($row['days']) }} días</span>
            </td>
        </tr>
        @endforeach
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>TOTALES</strong></td>
            <td class="right"><strong>{{ number_format(collect($detail)->sum('amount'),2,',',' ') }}</strong></td>
            <td class="right"><strong>{{ number_format(collect($detail)->sum('balance'),2,',',' ') }}</strong></td>
            <td colspan="2" class="center" style="font-size:9.5px;opacity:0.8;">
                {{ count($detail) }} cuentas activas
            </td>
        </tr>
    </tfoot>
</table>
@else
<div style="text-align:center;padding:30px;color:#64748b;font-size:13px;">
    No hay cuentas por cobrar activas al momento del corte.
</div>
@endif

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="text-align:left;">{{ $system['name'] }} · Cartera de Cobranzas · Corte al {{ $generatedAt }}</td>
            <td style="text-align:right;">Generado por {{ $generatedBy }}</td>
        </tr>
    </table>
</div>

</body>
</html>
