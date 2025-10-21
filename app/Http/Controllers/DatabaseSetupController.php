<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSetupController extends Controller
{
    /**
     * Crear todas las tablas necesarias para preventas y ventas
     */
    public function createTables()
    {
        try {
            $created = [];
            
            // Crear tabla presales
            if (!Schema::hasTable('presales')) {
                DB::statement("
                    CREATE TABLE presales (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        client_id BIGINT UNSIGNED NOT NULL,
                        salesperson_id BIGINT UNSIGNED NOT NULL,
                        presale_number VARCHAR(50) NOT NULL UNIQUE,
                        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
                        total_amount DECIMAL(10,2) DEFAULT 0.00,
                        notes TEXT NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_client_id (client_id),
                        INDEX idx_salesperson_id (salesperson_id),
                        INDEX idx_status (status),
                        INDEX idx_presale_number (presale_number)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'presales';
            }
            
            // Crear tabla presale_items
            if (!Schema::hasTable('presale_items')) {
                DB::statement("
                    CREATE TABLE presale_items (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        presale_id BIGINT UNSIGNED NOT NULL,
                        product_id BIGINT UNSIGNED NOT NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_presale_id (presale_id),
                        INDEX idx_product_id (product_id),
                        FOREIGN KEY (presale_id) REFERENCES presales(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'presale_items';
            }
            
            // Crear tabla sales
            if (!Schema::hasTable('sales')) {
                DB::statement("
                    CREATE TABLE sales (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        client_id BIGINT UNSIGNED NOT NULL,
                        salesperson_id BIGINT UNSIGNED NOT NULL,
                        presale_id BIGINT UNSIGNED NULL,
                        sale_number VARCHAR(50) NOT NULL UNIQUE,
                        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
                        total_amount DECIMAL(10,2) DEFAULT 0.00,
                        payment_method ENUM('cash', 'credit', 'transfer') DEFAULT 'cash',
                        notes TEXT NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_client_id (client_id),
                        INDEX idx_salesperson_id (salesperson_id),
                        INDEX idx_presale_id (presale_id),
                        INDEX idx_status (status),
                        INDEX idx_sale_number (sale_number)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'sales';
            }
            
            // Crear tabla sale_items
            if (!Schema::hasTable('sale_items')) {
                DB::statement("
                    CREATE TABLE sale_items (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        sale_id BIGINT UNSIGNED NOT NULL,
                        product_id BIGINT UNSIGNED NOT NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_sale_id (sale_id),
                        INDEX idx_product_id (product_id),
                        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'sale_items';
            }
            
            // Crear tabla products si no existe
            if (!Schema::hasTable('products')) {
                DB::statement("
                    CREATE TABLE products (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        code VARCHAR(100) NULL,
                        description TEXT NULL,
                        category_id BIGINT UNSIGNED NULL,
                        purchase_price DECIMAL(10,2) DEFAULT 0.00,
                        sale_price DECIMAL(10,2) DEFAULT 0.00,
                        stock_quantity INT DEFAULT 0,
                        min_stock INT DEFAULT 0,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_name (name),
                        INDEX idx_code (code),
                        INDEX idx_category_id (category_id),
                        INDEX idx_is_active (is_active)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'products';
            }
            
            // Crear tabla clients si no existe
            if (!Schema::hasTable('clients')) {
                DB::statement("
                    CREATE TABLE clients (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        business_name VARCHAR(255) NOT NULL,
                        trade_name VARCHAR(255) NULL,
                        document_type ENUM('ci', 'nit', 'passport') DEFAULT 'ci',
                        document_number VARCHAR(50) NOT NULL UNIQUE,
                        email VARCHAR(255) NULL,
                        phone VARCHAR(20) NULL,
                        address TEXT NULL,
                        credit_limit DECIMAL(10,2) DEFAULT 0.00,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_business_name (business_name),
                        INDEX idx_document_number (document_number),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'clients';
            }
            
            // Crear tabla users si no existe
            if (!Schema::hasTable('users')) {
                DB::statement("
                    CREATE TABLE users (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        email_verified_at TIMESTAMP NULL,
                        password VARCHAR(255) NOT NULL,
                        phone VARCHAR(20) NULL,
                        document_number VARCHAR(50) NULL,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        blocked_at TIMESTAMP NULL,
                        last_login_at TIMESTAMP NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_name (name),
                        INDEX idx_email (email),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'users';
            }
            
            // Crear tabla categories si no existe
            if (!Schema::hasTable('categories')) {
                DB::statement("
                    CREATE TABLE categories (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        description TEXT NULL,
                        is_active BOOLEAN DEFAULT TRUE,
                        created_by BIGINT UNSIGNED NULL,
                        updated_by BIGINT UNSIGNED NULL,
                        created_at TIMESTAMP NULL DEFAULT NULL,
                        updated_at TIMESTAMP NULL DEFAULT NULL,
                        INDEX idx_name (name),
                        INDEX idx_is_active (is_active)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $created[] = 'categories';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tablas creadas exitosamente',
                'created_tables' => $created,
                'total_created' => count($created)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tablas: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Verificar estado de las tablas
     */
    public function checkTables()
    {
        try {
            $tables = [
                'presales' => Schema::hasTable('presales'),
                'presale_items' => Schema::hasTable('presale_items'),
                'sales' => Schema::hasTable('sales'),
                'sale_items' => Schema::hasTable('sale_items'),
                'products' => Schema::hasTable('products'),
                'clients' => Schema::hasTable('clients'),
                'users' => Schema::hasTable('users'),
                'categories' => Schema::hasTable('categories'),
            ];
            
            $allExist = !in_array(false, $tables);
            
            return response()->json([
                'success' => true,
                'all_tables_exist' => $allExist,
                'tables' => $tables,
                'missing_tables' => array_keys(array_filter($tables, function($exists) { return !$exists; }))
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar tablas: ' . $e->getMessage()
            ], 500);
        }
    }
}
