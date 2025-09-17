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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cliente')->unique();
            $table->string('nombre_comercial');
            $table->string('razon_social');
            $table->string('tipo_documento')->default('RUC');
            $table->string('numero_documento')->unique();
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('provincia');
            $table->string('codigo_postal')->nullable();
            $table->string('telefono_principal');
            $table->string('telefono_secundario')->nullable();
            $table->string('email_principal');
            $table->string('email_secundario')->nullable();
            $table->string('contacto_nombre');
            $table->string('contacto_cargo')->nullable();
            $table->string('contacto_telefono')->nullable();
            $table->decimal('limite_credito', 12, 2)->default(0);
            $table->integer('dias_credito')->default(0);
            $table->enum('tipo_cliente', ['regular', 'mayorista', 'preferencial'])->default('regular');
            $table->decimal('descuento_general', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->string('horario_atencion')->nullable();
            $table->string('zona_reparto')->nullable();
            $table->json('configuraciones')->nullable(); // Para configuraciones específicas
            $table->timestamp('ultimo_pedido')->nullable();
            $table->decimal('total_compras', 15, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['activo', 'tipo_cliente']);
            $table->index(['ciudad', 'zona_reparto']);
            $table->index('ultimo_pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
