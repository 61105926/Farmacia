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

        $invoices = $query->with('sale:id,invoice_number')->latest('invoice_date')->paginate(15)->withQueryString();

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
            
            try {
                $invoice->load('sale:id,invoice_number');
            } catch (\Exception $e) {
                \Log::warning('Error cargando venta', ['error' => $e->getMessage()]);
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
        $query = Payment::with(['client', 'invoice.sale', 'creator', 'approver']);

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

    public function exportPayments(Request $request)
    {
        try {
            $query = Payment::with(['client', 'invoice.sale', 'creator', 'approver']);

            // Aplicar mismos filtros que el método payments
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

            $payments = $query->latest('payment_date')->get();

            // Preparar datos para exportación con número de nota de pago
            $data = $payments->map(function ($payment) {
                $clientName = $payment->client 
                    ? ($payment->client->business_name ?? $payment->client->trade_name ?? 'N/A')
                    : 'N/A';
                
                $invoiceNumber = $payment->invoice 
                    ? ($payment->invoice->sale->invoice_number ?? $payment->invoice->invoice_number)
                    : 'Sin factura';

                $paymentMethodLabel = 'N/A';
                if ($payment->payment_method) {
                    $methods = Payment::getPaymentMethods();
                    $paymentMethodLabel = $methods[$payment->payment_method] ?? $payment->payment_method;
                }

                return [
                    'Nota de Pago' => $payment->payment_number ?? 'N/A',
                    'Fecha de Pago' => $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A',
                    'Hora' => $payment->payment_date ? $payment->payment_date->format('H:i') : 'N/A',
                    'Cliente' => $clientName,
                    'NIT' => $payment->client->tax_id ?? '',
                    'Factura' => $invoiceNumber,
                    'Monto' => number_format($payment->amount ?? 0, 2, ',', '.'),
                    'Método de Pago' => $paymentMethodLabel,
                    'Referencia' => $payment->payment_reference ?? '',
                    'Banco' => $payment->bank_name ?? '',
                    'Número de Cuenta' => $payment->account_number ?? '',
                    'Número de Cheque' => $payment->check_number ?? '',
                    'Estado' => $payment->status_label ?? 'N/A',
                    'Registrado por' => $payment->creator ? $payment->creator->name : 'Sistema',
                    'Fecha Registro' => $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A',
                    'Aprobado por' => $payment->approver ? $payment->approver->name : '',
                    'Fecha Aprobación' => $payment->approved_at ? $payment->approved_at->format('d/m/Y H:i') : '',
                    'Observaciones' => $payment->notes ?? '',
                ];
            });

            // Generar CSV
            $filename = 'pagos_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                if (!empty($data)) {
                    // Headers
                    fputcsv($file, array_keys($data[0]), ';');
                    
                    // Data
                    foreach ($data as $row) {
                        fputcsv($file, $row, ';');
                    }
                } else {
                    // Si no hay datos, agregar una fila indicando que no hay registros
                    fputcsv($file, ['No hay datos disponibles'], ';');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Error en exportPayments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Error al exportar pagos: ' . $e->getMessage()]);
        }
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

            // Crear notificación para el usuario que registró el pago
            try {
                $payment->load('client', 'invoice');
                $clientName = $payment->client->name ?? 'N/A';
                $invoiceNumber = $payment->invoice ? $payment->invoice->invoice_number : 'N/A';
                
                \App\Helpers\NotificationHelper::success(
                    Auth::user(),
                    'Pago Registrado Exitosamente',
                    "Pago de $" . number_format($payment->amount, 2) . " registrado - Cliente: {$clientName}" . ($payment->invoice_id ? " - Factura: {$invoiceNumber}" : ''),
                    route('account-receivables.index')
                );

                // Notificar a administradores sobre nuevo pago
                $admins = \App\Models\User::role(['super-admin', 'Administrador', 'administrador'])->get();
                if ($admins->isNotEmpty()) {
                    \App\Helpers\NotificationHelper::createForUsers(
                        $admins->all(),
                        'Nuevo Pago Registrado',
                        "Pago de $" . number_format($payment->amount, 2) . " registrado por " . Auth::user()->name . " - Cliente: {$clientName}",
                        'success',
                        route('account-receivables.index')
                    );
                }
            } catch (\Exception $e) {
                // No fallar el registro del pago si hay error en notificaciones
                \Log::warning('AccountReceivableController createPayment - Error al crear notificaciones', [
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id
                ]);
            }

            return redirect()->route('account-receivables.payments')
                ->with('success', 'Pago registrado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    public function showPayment(Payment $payment)
    {
        $payment->load(['client', 'invoice.sale', 'creator', 'approver']);

        return Inertia::render('AccountReceivables/PaymentShow', [
            'payment' => $payment,
        ]);
    }

    public function printPaymentNote(Payment $payment)
    {
        $payment->load(['client', 'invoice.sale', 'creator', 'approver']);

        // Generar ticket térmico
        $ticket = $this->generateThermalTicket($payment);

        return response($ticket, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline'
        ]);
    }

    /**
     * Generate thermal printer ticket for payment note
     */
    private function generateThermalTicket($payment): string
    {
        $lineWidth = 42; // Ancho típico para impresoras térmicas (80mm)

        $ticket = "";

        // Encabezado
        $ticket .= $this->centerText("FARMACIA PANDO", $lineWidth) . "\n";
        $ticket .= $this->centerText("NOTA DE PAGO", $lineWidth) . "\n";
        $ticket .= str_repeat("=", $lineWidth) . "\n";
        $ticket .= "\n";

        // Número de nota y fecha
        $ticket .= "Nº: " . $payment->payment_number . "\n";
        $ticket .= "Fecha: " . $payment->payment_date->format('d/m/Y H:i') . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= "\n";

        // Información del cliente
        $ticket .= $this->centerText("DATOS DEL CLIENTE", $lineWidth) . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        
        if ($payment->client) {
            $ticket .= "Razon Social:\n";
            $ticket .= $this->wrapText($payment->client->business_name ?? 'N/A', $lineWidth) . "\n";
            
            if ($payment->client->trade_name) {
                $ticket .= "Nombre Comercial:\n";
                $ticket .= $this->wrapText($payment->client->trade_name, $lineWidth) . "\n";
            }
            
            if ($payment->client->tax_id) {
                $ticket .= "NIT: " . $payment->client->tax_id . "\n";
            }
            
            if ($payment->client->address) {
                $ticket .= "Direccion:\n";
                $ticket .= $this->wrapText($payment->client->address, $lineWidth) . "\n";
            }
            
            if ($payment->client->phone) {
                $ticket .= "Telefono: " . $payment->client->phone . "\n";
            }
        }
        
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= "\n";

        // Información del pago
        $ticket .= $this->centerText("DATOS DEL PAGO", $lineWidth) . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= "Estado: " . strtoupper($payment->status_label) . "\n";
        $ticket .= "Metodo: " . $payment->payment_method_label . "\n";
        
        if ($payment->payment_reference) {
            $ticket .= "Referencia: " . $payment->payment_reference . "\n";
        }
        
        if ($payment->bank_name) {
            $ticket .= "Banco: " . $payment->bank_name . "\n";
        }
        
        if ($payment->account_number) {
            $ticket .= "Nº Cuenta: " . $payment->account_number . "\n";
        }
        
        if ($payment->check_number) {
            $ticket .= "Nº Cheque: " . $payment->check_number . "\n";
        }
        
        if ($payment->invoice) {
            // Usar el número de factura de la venta si existe, sino el de la factura
            $invoiceNumber = $payment->invoice->sale->invoice_number ?? $payment->invoice->invoice_number;
            $ticket .= "Factura: " . $invoiceNumber . "\n";
        }
        
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $ticket .= "\n";

        // Monto del pago - Sección destacada
        $ticket .= $this->centerText("RECIBI DE", $lineWidth) . "\n";
        $ticket .= str_repeat("-", $lineWidth) . "\n";
        $clientName = $payment->client 
            ? ($payment->client->business_name ?? $payment->client->trade_name ?? 'N/A')
            : 'N/A';
        $ticket .= $this->centerText($this->truncateText($clientName, $lineWidth - 4), $lineWidth) . "\n";
        $ticket .= "\n";
        
        $ticket .= $this->centerText("LA CANTIDAD DE", $lineWidth) . "\n";
        $amountWords = $this->amountInWords($payment->amount);
        $ticket .= $this->wrapText($amountWords, $lineWidth) . "\n";
        $ticket .= "\n";
        
        $ticket .= $this->centerText("EL MONTO DE", $lineWidth) . "\n";
        $ticket .= $this->centerText(number_format($payment->amount, 2, ',', '.') . " Bs.", $lineWidth) . "\n";
        $ticket .= "\n";
        
        if ($payment->invoice) {
            // Usar el número de factura de la venta si existe, sino el de la factura
            $invoiceNumber = $payment->invoice->sale->invoice_number ?? $payment->invoice->invoice_number;
            $ticket .= "En concepto de pago de la\n";
            $ticket .= "factura: " . $invoiceNumber . "\n";
            $ticket .= "\n";
        }
        
        $ticket .= str_repeat("=", $lineWidth) . "\n";
        $ticket .= "\n";

        // Footer
        $ticket .= str_repeat("=", $lineWidth) . "\n";
        $ticket .= $this->centerText("NOTA DE PAGO Nº " . $payment->payment_number, $lineWidth) . "\n";
        $ticket .= $this->centerText("Gracias por su pago", $lineWidth) . "\n";
        $ticket .= "\n";
        $ticket .= "\n";
        $ticket .= "\n";

        return $ticket;
    }

    private function centerText($text, $width): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $textLength = mb_strlen($text);
        $padding = max(0, ($width - $textLength) / 2);
        return str_repeat(" ", (int)$padding) . $text;
    }

    private function truncateText($text, $length): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 3) . '...';
    }

    private function wrapText($text, $width): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            if (mb_strlen($testLine) <= $width) {
                $currentLine = $testLine;
            } else {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }
        
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return implode("\n", $lines);
    }

    private function amountInWords($amount): string
    {
        if (!is_numeric($amount) || $amount == 0) {
            return 'Cero bolivianos';
        }

        $ones = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $teens = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        $hundreds = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        $num = (int)floor($amount);
        $cents = (int)round(($amount - $num) * 100);

        $convert = function($n) use ($ones, $tens, $teens, $hundreds, &$convert) {
            if ($n == 0) return '';
            if ($n < 10) return $ones[$n];
            if ($n < 20) return $teens[$n - 10];
            if ($n < 100) {
                $ten = (int)floor($n / 10);
                $one = $n % 10;
                if ($one == 0) return $tens[$ten];
                if ($ten == 1) return 'dieci' . $ones[$one];
                return $tens[$ten] . ($one > 0 ? ' y ' . $ones[$one] : '');
            }
            if ($n < 1000) {
                $hundred = (int)floor($n / 100);
                $remainder = $n % 100;
                if ($hundred == 1 && $remainder == 0) return 'cien';
                return $hundreds[$hundred] . ($remainder > 0 ? ' ' . $convert($remainder) : '');
            }
            if ($n < 1000000) {
                $thousand = (int)floor($n / 1000);
                $remainder = $n % 1000;
                $thousandText = $thousand == 1 ? 'mil' : $convert($thousand) . ' mil';
                return $thousandText . ($remainder > 0 ? ' ' . $convert($remainder) : '');
            }
            return 'Número muy grande';
        };

        $result = $convert($num);
        if ($num == 0) {
            $result = 'Cero';
        } elseif ($num == 1) {
            $result = 'Un';
        }

        $result = mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);
        $result .= ' bolivianos';

        if ($cents > 0) {
            $centsText = $convert($cents);
            $result .= ' con ' . $centsText . ' centavos';
        }

        return $result;
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
        try {
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

            // Log para depuración
            \Log::info('Export cuentas por cobrar', [
                'total_invoices' => $invoices->count(),
                'filters' => $request->all(),
            ]);

            // Generar CSV
            $filename = 'cuentas_por_cobrar_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
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
                ], ';'); // Usar punto y coma como delimitador para mejor compatibilidad con Excel

                // Data
                if ($invoices->count() === 0) {
                    // Si no hay datos, agregar una fila indicando que no hay registros
                    fputcsv($file, [
                        'No hay datos disponibles',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        ''
                    ], ';');
                } else {
                    foreach ($invoices as $invoice) {
                        try {
                            $invoiceDate = $invoice->invoice_date 
                                ? (is_string($invoice->invoice_date) ? date('d/m/Y', strtotime($invoice->invoice_date)) : $invoice->invoice_date->format('d/m/Y'))
                                : '';
                            
                            $dueDate = $invoice->due_date 
                                ? (is_string($invoice->due_date) ? date('d/m/Y', strtotime($invoice->due_date)) : $invoice->due_date->format('d/m/Y'))
                                : '';

                            $paymentStatusLabel = 'Sin Pagar';
                            if ($invoice->payment_status === 'paid') {
                                $paymentStatusLabel = 'Pagado';
                            } elseif ($invoice->payment_status === 'partial') {
                                $paymentStatusLabel = 'Parcial';
                            }

                            fputcsv($file, [
                                $invoice->invoice_number ?? '',
                                $invoice->client_name ?? ($invoice->client ? ($invoice->client->business_name ?? $invoice->client->trade_name ?? '') : ''),
                                $invoiceDate,
                                $dueDate,
                                number_format($invoice->total ?? 0, 2, '.', ''),
                                number_format($invoice->paid_amount ?? 0, 2, '.', ''),
                                number_format($invoice->balance ?? 0, 2, '.', ''),
                                $paymentStatusLabel,
                                $invoice->status ?? '',
                            ], ';');
                        } catch (\Exception $e) {
                            \Log::error('Error procesando factura en export', [
                                'invoice_id' => $invoice->id ?? 'N/A',
                                'error' => $e->getMessage(),
                            ]);
                            // Continuar con la siguiente factura
                            continue;
                        }
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error en export cuentas por cobrar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Error al exportar datos: ' . $e->getMessage()]);
        }
    }

    public function getClientInvoices(Request $request, $clientId)
    {
        $invoices = Invoice::where('client_id', $clientId)
            ->where('status', '!=', 'cancelled')
            ->where('balance', '>', 0)
            ->with('sale:id,invoice_number')
            ->select('id', 'invoice_number', 'invoice_date', 'due_date', 'total', 'paid_amount', 'balance', 'sale_id')
            ->orderBy('invoice_date', 'desc')
            ->get();

        return response()->json($invoices);
    }
}
