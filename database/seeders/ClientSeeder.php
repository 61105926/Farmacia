<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PaymentTerm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first();
        $paymentTerms = PaymentTerm::all();

        $clients = [
            [
                'code' => 'CLI001',
                'business_name' => 'FARMACIA SAN JOSÉ S.A.',
                'trade_name' => 'Farmacia San José',
                'tax_id' => '1791234567001',
                'client_type' => 'pharmacy',
                'category' => 'A',
                'status' => 'active',
                'address' => 'Av. Amazonas N35-142 y Atahualpa',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'country' => 'Ecuador',
                'postal_code' => '170505',
                'phone' => '02-2234567',
                'email' => 'gerencia@farmaciasanjose.com',
                'price_list_id' => 1,
                'default_discount' => 5.00,
                'payment_term_id' => $paymentTerms->where('name', '30 días')->first()?->id ?? $paymentTerms->first()?->id,
                'credit_limit' => 15000.00,
                'credit_days' => 30,
                'zone' => 'Norte',
                'visit_day' => 'tuesday',
                'visit_frequency' => 'weekly',
                'notes' => 'Cliente preferencial con historial de pagos excelente',
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'CLI002',
                'business_name' => 'FARMACIA LA SALUD LTDA.',
                'trade_name' => 'Farmacia La Salud',
                'tax_id' => '1792345678001',
                'client_type' => 'pharmacy',
                'category' => 'A',
                'status' => 'active',
                'address' => 'Av. 6 de Diciembre N24-156',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'country' => 'Ecuador',
                'postal_code' => '170143',
                'phone' => '02-2345678',
                'email' => 'compras@farmacialasalud.com',
                'price_list_id' => 1,
                'default_discount' => 3.00,
                'payment_term_id' => $paymentTerms->where('name', '15 días')->first()?->id ?? $paymentTerms->first()?->id,
                'credit_limit' => 10000.00,
                'credit_days' => 15,
                'zone' => 'Centro',
                'visit_day' => 'wednesday',
                'visit_frequency' => 'biweekly',
                'notes' => 'Cliente regular',
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'CLI003',
                'business_name' => 'CADENA FARMACÉUTICA DEL VALLE S.A.',
                'trade_name' => 'Farmacias del Valle',
                'tax_id' => '1793456789001',
                'client_type' => 'chain',
                'category' => 'A',
                'status' => 'active',
                'address' => 'Av. Mariscal Sucre y Av. Interoceánica',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'country' => 'Ecuador',
                'postal_code' => '170135',
                'phone' => '02-2456789',
                'email' => 'compras@farmaciasdelvalle.com',
                'price_list_id' => 1,
                'default_discount' => 8.00,
                'payment_term_id' => $paymentTerms->where('name', '30 días')->first()?->id ?? $paymentTerms->first()?->id,
                'credit_limit' => 25000.00,
                'credit_days' => 30,
                'zone' => 'Sur',
                'visit_day' => 'monday',
                'visit_frequency' => 'weekly',
                'notes' => 'Cadena importante, requiere atención especial',
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'CLI004',
                'business_name' => 'FARMACIA EL REMEDIO',
                'trade_name' => 'Farmacia El Remedio',
                'tax_id' => '1794567890001',
                'client_type' => 'pharmacy',
                'category' => 'B',
                'status' => 'active',
                'address' => 'Calle Guayaquil N9-08',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'country' => 'Ecuador',
                'postal_code' => '170401',
                'phone' => '02-2567890',
                'email' => 'info@farmaciaelremedio.com',
                'price_list_id' => 1,
                'default_discount' => 2.00,
                'payment_term_id' => $paymentTerms->where('name', 'Contado')->first()?->id ?? $paymentTerms->first()?->id,
                'credit_limit' => 5000.00,
                'credit_days' => 0,
                'zone' => 'Centro',
                'visit_day' => 'friday',
                'visit_frequency' => 'monthly',
                'notes' => 'Cliente minorista',
                'created_by' => $adminUser->id ?? 1,
            ],
            [
                'code' => 'CLI005',
                'business_name' => 'CLÍNICA SANTA MARÍA S.A.',
                'trade_name' => 'Clínica Santa María',
                'tax_id' => '1795678901001',
                'client_type' => 'hospital',
                'category' => 'A',
                'status' => 'active',
                'address' => 'Av. Mariana de Jesús y Av. 10 de Agosto',
                'city' => 'Quito',
                'state' => 'Pichincha',
                'country' => 'Ecuador',
                'postal_code' => '170102',
                'phone' => '02-2678901',
                'email' => 'farmacia@clinicasantamaria.com',
                'price_list_id' => 1,
                'default_discount' => 10.00,
                'payment_term_id' => $paymentTerms->where('name', '45 días')->first()?->id ?? $paymentTerms->first()?->id,
                'credit_limit' => 50000.00,
                'credit_days' => 45,
                'zone' => 'Norte',
                'visit_day' => 'thursday',
                'visit_frequency' => 'biweekly',
                'notes' => 'Cliente institucional importante',
                'created_by' => $adminUser->id ?? 1,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::firstOrCreate(
                ['code' => $clientData['code']],
                $clientData
            );
            $this->command->info("Cliente creado: {$clientData['business_name']}");
        }

        $this->command->info('✅ Clientes creados exitosamente');
    }
}

