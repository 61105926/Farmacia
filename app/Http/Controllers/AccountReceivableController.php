<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccountReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'creator', 'payments'])
            ->where('status', '!=', 'cancelled')
            ->where('total', '>', 0);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }

        if ($request->filled('unpaid')) {
            $query->unpaid();
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        // Calcular estadísticas
        $stats = [
            'totalReceivable' => Invoice::where('status', '!=', 'cancelled')
                ->where('payment_status', '!=', 'paid')
                ->sum('balance'),
            'overdueAmount' => Invoice::overdue()->sum('balance'),
            'unpaidInvoices' => Invoice::unpaid()->count(),
            'overdueInvoices' => Invoice::overdue()->count(),
            'totalPaid' => Payment::completed()->sum('amount'),
            'pendingPayments' => Payment::pending()->count(),
        ];

        return Inertia::render('AccountReceivables/Index', [
            'invoices' => $invoices,
            'clients' => Client::where('status', 'active')->get(['id', 'business_name', 'trade_name']),
            'paymentStatuses' => Invoice::getPaymentStatuses(),
            'filters' => $request->only([
                'search', 'payment_status', 'client_id', 'date_from', 'date_to', 'overdue', 'unpaid'
            ]),
            'stats' => $stats,
        ]);
    }

    public function show($id)
    {
        \Log::info('AccountReceivableController show - Iniciando', ['invoice_id' => $id]);
        
        try {
            // Cargar factura con relaciones de forma segura
            $invoice = Invoice::findOrFail($id);
            
            // Cargar relaciones de forma individual para evitar errores
            try {
                $invoice->load('client:id,business_name,trade_name,tax_id,address');
            } catch (\Exception $e) {
                \Log::warning('Error cargando cliente', ['error' => $e->getMessage()]);
            }
            
            try {
                $invoice->load('creator:id,name');
            } catch (\Exception $e) {
                \Log::warning('Error cargando creador', ['error' => $e->getMessage()]);
            }
            
            try {
                $invoice->load(['items' => function($query) {
                    $query->with('product:id,name,code');
                }]);
            } catch (\Exception $e) {
                \Log::warning('Error cargando items', ['error' => $e->getMessage()]);
                $invoice->setRelation('items', collect([]));
            }
            
            try {
                $invoice->load(['payments' => function($query) {
                    $query->with([
                        'creator:id,name',
                        'approver:id,name'
                    ])->latest('payment_date');
                }]);
            } catch (\Exception $e) {
                \Log::warning('Error cargando pagos', ['error' => $e->getMessage()]);
                $invoice->setRelation('payments', collect([]));
            }

            \Log::info('AccountReceivableController show - Factura cargada exitosamente', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            return Inertia::render('AccountReceivables/Show', [
                'invoice' => $invoice,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('AccountReceivableController show - Factura no encontrada', ['invoice_id' => $id]);
            return redirect()->route('account-receivables.index')
                ->with('error', 'La factura no existe.');
        } catch (\Exception $e) {
            \Log::error('AccountReceivableController show - Error', [
                'invoice_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect()->route('account-receivables.index')
                ->with('error', 'Error al cargar la factura. Por favor, intente nuevamente.');
        }
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['client', 'invoice', 'creator', 'approver']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('business_name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function ($q) use ($search) {
                      $q->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->get('date_to'));
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return Inertia::render('AccountReceivables/Payments', [
            'payments' => $payments,
            'clients' => Client::where('status', 'active')->get(['id', 'business_name', 'trade_name']),
            'statuses' => Payment::getStatuses(),
            'paymentMethods' => Payment::getPaymentMethods(),
            'filters' => $request->only([
                'search', 'status', 'payment_method', 'client_id', 'date_from', 'date_to'
            ]),
        ]);
    }

    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'check_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Asegurar que los campos opcionales sean null si están vacíos
        $validated['invoice_id'] = $validated['invoice_id'] ?? null;
        $validated['payment_reference'] = $validated['payment_reference'] ?? null;
        $validated['bank_name'] = $validated['bank_name'] ?? null;
        $validated['account_number'] = $validated['account_number'] ?? null;
        $validated['check_number'] = $validated['check_number'] ?? null;
        $validated['notes'] = $validated['notes'] ?? null;

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'payment_date' => $validated['payment_date'],
                'client_id' => $validated['client_id'],
                'invoice_id' => $validated['invoice_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'check_number' => $validated['check_number'],
                'notes' => $validated['notes'],
                'status' => 'completed', // Auto-aprobar pagos directos
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Actualizar el saldo de la factura si está asociada
            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
                $totalPaid = $invoice->payments()->completed()->sum('amount');
                $balance = $invoice->total - $totalPaid;
                
                $paymentStatus = 'unpaid';
                if ($balance <= 0) {
                    $paymentStatus = 'paid';
                } elseif ($totalPaid > 0) {
                    $paymentStatus = 'partial';
                }

                $invoice->update([
                    'paid_amount' => $totalPaid,
                    'balance' => $balance,
                    'payment_status' => $paymentStatus,
                    'paid_at' => $balance <= 0 ? now() : null,
                ]);

                // Sincronizar el estado de pago con la venta relacionada
                if ($invoice->sale_id) {
                    $sale = \App\Models\Sale::find($invoice->sale_id);
                    if ($sale) {
                        $sale->update([
                            'payment_status' => $paymentStatus,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('account-receivables.payments')
                ->with('success', 'Pago registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    public function showPayment(Payment $payment)
    {
        $payment->load(['client', 'invoice', 'creator', 'approver']);

        return Inertia::render('AccountReceivables/PaymentShow', [
            'payment' => $payment,
        ]);
    }

    public function approvePayment(Payment $payment)
    {
        if (!$payment->can_approve) {
            return back()->withErrors(['error' => 'Solo se pueden aprobar pagos pendientes']);
        }

        $payment->approve(Auth::user());

        return redirect()->route('account-receivables.payment.show', $payment)
            ->with('success', 'Pago aprobado exitosamente');
    }

    public function cancelPayment(Request $request, Payment $payment)
    {
        if (!$payment->can_cancel) {
            return back()->withErrors(['error' => 'No se puede cancelar este pago']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $payment->cancel($validated['reason']);

        return redirect()->route('account-receivables.payment.show', $payment)
            ->with('success', 'Pago cancelado');
    }

    public function overdue()
    {
        $invoices = Invoice::overdue()
            ->with(['client', 'creator'])
            ->latest('due_date')
            ->paginate(20);

        return Inertia::render('AccountReceivables/Overdue', [
            'invoices' => $invoices,
        ]);
    }

    public function clientStatement(Request $request, Client $client)
    {
        $query = Invoice::with(['payments'])
            ->where('client_id', $client->id)
            ->where('status', '!=', 'cancelled');

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        $invoices = $query->latest('invoice_date')->get();

        // Calcular totales
        $totals = [
            'totalInvoiced' => $invoices->sum('total'),
            'totalPaid' => $invoices->sum('paid_amount'),
            'totalBalance' => $invoices->sum('balance'),
            'overdueAmount' => $invoices->where('is_overdue', true)->sum('balance'),
        ];

        return Inertia::render('AccountReceivables/ClientStatement', [
            'client' => $client,
            'invoices' => $invoices,
            'totals' => $totals,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    public function agingReport(Request $request)
    {
        $query = Invoice::with(['client'])
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        $invoices = $query->get();

        // Agrupar por rangos de días
        $aging = [
            'current' => $invoices->filter(function ($invoice) {
                return $invoice->due_date && $invoice->due_date >= now();
            })->sum('balance'),
            '1-30' => $invoices->filter(function ($invoice) {
                return $invoice->due_date && 
                       $invoice->due_date < now() && 
                       $invoice->due_date >= now()->subDays(30);
            })->sum('balance'),
            '31-60' => $invoices->filter(function ($invoice) {
                return $invoice->due_date && 
                       $invoice->due_date < now()->subDays(30) && 
                       $invoice->due_date >= now()->subDays(60);
            })->sum('balance'),
            '61-90' => $invoices->filter(function ($invoice) {
                return $invoice->due_date && 
                       $invoice->due_date < now()->subDays(60) && 
                       $invoice->due_date >= now()->subDays(90);
            })->sum('balance'),
            'over_90' => $invoices->filter(function ($invoice) {
                return $invoice->due_date && $invoice->due_date < now()->subDays(90);
            })->sum('balance'),
        ];

        return Inertia::render('AccountReceivables/AgingReport', [
            'invoices' => $invoices,
            'aging' => $aging,
            'clients' => Client::where('status', 'active')->get(['id', 'business_name', 'trade_name']),
            'filters' => $request->only(['client_id']),
        ]);
    }

    public function export(Request $request)
    {
        $query = Invoice::with(['client', 'creator', 'payments'])
            ->where('status', '!=', 'cancelled')
            ->where('total', '>', 0);

        // Aplicar mismos filtros que el index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }

        if ($request->filled('unpaid')) {
            $query->unpaid();
        }

        $invoices = $query->latest('invoice_date')->get();

        // Generar CSV
        $filename = 'cuentas_por_cobrar_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Número de Factura',
                'Cliente',
                'Fecha',
                'Vencimiento',
                'Total',
                'Pagado',
                'Saldo',
                'Estado de Pago',
                'Estado'
            ]);

            // Data
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->client_name,
                    $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '',
                    $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '',
                    number_format($invoice->total, 2, '.', ''),
                    number_format($invoice->paid_amount, 2, '.', ''),
                    number_format($invoice->balance, 2, '.', ''),
                    $invoice->payment_status === 'paid' ? 'Pagado' : ($invoice->payment_status === 'partial' ? 'Parcial' : 'Sin Pagar'),
                    $invoice->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getClientInvoices(Request $request, $clientId)
    {
        $invoices = Invoice::where('client_id', $clientId)
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->select('id', 'invoice_number', 'invoice_date', 'due_date', 'total', 'paid_amount', 'balance')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return response()->json($invoices);
    }
}
