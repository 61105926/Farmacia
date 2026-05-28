<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ruta de Cobros – {{ $periodLabel }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

    /* ── Header ── */
    .header { background: linear-gradient(135deg, #0f766e 0%, #065f46 100%); color: #fff; padding: 18px 24px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-logo  { width: 64px; text-align: left; vertical-align: middle; }
    .header-logo img { max-height: 50px; max-width: 58px; }
    .header-title { text-align: left; vertical-align: middle; padding-left: 12px; }
    .header-title h1 { font-size: 18px; font-weight: 800; letter-spacing: 0.4px; }
    .header-title p  { font-size: 11px; margin-top: 2px; opacity: 0.85; }
    .header-meta { text-align: right; vertical-align: middle; font-size: 9.5px; opacity: 0.9; line-height: 1.7; }

    /* ── Summary bar ── */
    .summary-bar { background: #f0fdf4; border-bottom: 2px solid #0f766e; padding: 10px 24px; }
    .summary-inner { width: 100%; border-collapse: collapse; }
    .summary-item { width: 25%; text-align: center; padding: 4px 8px; border-right: 1px solid #bbf7d0; }
    .summary-item:last-child { border-right: none; }
    .summary-item .s-val { font-size: 16px; font-weight: 800; color: #065f46; }
    .summary-item .s-lbl { font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-top: 1px; }

    /* ── Instrucciones ── */
    .instructions { background: #fffbeb; border: 1px solid #fde68a; padding: 7px 14px; margin: 10px 0; font-size: 9.5px; color: #78350f; border-radius: 4px; }

    /* ── Client card ── */
    .client-card { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 14px; overflow: hidden; page-break-inside: avoid; }
    .client-card.urgent { border-color: #fca5a5; }

    .client-header { padding: 10px 14px; }
    .client-header.overdue { background: #fff1f2; }
    .client-header.ok      { background: #f0fdf4; }

    .client-header-table { width: 100%; border-collapse: collapse; }
    .client-name-cell { vertical-align: top; }
    .client-number { display: inline-block; width: 22px; height: 22px; border-radius: 50%; background: #0f766e; color: #fff; text-align: center; line-height: 22px; font-size: 10px; font-weight: 800; margin-right: 6px; }
    .client-number.red { background: #dc2626; }
    .client-name { font-size: 13px; font-weight: 800; color: #1e293b; }
    .client-trade { font-size: 10px; color: #64748b; margin-top: 1px; }
    .client-contact { font-size: 9.5px; color: #475569; margin-top: 3px; }
    .client-total-cell { text-align: right; vertical-align: top; }
    .client-total-lbl { font-size: 9px; text-transform: uppercase; color: #64748b; }
    .client-total-val { font-size: 18px; font-weight: 800; color: #0f766e; }
    .client-total-val.red { color: #dc2626; }

    /* ── Facturas table inside card ── */
    .facturas-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .facturas-table thead tr { background: #1e293b; color: #fff; }
    .facturas-table thead th { padding: 5px 10px; text-align: left; font-size: 9.5px; font-weight: 600; }
    .facturas-table thead th.right { text-align: right; }
    .facturas-table thead th.center { text-align: center; }
    .facturas-table tbody tr:nth-child(even) { background: #f8fafc; }
    .facturas-table tbody td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
    .facturas-table tbody td.right { text-align: right; font-variant-numeric: tabular-nums; }
    .facturas-table tbody td.center { text-align: center; }
    .facturas-table tfoot tr { background: #f1f5f9; font-weight: 700; }
    .facturas-table tfoot td { padding: 6px 10px; }
    .facturas-table tfoot td.right { text-align: right; }

    /* ── Status badges ── */
    .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 8.5px; font-weight: 700; }
    .badge-overdue  { background: #fee2e2; color: #991b1b; }
    .badge-partial  { background: #fef9c3; color: #92400e; }
    .badge-pending  { background: #dcfce7; color: #166534; }

    /* ── Days chip ── */
    .days-ok  { color: #16a34a; font-weight: 700; }
    .days-w1  { color: #b45309; font-weight: 700; }
    .days-bad { color: #dc2626; font-weight: 700; }

    /* ── Firma / cobro box ── */
    .cobro-box { border: 1px solid #e2e8f0; margin: 0; padding: 8px 14px; background: #fafafa; }
    .cobro-box-table { width: 100%; border-collapse: collapse; }
    .cobro-line { border-bottom: 1px solid #cbd5e1; height: 20px; margin-top: 4px; }
    .cobro-label { font-size: 9px; color: #94a3b8; padding-top: 3px; }

    /* ── No data ── */
    .no-data { text-align: center; padding: 40px 20px; color: #94a3b8; font-size: 13px; }
    .no-data-icon { font-size: 32px; margin-bottom: 8px; }

    /* ── Footer ── */
    .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #94a3b8; }
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
                    <div style="width:50px;height:50px;background:rgba(255,255,255,0.2);border-radius:8px;text-align:center;line-height:50px;font-size:18px;font-weight:800;">
                        {{ strtoupper(substr($system['name'],0,1)) }}
                    </div>
                @endif
            </td>
            <td class="header-title">
                <h1>{{ $system['name'] }}</h1>
                <p>Ruta de Cobros · {{ $periodLabel }}</p>
            </td>
            <td class="header-meta">
                <strong>Fecha:</strong> {{ $periodLabel }}<br>
                <strong>Generado:</strong> {{ $generatedAt }}<br>
                <strong>Cobrador:</strong> {{ $generatedBy }}
            </td>
        </tr>
    </table>
</div>

{{-- ══ SUMMARY BAR ══ --}}
<div class="summary-bar">
    <table class="summary-inner">
        <tr>
            <td class="summary-item">
                <div class="s-val">{{ $totalClients }}</div>
                <div class="s-lbl">Clientes a visitar</div>
            </td>
            <td class="summary-item">
                <div class="s-val">{{ $totalFacturas }}</div>
                <div class="s-lbl">Facturas pendientes</div>
            </td>
            <td class="summary-item">
                <div class="s-val" style="color:#dc2626;">{{ number_format($totalBalance,2,',','.') }}</div>
                <div class="s-lbl">Total a cobrar (Bs)</div>
            </td>
            <td class="summary-item">
                <div class="s-val" style="color:#0f766e;">{{ $periodLabel }}</div>
                <div class="s-lbl">Período de cobro</div>
            </td>
        </tr>
    </table>
</div>

{{-- ══ INSTRUCCIONES ══ --}}
<div class="instructions" style="margin: 8px 0 10px 0;">
    <strong>Instrucciones:</strong>
    Visitar los clientes en el orden indicado. Registrar el monto cobrado, forma de pago y firma del cliente en cada casilla.
    Priorizar clientes con facturas <strong>VENCIDAS</strong> (marcadas en rojo).
</div>

@if($byClient->isEmpty())
<div class="no-data">
    <div class="no-data-icon">✓</div>
    <p>No hay cuentas por cobrar en el período seleccionado.</p>
</div>
@else

@foreach($byClient as $idx => $c)
{{-- ── CLIENT CARD ── --}}
<div class="client-card {{ $c['has_overdue'] ? 'urgent' : '' }}">

    {{-- Header del cliente --}}
    <div class="client-header {{ $c['has_overdue'] ? 'overdue' : 'ok' }}">
        <table class="client-header-table">
            <tr>
                <td class="client-name-cell">
                    <span class="client-number {{ $c['has_overdue'] ? 'red' : '' }}">{{ $idx + 1 }}</span>
                    <span class="client-name">{{ $c['name'] }}</span>
                    @if($c['trade'] && $c['trade'] !== $c['name'])
                        <div class="client-trade" style="margin-left:28px;">{{ $c['trade'] }}</div>
                    @endif
                    <div class="client-contact" style="margin-left:28px;">
                        @if($c['phone']) 📞 {{ $c['phone'] }}  @endif
                        @if($c['address']) · 📍 {{ $c['address'] }}{{ $c['city'] ? ', ' . $c['city'] : '' }} @endif
                        @if($c['tax_id']) · NIT: {{ $c['tax_id'] }} @endif
                    </div>
                </td>
                <td class="client-total-cell" style="width:140px;">
                    <div class="client-total-lbl">Total a cobrar</div>
                    <div class="client-total-val {{ $c['has_overdue'] ? 'red' : '' }}">
                        Bs {{ number_format($c['total_balance'],2,',','.') }}
                    </div>
                    @if($c['has_overdue'])
                        <div style="margin-top:3px;">
                            <span class="badge badge-overdue">⚠ VENCIDO</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Facturas del cliente --}}
    <table class="facturas-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Vencimiento</th>
                <th class="right">Monto original</th>
                <th class="right">Saldo pendiente</th>
                <th class="center">Estado</th>
                <th class="center">Días</th>
                @if($c['facturas'][0]['notes'] ?? false)<th>Notas</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($c['facturas'] as $fi => $f)
            <tr>
                <td class="center" style="color:#94a3b8;">{{ $fi + 1 }}</td>
                <td><strong>{{ $f['due_date'] ?? '—' }}</strong></td>
                <td class="right">{{ number_format($f['amount'],2,',','.') }}</td>
                <td class="right"><strong style="color:{{ $f['overdue'] ? '#dc2626' : '#0f766e' }};">{{ number_format($f['balance'],2,',','.') }}</strong></td>
                <td class="center">
                    @if($f['overdue'])
                        <span class="badge badge-overdue">Vencida</span>
                    @elseif($f['status'] === 'partial')
                        <span class="badge badge-partial">Parcial</span>
                    @else
                        <span class="badge badge-pending">Vigente</span>
                    @endif
                </td>
                <td class="center">
                    @if($f['overdue'])
                        <span class="days-bad">{{ $f['days'] }}d vencida</span>
                    @elseif($f['days'] <= 7)
                        <span class="days-w1">vence en {{ $f['days'] }}d</span>
                    @else
                        <span class="days-ok">{{ $f['days'] }}d restantes</span>
                    @endif
                </td>
                @if($f['notes'] ?? false)<td style="font-size:9px;color:#64748b;">{{ $f['notes'] }}</td>@endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="padding-left:10px;font-size:10px;">Total del cliente</td>
                <td class="right"><strong>Bs {{ number_format($c['total_balance'],2,',','.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Casilla de cobro / firma --}}
    <div class="cobro-box">
        <table class="cobro-box-table">
            <tr>
                <td style="width:30%;padding-right:12px;">
                    <div class="cobro-label">Monto cobrado (Bs)</div>
                    <div class="cobro-line"></div>
                </td>
                <td style="width:25%;padding-right:12px;">
                    <div class="cobro-label">Forma de pago</div>
                    <div class="cobro-line"></div>
                </td>
                <td style="width:25%;padding-right:12px;">
                    <div class="cobro-label">N° comprobante / recibo</div>
                    <div class="cobro-line"></div>
                </td>
                <td style="width:20%;">
                    <div class="cobro-label">Firma del cliente</div>
                    <div class="cobro-line"></div>
                </td>
            </tr>
        </table>
    </div>

</div>
@endforeach

{{-- ══ RESUMEN DE COBROS ══ --}}
<div style="margin-top:16px;border:2px solid #0f766e;border-radius:6px;overflow:hidden;">
    <div style="background:#0f766e;color:#fff;padding:7px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
        Resumen del Cobrador
    </div>
    <div style="padding:10px 14px;">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:50%;padding-right:12px;">
                    <div class="cobro-label">Total programado a cobrar</div>
                    <div style="font-size:18px;font-weight:800;color:#0f766e;">Bs {{ number_format($totalBalance,2,',','.') }}</div>
                </td>
                <td style="width:50%;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="width:50%;padding-right:8px;">
                                <div class="cobro-label">Total cobrado efectivo</div>
                                <div class="cobro-line" style="width:100%;"></div>
                            </td>
                            <td style="width:50%;">
                                <div class="cobro-label">Total cobrado transferencia</div>
                                <div class="cobro-line" style="width:100%;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top:10px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="width:33%;padding-right:8px;">
                                <div class="cobro-label">Total cobrado (Bs)</div>
                                <div class="cobro-line"></div>
                            </td>
                            <td style="width:33%;padding-right:8px;">
                                <div class="cobro-label">Saldo pendiente (no cobrado)</div>
                                <div class="cobro-line"></div>
                            </td>
                            <td style="width:34%;">
                                <div class="cobro-label">Firma del cobrador</div>
                                <div class="cobro-line"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

@endif

{{-- ══ FOOTER ══ --}}
<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="text-align:left;">{{ $system['name'] }} · Ruta de Cobros · {{ $periodLabel }}</td>
            <td style="text-align:center;">Cobrador: {{ $generatedBy }}</td>
            <td style="text-align:right;">Generado el {{ $generatedAt }}</td>
        </tr>
    </table>
</div>

</body>
</html>
