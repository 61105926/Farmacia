<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['product.category', 'creator']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($p) => $p->where('description', 'like', "%{$search}%")
                                                       ->orWhere('name', 'like', "%{$search}%")
                                                       ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('expiring_soon')) {
            $query->where('expiry_date', '<=', now()->addDays(30))
                  ->where('expiry_date', '>', now())
                  ->where('status', 'active');
        }

        $batches = $query->orderBy('entry_date', 'asc')
                         ->orderBy('id', 'asc')
                         ->paginate(25)
                         ->withQueryString();

        $stats = [
            'total'        => Batch::count(),
            'active'       => Batch::where('status', 'active')->where('remaining_quantity', '>', 0)->count(),
            'depleted'     => Batch::where('status', 'depleted')->count(),
            'expiringSoon' => Batch::where('status', 'active')
                                   ->where('expiry_date', '<=', now()->addDays(30))
                                   ->where('expiry_date', '>', now())
                                   ->count(),
        ];

        return Inertia::render('Batches/Index', [
            'batches'  => $batches,
            'products' => Product::active()->orderBy('description')->get(['id', 'name', 'description', 'code']),
            'stats'    => $stats,
            'filters'  => $request->only(['search', 'product_id', 'status', 'expiring_soon']),
        ]);
    }

    public function show(int $productId)
    {
        $product = Product::findOrFail($productId);

        $batches = Batch::with('creator')
            ->where('product_id', $productId)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Batches/Show', [
            'product' => $product,
            'batches' => $batches,
        ]);
    }

    public function edit(Batch $batch)
    {
        $batch->load('product');

        return Inertia::render('Batches/Edit', [
            'batch'   => $batch,
            'product' => $batch->product,
        ]);
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'batch_number'      => 'required|string|max:100',
            'supplier'          => 'nullable|string|max:255',
            'entry_date'        => 'required|date',
            'expiry_date'       => 'nullable|date',
            'initial_quantity'  => 'required|integer|min:0',
            'remaining_quantity'=> 'required|integer|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:1000',
            'status'            => 'required|in:active,depleted,expired',
        ]);

        $batch->update($validated);

        // Si el producto no tiene precio de costo o venta, tomarlo del lote
        $product = $batch->product;
        $changes = [];
        if (!empty($validated['cost_price']) && (!$product->cost_price || $product->cost_price == 0)) {
            $changes['cost_price'] = $validated['cost_price'];
        }
        if (!empty($validated['sale_price']) && (!$product->sale_price || $product->sale_price == 0)) {
            $changes['sale_price'] = $validated['sale_price'];
        }
        if (!empty($changes)) {
            $product->update($changes);
        }

        return redirect("/lotes/producto/{$batch->product_id}")
            ->with('success', 'Lote actualizado correctamente.');
    }

    // API: devuelve lotes activos de un producto en orden FIFO
    public function forProduct(int $productId)
    {
        $batches = Batch::activeFifo($productId)->get(['id', 'batch_number', 'remaining_quantity', 'expiry_date', 'entry_date', 'supplier']);

        return response()->json($batches);
    }

    // API: devuelve el lote activo más antiguo (FIFO) para un producto
    public function lastBatch(int $productId)
    {
        $batch = Batch::where('product_id', $productId)
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC')
            ->orderBy('id', 'asc')
            ->first(['id', 'batch_number', 'expiry_date', 'entry_date', 'supplier', 'cost_price']);

        // Si no hay activo, devolver el más reciente registrado como referencia
        if (!$batch) {
            $batch = Batch::where('product_id', $productId)
                ->orderByDesc('entry_date')
                ->orderByDesc('id')
                ->first(['id', 'batch_number', 'expiry_date', 'entry_date', 'supplier', 'cost_price']);
        }

        return response()->json($batch);
    }
}
