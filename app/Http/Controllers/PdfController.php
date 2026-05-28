<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\Presale;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    // ─── Helpers compartidos ──────────────────────────────────────────────────

    private function systemInfo(): array
    {
        try {
            $s = SystemSetting::current();
            return [
                'name'     => $s->site_name ?? 'SISPANDO',
                'logo_url' => $s->logo_path ? \Storage::disk('public')->url($s->logo_path) : null,
                'logo_path'=> $s->logo_path ? storage_path('app/public/' . $s->logo_path) : null,
            ];
        } catch (\Exception $e) {
            return ['name' => 'SISPANDO', 'logo_url' => null, 'logo_path' => null];
        }
    }

    private function monthName(int $month): string
    {
        return ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][$month - 1];
    }

    private function growthPercent($prev, $curr): float
    {
        if ($prev == 0 && $curr == 0) return 0;
        if ($prev == 0) return 100;
        return round((($curr - $prev) / $prev) * 100, 1);
    }

    // ─── PDF: Proyecciones ────────────────────────────────────────────────────

    public function proyecciones()
    {
        $now      = Carbon::now();
        $curFrom  = $now->copy()->startOfMonth();
        $curTo    = $now->copy()->endOfMonth();
        $lastFrom = $now->copy()->subMonth()->startOfMonth();
        $lastTo   = $now->copy()->subMonth()->endOfMonth();

        // KPIs del mes
        $ventasMes  = Sale::whereBetween('created_at', [$curFrom, $curTo])->where('status','!=','cancelled')->sum('total') ?? 0;
        $ventasAnt  = Sale::whereBetween('created_at', [$lastFrom, $lastTo])->where('status','!=','cancelled')->sum('total') ?? 0;
        $cobrosMes  = Payment::whereBetween('payment_date', [$curFrom, $curTo])->where('status','completed')->sum('amount') ?? 0;
        $cobrosAnt  = Payment::whereBetween('payment_date', [$lastFrom, $lastTo])->where('status','completed')->sum('amount') ?? 0;
        $porCobrar  = Receivable::whereIn('status',['pending','partial'])->sum('balance') ?? 0;
        $vencido    = Receivable::whereIn('status',['pending','partial','overdue'])->where('due_date','<',$now)->sum('balance') ?? 0;

        // Proyección de ventas (promedio últimos 3 meses)
        $history = [];
        for ($i = 3; $i >= 1; $i--) {
            $d = Carbon::now()->subMonths($i);
            $history[] = Sale::whereYear('created_at',$d->year)->whereMonth('created_at',$d->month)
                ->where('status','!=','cancelled')->sum('total') ?? 0;
        }
        $avg   = count($history) > 0 ? array_sum($history) / count($history) : 0;
        $trend = (count($history) >= 2 && $history[0] > 0)
            ? max(-0.2, min(0.2, ($history[2] - $history[0]) / $history[0] / count($history)))
            : 0;

        $salesProjection = [];
        for ($i = 1; $i <= 3; $i++) {
            $d = Carbon::now()->addMonths($i);
            $salesProjection[] = [
                'label'     => $this->monthName($d->month) . ' ' . $d->year,
                'estimated' => max(0, round($avg * (1 + $trend * $i), 2)),
                'trend_pct' => round($trend * 100, 1),
            ];
        }

        // Proyección de cobros
        $cobrosProjection = [
            ['label' => 'Próximos 30 días',   'from' => Carbon::today(),         'to' => Carbon::today()->addDays(30)],
            ['label' => 'Próximos 31-60 días', 'from' => Carbon::today()->addDays(31), 'to' => Carbon::today()->addDays(60)],
            ['label' => 'Próximos 61-90 días', 'from' => Carbon::today()->addDays(61), 'to' => Carbon::today()->addDays(90)],
        ];
        foreach ($cobrosProjection as &$w) {
            $w['amount'] = round(Receivable::whereIn('status',['pending','partial'])->whereBetween('due_date',[$w['from'],$w['to']])->sum('balance') ?? 0, 2);
            $w['count']  = Receivable::whereIn('status',['pending','partial'])->whereBetween('due_date',[$w['from'],$w['to']])->count();
            unset($w['from'], $w['to']);
        }

        // Clientes sin actividad
        $cutoff = Carbon::now()->subDays(60);
        $churnedIds = DB::table('sales')->select('client_id')
            ->where('status','!=','cancelled')
            ->where('created_at','>=',Carbon::now()->subDays(180))
            ->groupBy('client_id')
            ->havingRaw('MAX(created_at) < ?', [$cutoff])
            ->pluck('client_id')->toArray();

        $churnedClients = [];
        if (!empty($churnedIds)) {
            $churnedClients = Client::whereIn('id', $churnedIds)->limit(15)->get()->map(function($c) {
                $last = Sale::where('client_id',$c->id)->where('status','!=','cancelled')->latest()->first();
                return [
                    'name'       => $c->business_name ?? 'Sin nombre',
                    'last_date'  => $last?->created_at?->format('d/m/Y'),
                    'last_amount'=> $last ? round($last->total, 2) : 0,
                    'days'       => $last ? (int)Carbon::parse($last->created_at)->diffInDays(now()) : null,
                ];
            })->toArray();
        }

        $data = [
            'system'           => $this->systemInfo(),
            'generatedAt'      => $now->format('d/m/Y H:i'),
            'generatedBy'      => Auth::user()->name ?? '—',
            'period'           => $this->monthName($now->month) . ' ' . $now->year,
            'kpis'             => [
                'ventas_mes'    => round($ventasMes, 2),
                'ventas_ant'    => round($ventasAnt, 2),
                'ventas_growth' => $this->growthPercent($ventasAnt, $ventasMes),
                'cobros_mes'    => round($cobrosMes, 2),
                'cobros_ant'    => round($cobrosAnt, 2),
                'cobros_growth' => $this->growthPercent($cobrosAnt, $cobrosMes),
                'por_cobrar'    => round($porCobrar, 2),
                'vencido'       => round($vencido, 2),
            ],
            'salesProjection'    => $salesProjection,
            'cobrosProjection'   => $cobrosProjection,
            'churnedClients'     => $churnedClients,
        ];

        $pdf = Pdf::loadView('pdf.proyecciones', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('proyecciones-' . $now->format('Y-m') . '.pdf');
    }

    // ─── PDF: Ventas ──────────────────────────────────────────────────────────

    public function ventas(Request $request)
    {
        $months = (int)($request->get('months', 12));
        $months = in_array($months, [3, 6, 12]) ? $months : 12;
        $now    = Carbon::now();

        // Datos mes a mes
        $monthlyData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d    = $now->copy()->subMonths($i);
            $from = $d->copy()->startOfMonth();
            $to   = $d->copy()->endOfMonth();

            $ventas = Sale::whereBetween('created_at', [$from, $to])->where('status','!=','cancelled');
            $cobros = Payment::whereBetween('payment_date', [$from, $to])->where('status','completed');

            $monthlyData[] = [
                'label'    => $this->monthName($d->month) . ' ' . $d->year,
                'ventas'   => round($ventas->sum('total') ?? 0, 2),
                'cantidad' => $ventas->count(),
                'cobros'   => round($cobros->sum('amount') ?? 0, 2),
            ];
        }

        // Totales
        $totalVentas = array_sum(array_column($monthlyData, 'ventas'));
        $totalCobros = array_sum(array_column($monthlyData, 'cobros'));
        $totalCant   = array_sum(array_column($monthlyData, 'cantidad'));

        // Top 10 clientes
        $topClients = Client::withCount('sales')
            ->withSum(['sales' => fn($q) => $q->where('status','!=','cancelled')], 'total')
            ->having('sales_count', '>', 0)
            ->orderByDesc('sales_sum_total')
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'name'   => $c->business_name ?? '—',
                'count'  => $c->sales_count,
                'total'  => round($c->sales_sum_total ?? 0, 2),
            ])->toArray();

        // Top 10 productos vendidos
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status','!=','cancelled')
            ->where('sales.created_at','>=', $now->copy()->subMonths($months))
            ->selectRaw('products.name, products.code, SUM(sale_items.quantity) as qty, SUM(sale_items.total) as total')
            ->groupBy('products.id','products.name','products.code')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()->toArray();

        $data = [
            'system'      => $this->systemInfo(),
            'generatedAt' => $now->format('d/m/Y H:i'),
            'generatedBy' => Auth::user()->name ?? '—',
            'period'      => "Últimos {$months} meses",
            'monthly'     => $monthlyData,
            'totals'      => ['ventas' => round($totalVentas,2), 'cobros' => round($totalCobros,2), 'cantidad' => $totalCant],
            'topClients'  => $topClients,
            'topProducts' => $topProducts,
        ];

        $pdf = Pdf::loadView('pdf.ventas', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('reporte-ventas-' . $now->format('Y-m') . '.pdf');
    }

    // ─── PDF: Ruta de cobros por fecha ───────────────────────────────────────

    public function rutaCobros(Request $request)
    {
        $from  = Carbon::parse($request->get('from', today()))->startOfDay();
        $to    = Carbon::parse($request->get('to',   today()))->endOfDay();
        $now   = Carbon::now();
        $today = Carbon::today();

        // Cuentas con vencimiento en el rango seleccionado
        $receivables = Receivable::with(['client:id,business_name,trade_name,phone,address,city,tax_id'])
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where(function ($q) use ($from, $to) {
                // Incluye vencidas dentro del rango Y también las ya vencidas si el rango empieza hoy o antes
                $q->whereBetween('due_date', [$from->toDateString(), $to->toDateString()]);
            })
            ->orderBy('due_date')
            ->get();

        // También incluir las ya vencidas si el from <= hoy
        $overdueExtra = collect();
        if ($from->lte($today)) {
            $overdueExtra = Receivable::with(['client:id,business_name,trade_name,phone,address,city,tax_id'])
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->where('due_date', '<', $today->toDateString())
                ->orderBy('due_date')
                ->get();
        }

        // Merge y deduplicar por id
        $all = $receivables->merge($overdueExtra)->unique('id')->sortBy('due_date');

        // Agrupar por cliente
        $byClient = $all->groupBy('client_id')->map(function ($rows) use ($today) {
            $client = $rows->first()->client;
            return [
                'name'      => $client?->business_name ?? '—',
                'trade'     => $client?->trade_name,
                'phone'     => $client?->phone,
                'address'   => $client?->address,
                'city'      => $client?->city,
                'tax_id'    => $client?->tax_id,
                'facturas'  => $rows->map(function ($r) use ($today) {
                    $days = $today->diffInDays($r->due_date, false);
                    return [
                        'id'       => $r->id,
                        'due_date' => $r->due_date?->format('d/m/Y'),
                        'amount'   => round($r->amount, 2),
                        'balance'  => round($r->balance, 2),
                        'status'   => $r->status,
                        'notes'    => $r->notes,
                        'overdue'  => $days < 0,
                        'days'     => (int) abs($days),
                    ];
                })->values()->toArray(),
                'total_balance' => round($rows->sum('balance'), 2),
                'has_overdue'   => $rows->filter(fn($r) => $today->gt($r->due_date))->count() > 0,
            ];
        })->values()->sortByDesc('has_overdue')->values();

        $isSameDay = $from->isSameDay($to);
        $periodLabel = $isSameDay
            ? $from->format('d/m/Y')
            : $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y');

        $data = [
            'system'       => $this->systemInfo(),
            'generatedAt'  => $now->format('d/m/Y H:i'),
            'generatedBy'  => Auth::user()->name ?? '—',
            'periodLabel'  => $periodLabel,
            'from'         => $from,
            'to'           => $to,
            'byClient'     => $byClient,
            'totalClients' => $byClient->count(),
            'totalBalance' => round($all->sum('balance'), 2),
            'totalFacturas'=> $all->count(),
        ];

        $pdf = Pdf::loadView('pdf.ruta-cobros', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('ruta-cobros-' . $from->format('Y-m-d') . '.pdf');
    }

    // ─── PDF: Cartera de cobranzas ────────────────────────────────────────────

    public function cartera()
    {
        $now   = Carbon::now();
        $today = Carbon::today();

        // Resumen por antigüedad
        $aging = [
            'al_dia'       => round(Receivable::whereIn('status',['pending','partial'])->where('due_date','>=',$today)->sum('balance') ?? 0, 2),
            'venc_30'      => round(Receivable::whereIn('status',['pending','partial','overdue'])->where('due_date','<',$today)->where('due_date','>=',$today->copy()->subDays(30))->sum('balance') ?? 0, 2),
            'venc_60'      => round(Receivable::whereIn('status',['pending','partial','overdue'])->where('due_date','<',$today->copy()->subDays(30))->where('due_date','>=',$today->copy()->subDays(60))->sum('balance') ?? 0, 2),
            'venc_mas60'   => round(Receivable::whereIn('status',['pending','partial','overdue'])->where('due_date','<',$today->copy()->subDays(60))->sum('balance') ?? 0, 2),
        ];
        $aging['total'] = $aging['al_dia'] + $aging['venc_30'] + $aging['venc_60'] + $aging['venc_mas60'];

        // Detalle por cliente
        $detail = Receivable::with('client:id,business_name')
            ->whereIn('status',['pending','partial','overdue'])
            ->orderByRaw("CASE WHEN due_date < ? THEN 0 ELSE 1 END", [$today])
            ->orderBy('due_date')
            ->get()
            ->map(function($r) use ($today) {
                $days = $today->diffInDays($r->due_date, false);
                return [
                    'client'   => $r->client?->business_name ?? '—',
                    'due_date' => $r->due_date?->format('d/m/Y'),
                    'amount'   => round($r->amount, 2),
                    'balance'  => round($r->balance, 2),
                    'status'   => $r->status,
                    'days'     => (int)$days,
                    'overdue'  => $days < 0,
                ];
            })->toArray();

        $data = [
            'system'      => $this->systemInfo(),
            'generatedAt' => $now->format('d/m/Y H:i'),
            'generatedBy' => Auth::user()->name ?? '—',
            'aging'       => $aging,
            'detail'      => $detail,
        ];

        $pdf = Pdf::loadView('pdf.cartera', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('cartera-cobranzas-' . $now->format('Y-m-d') . '.pdf');
    }
}
