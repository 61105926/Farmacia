<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('code')->unique()->comment('Código interno del producto');
            $table->string('name')->comment('Nombre del producto');
            $table->text('description')->nullable();
            $table->string('slug')->unique();

            // Categorización
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->string('brand')->nullable()->comment('Marca/Laboratorio');

            // Identificación farmacéutica
            $table->string('active_ingredient')->nullable()->comment('Principio activo');
            $table->string('dosage')->nullable()->comment('Dosificación');
            $table->string('presentation')->nullable()->comment('Presentación (caja, frasco, etc)');
            $table->string('unit_type')->default('unit')->comment('Tipo de unidad (unit, box, bottle, etc)');
            $table->integer('units_per_package')->default(1)->comment('Unidades por paquete');

            // Registro sanitario
            $table->string('sanitary_registration')->nullable()->comment('Registro sanitario');
            $table->date('sanitary_expiry_date')->nullable();

            // Código de barras
            $table->string('barcode')->nullable()->unique();
            $table->string('sku')->nullable();

            // Precios
            $table->decimal('base_price', 10, 2)->default(0)->comment('Precio base');
            $table->decimal('cost_price', 10, 2)->default(0)->comment('Precio de costo');
            $table->decimal('sale_price', 10, 2)->default(0)->comment('Precio de venta público');
            $table->decimal('wholesale_price', 10, 2)->nullable()->comment('Precio mayorista');

            // Impuestos
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('Tasa de impuesto (%)');
            $table->boolean('tax_included')->default(false)->comment('Precio incluye impuestos');

            // Inventario
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0)->comment('Stock mínimo');
            $table->integer('max_stock')->default(0)->comment('Stock máximo');
            $table->integer('reorder_point')->default(0)->comment('Punto de reorden');

            // Características especiales farmacéuticas
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_controlled')->default(false)->comment('Medicamento controlado');
            $table->string('storage_conditions')->nullable()->comment('Condiciones de almacenamiento');
            $table->string('administration_route')->nullable()->comment('Vía de administración');

            // Estado
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('allow_backorder')->default(false);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
