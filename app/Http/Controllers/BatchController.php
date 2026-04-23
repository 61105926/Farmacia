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
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%")
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
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'code']),
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

    // API: devuelve lotes activos de un producto en orden FIFO
    public function forProduct(int $productId)
    {
        $batches = Batch::activeFifo($productId)->get(['id', 'batch_number', 'remaining_quantity', 'expiry_date', 'entry_date', 'supplier']);

        return response()->json($batches);
    }

    // API: devuelve el último lote registrado para un producto (para auto-rellenar formularios)
    public function lastBatch(int $productId)
    {
        $batch = Batch::where('product_id', $productId)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first(['id', 'batch_number', 'expiry_date', 'entry_date', 'supplier', 'cost_price']);

        return response()->json($batch);
    }
}
