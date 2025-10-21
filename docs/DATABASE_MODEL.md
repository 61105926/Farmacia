# MODELO DE BASE DE DATOS
## Sistema de Gestión de Preventas, Ventas y Cuentas por Cobrar
### Distribuidora de Medicamentos PANDO

---

**Versión:** 1.0
**Fecha:** 20 de Octubre, 2025
**Motor:** MySQL 8.0+ / PostgreSQL 15+
**ORM:** Laravel Eloquent

---

## ÍNDICE

1. [Diagrama Entidad-Relación (ERD)](#diagrama-entidad-relación)
2. [Diccionario de Datos](#diccionario-de-datos)
3. [Relaciones entre Tablas](#relaciones-entre-tablas)
4. [Índices y Optimizaciones](#índices-y-optimizaciones)
5. [Triggers y Procedimientos](#triggers-y-procedimientos)
6. [Políticas de Datos](#políticas-de-datos)

---

## DIAGRAMA ENTIDAD-RELACIÓN

### Esquema Visual Simplificado

```
┌─────────────────────────────────────────────────────────────────────┐
│                         MÓDULO USUARIOS                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐      ┌──────────┐      ┌─────────────┐               │
│  │  users   │──────│role_user │──────│   roles     │               │
│  └──────────┘  n:m └──────────┘  n:m └─────────────┘               │
│       │                                     │                        │
│       │                              ┌──────────────┐               │
│       │                              │role_permission│              │
│       │                              └──────────────┘               │
│       │                                     │                        │
│       │                              ┌─────────────┐                │
│       │                              │ permissions │                │
│       │                              └─────────────┘                │
└───────┼──────────────────────────────────────────────────────────────┘
        │
┌───────┼──────────────────────────────────────────────────────────────┐
│       │                   MÓDULO CLIENTES                            │
├───────┼──────────────────────────────────────────────────────────────┤
│       │                                                              │
│  ┌────▼─────┐      ┌──────────────┐      ┌─────────────────┐       │
│  │ clients  │──────│client_contacts│      │client_addresses │       │
│  └──────────┘ 1:n  └──────────────┘      └─────────────────┘       │
│       │                                            │                 │
│       │                                            │                 │
└───────┼────────────────────────────────────────────┼─────────────────┘
        │                                            │
┌───────┼────────────────────────────────────────────┼─────────────────┐
│       │              MÓDULO PRODUCTOS                                │
├───────┼────────────────────────────────────────────┼─────────────────┤
│       │                                            │                 │
│  ┌────▼──────┐    ┌─────────────┐    ┌────────────▼──┐             │
│  │ products  │────│product_prices│    │price_lists    │             │
│  └───────────┘1:n └─────────────┘ n:1└───────────────┘             │
│       │                                                              │
│       │         ┌────────────────┐                                  │
│       └─────────│product_categories│                                │
│            n:1  └────────────────┘                                  │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
        │
┌───────┼──────────────────────────────────────────────────────────────┐
│       │              MÓDULO PREVENTAS                                │
├───────┼──────────────────────────────────────────────────────────────┤
│       │                                                              │
│  ┌────▼──────┐       ┌─────────────────┐                           │
│  │ presales  │───────│presale_items    │                           │
│  └───────────┘  1:n  └─────────────────┘                           │
│       │                      │                                       │
│       │                      │                                       │
└───────┼──────────────────────┼───────────────────────────────────────┘
        │                      │
┌───────┼──────────────────────┼───────────────────────────────────────┐
│       │              MÓDULO VENTAS                                   │
├───────┼──────────────────────┼───────────────────────────────────────┤
│       │                      │                                       │
│  ┌────▼──────┐       ┌──────▼──────┐      ┌──────────────┐         │
│  │  sales    │───────│ sale_items  │──────│  products    │         │
│  └───────────┘  1:n  └─────────────┘  n:1 └──────────────┘         │
│       │                                                              │
│       │         ┌────────────────┐                                  │
│       ├─────────│  invoices      │                                  │
│       │    1:1  └────────────────┘                                  │
│       │                │                                             │
│       │         ┌──────▼──────┐                                     │
│       │         │credit_notes │                                     │
│       │         └─────────────┘                                     │
│       │                                                              │
└───────┼──────────────────────────────────────────────────────────────┘
        │
┌───────┼──────────────────────────────────────────────────────────────┐
│       │         MÓDULO CUENTAS POR COBRAR                            │
├───────┼──────────────────────────────────────────────────────────────┤
│       │                                                              │
│  ┌────▼──────────┐     ┌──────────────┐     ┌────────────────┐    │
│  │ receivables   │─────│   payments   │─────│payment_details │    │
│  └───────────────┘ 1:n └──────────────┘ 1:n └────────────────┘    │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
        │
┌───────┼──────────────────────────────────────────────────────────────┐
│       │            MÓDULO INVENTARIO                                 │
├───────┼──────────────────────────────────────────────────────────────┤
│       │                                                              │
│  ┌────▼──────────┐     ┌──────────────┐     ┌────────────────┐    │
│  │  inventory    │─────│inventory_lots│─────│    products    │    │
│  └───────────────┘ 1:n └──────────────┘ n:1 └────────────────┘    │
│       │                                                              │
│  ┌────▼─────────────┐                                               │
│  │inventory_movements│                                              │
│  └──────────────────┘                                               │
│       │                                                              │
│  ┌────▼────────────┐                                                │
│  │ stock_transfers │                                                │
│  └─────────────────┘                                                │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## DICCIONARIO DE DATOS

### MÓDULO 1: USUARIOS Y AUTENTICACIÓN

#### Tabla: `users`
**Descripción:** Usuarios del sistema

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único del usuario |
| name | VARCHAR(255) | NO | - | Nombre completo |
| email | VARCHAR(255) | NO | - | Email (único) |
| email_verified_at | TIMESTAMP | YES | NULL | Fecha verificación email |
| password | VARCHAR(255) | NO | - | Contraseña hasheada |
| phone | VARCHAR(50) | YES | NULL | Teléfono |
| document_number | VARCHAR(50) | YES | NULL | CI/DNI |
| branch_id | BIGINT UNSIGNED | YES | NULL | Sucursal asignada |
| avatar | VARCHAR(255) | YES | NULL | URL foto perfil |
| status | ENUM | NO | 'active' | active, inactive, blocked |
| last_login_at | TIMESTAMP | YES | NULL | Último login |
| last_login_ip | VARCHAR(45) | YES | NULL | IP último acceso |
| failed_login_attempts | INT | NO | 0 | Intentos fallidos |
| must_change_password | BOOLEAN | NO | false | Forzar cambio password |
| remember_token | VARCHAR(100) | YES | NULL | Token recordar sesión |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha creación |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha actualización |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- INDEX (branch_id)
- INDEX (status)

---

#### Tabla: `roles`
**Descripción:** Roles del sistema

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| name | VARCHAR(100) | NO | - | Nombre del rol (único) |
| slug | VARCHAR(100) | NO | - | Slug (super-admin, vendedor) |
| description | TEXT | YES | NULL | Descripción del rol |
| is_system | BOOLEAN | NO | false | Rol del sistema (no editable) |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (slug)

**Datos predefinidos:**
- super-admin
- administrador
- vendedor-preventas
- vendedor-ventas
- cobrador
- bodeguero
- contabilidad
- auditor

---

#### Tabla: `permissions`
**Descripción:** Permisos del sistema

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| name | VARCHAR(100) | NO | - | Nombre del permiso |
| slug | VARCHAR(100) | NO | - | Slug (users.create) |
| module | VARCHAR(50) | NO | - | Módulo (users, sales, etc.) |
| description | TEXT | YES | NULL | Descripción |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (slug)
- INDEX (module)

---

#### Tabla: `role_user` (Pivot)
**Descripción:** Relación muchos a muchos entre usuarios y roles

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| user_id | BIGINT UNSIGNED | NO | - | FK a users |
| role_id | BIGINT UNSIGNED | NO | - | FK a roles |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (user_id, role_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE

---

#### Tabla: `permission_role` (Pivot)
**Descripción:** Relación muchos a muchos entre roles y permisos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| role_id | BIGINT UNSIGNED | NO | - | FK a roles |
| permission_id | BIGINT UNSIGNED | NO | - | FK a permissions |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (role_id, permission_id)
- FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
- FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE

---

#### Tabla: `sessions`
**Descripción:** Registro de sesiones de usuarios

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| user_id | BIGINT UNSIGNED | NO | - | FK a users |
| ip_address | VARCHAR(45) | NO | - | IP de conexión |
| user_agent | TEXT | YES | NULL | Navegador/dispositivo |
| login_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha/hora login |
| logout_at | TIMESTAMP | YES | NULL | Fecha/hora logout |
| latitude | DECIMAL(10,8) | YES | NULL | Geolocalización |
| longitude | DECIMAL(11,8) | YES | NULL | Geolocalización |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (login_at)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### MÓDULO 2: CLIENTES

#### Tabla: `clients`
**Descripción:** Clientes (farmacias, cadenas, hospitales)

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código cliente (único) |
| business_name | VARCHAR(255) | NO | - | Razón social |
| trade_name | VARCHAR(255) | YES | NULL | Nombre comercial |
| tax_id | VARCHAR(50) | NO | - | RUT/NIT (único) |
| client_type | ENUM | NO | - | pharmacy, chain, hospital, clinic, other |
| category | ENUM | NO | 'B' | A, B, C (volumen/riesgo) |
| status | ENUM | NO | 'active' | active, inactive, blocked |
| address | TEXT | YES | NULL | Dirección fiscal |
| city | VARCHAR(100) | YES | NULL | Ciudad |
| state | VARCHAR(100) | YES | NULL | Departamento/Estado |
| country | VARCHAR(100) | NO | 'Bolivia' | País |
| postal_code | VARCHAR(20) | YES | NULL | Código postal |
| phone | VARCHAR(50) | YES | NULL | Teléfono principal |
| email | VARCHAR(255) | YES | NULL | Email principal |
| website | VARCHAR(255) | YES | NULL | Sitio web |
| price_list_id | BIGINT UNSIGNED | YES | NULL | FK a price_lists |
| default_discount | DECIMAL(5,2) | NO | 0.00 | Descuento % (0-100) |
| payment_term_id | BIGINT UNSIGNED | YES | NULL | FK a payment_terms |
| credit_limit | DECIMAL(12,2) | NO | 0.00 | Límite de crédito |
| credit_days | INT | NO | 0 | Plazo máximo (días) |
| salesperson_id | BIGINT UNSIGNED | YES | NULL | Vendedor/Preventista |
| collector_id | BIGINT UNSIGNED | YES | NULL | Cobrador asignado |
| zone | VARCHAR(100) | YES | NULL | Zona geográfica |
| visit_day | ENUM | YES | NULL | monday, tuesday, ... |
| visit_frequency | ENUM | NO | 'weekly' | weekly, biweekly, monthly |
| notes | TEXT | YES | NULL | Observaciones |
| created_by | BIGINT UNSIGNED | YES | NULL | FK a users |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- UNIQUE KEY (tax_id)
- INDEX (status)
- INDEX (client_type)
- INDEX (salesperson_id)
- INDEX (collector_id)
- INDEX (price_list_id)
- FOREIGN KEY (salesperson_id) REFERENCES users(id)
- FOREIGN KEY (collector_id) REFERENCES users(id)

---

#### Tabla: `client_contacts`
**Descripción:** Contactos de clientes

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| name | VARCHAR(255) | NO | - | Nombre completo |
| position | VARCHAR(100) | YES | NULL | Cargo |
| phone | VARCHAR(50) | YES | NULL | Teléfono |
| email | VARCHAR(255) | YES | NULL | Email |
| contact_type | ENUM | NO | 'general' | general, purchases, payments, manager |
| is_primary | BOOLEAN | NO | false | Contacto principal |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (client_id)
- FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE

---

#### Tabla: `client_addresses`
**Descripción:** Direcciones de entrega de clientes

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| name | VARCHAR(255) | NO | - | Nombre sucursal/punto |
| address | TEXT | NO | - | Dirección completa |
| city | VARCHAR(100) | YES | NULL | Ciudad |
| state | VARCHAR(100) | YES | NULL | Departamento |
| postal_code | VARCHAR(20) | YES | NULL | Código postal |
| reference | TEXT | YES | NULL | Punto de referencia |
| latitude | DECIMAL(10,8) | YES | NULL | GPS |
| longitude | DECIMAL(11,8) | YES | NULL | GPS |
| contact_name | VARCHAR(255) | YES | NULL | Contacto en sitio |
| contact_phone | VARCHAR(50) | YES | NULL | Teléfono contacto |
| delivery_hours | VARCHAR(255) | YES | NULL | Horario de recepción |
| special_instructions | TEXT | YES | NULL | Instrucciones especiales |
| is_default | BOOLEAN | NO | false | Dirección predeterminada |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (client_id)
- INDEX (is_default)
- FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE

---

### MÓDULO 3: PRODUCTOS

#### Tabla: `product_categories`
**Descripción:** Categorías de productos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| parent_id | BIGINT UNSIGNED | YES | NULL | Categoría padre |
| name | VARCHAR(255) | NO | - | Nombre categoría |
| slug | VARCHAR(255) | NO | - | Slug único |
| description | TEXT | YES | NULL | Descripción |
| image | VARCHAR(255) | YES | NULL | Imagen categoría |
| sort_order | INT | NO | 0 | Orden visualización |
| is_active | BOOLEAN | NO | true | Activa/Inactiva |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (slug)
- INDEX (parent_id)
- FOREIGN KEY (parent_id) REFERENCES product_categories(id)

---

#### Tabla: `products`
**Descripción:** Productos farmacéuticos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código interno (único) |
| barcode | VARCHAR(100) | YES | NULL | Código de barras |
| generic_name | VARCHAR(255) | NO | - | Nombre genérico |
| commercial_name | VARCHAR(255) | YES | NULL | Nombre comercial |
| active_ingredient | VARCHAR(255) | YES | NULL | Principio activo (DCI) |
| concentration | VARCHAR(100) | YES | NULL | Ej: 500mg, 10mg/ml |
| presentation | VARCHAR(100) | YES | NULL | tableta, cápsula, jarabe |
| pharmaceutical_form | VARCHAR(100) | YES | NULL | Forma farmacéutica |
| administration_route | VARCHAR(100) | YES | NULL | oral, tópica, inyectable |
| laboratory | VARCHAR(255) | YES | NULL | Fabricante |
| sanitary_registry | VARCHAR(100) | YES | NULL | Registro sanitario |
| atc_code | VARCHAR(20) | YES | NULL | Código ATC |
| category_id | BIGINT UNSIGNED | YES | NULL | FK a product_categories |
| unit_of_measure | VARCHAR(50) | NO | 'unit' | Unidad base |
| units_per_package | INT | NO | 1 | Unidades por empaque |
| requires_prescription | BOOLEAN | NO | false | Requiere receta |
| is_controlled | BOOLEAN | NO | false | Producto controlado |
| allow_unit_sale | BOOLEAN | NO | true | Venta por unidad suelta |
| minimum_sale_quantity | INT | NO | 1 | Cantidad mínima venta |
| maximum_sale_quantity | INT | YES | NULL | Cantidad máxima |
| allow_discount | BOOLEAN | NO | true | Permite descuento |
| max_discount_percentage | DECIMAL(5,2) | NO | 100.00 | Descuento máx % |
| track_lot | BOOLEAN | NO | true | Control de lotes |
| track_expiry | BOOLEAN | NO | true | Control vencimiento |
| track_serial | BOOLEAN | NO | false | Control número serie |
| cost | DECIMAL(12,2) | NO | 0.00 | Costo unitario |
| price | DECIMAL(12,2) | NO | 0.00 | Precio venta general |
| image | VARCHAR(255) | YES | NULL | Imagen principal |
| description | TEXT | YES | NULL | Descripción |
| status | ENUM | NO | 'active' | active, inactive, discontinued |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (barcode)
- INDEX (category_id)
- INDEX (status)
- INDEX (active_ingredient)
- FULLTEXT (generic_name, commercial_name, active_ingredient)
- FOREIGN KEY (category_id) REFERENCES product_categories(id)

---

#### Tabla: `product_images`
**Descripción:** Imágenes adicionales de productos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| image_url | VARCHAR(255) | NO | - | URL imagen |
| sort_order | INT | NO | 0 | Orden |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (product_id)
- FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE

---

#### Tabla: `price_lists`
**Descripción:** Listas de precios

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código (único) |
| name | VARCHAR(255) | NO | - | Nombre (General, Mayorista) |
| description | TEXT | YES | NULL | Descripción |
| valid_from | DATE | YES | NULL | Vigencia desde |
| valid_until | DATE | YES | NULL | Vigencia hasta |
| is_active | BOOLEAN | NO | true | Activa |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)

---

#### Tabla: `product_prices`
**Descripción:** Precios de productos por lista

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| price_list_id | BIGINT UNSIGNED | NO | - | FK a price_lists |
| price | DECIMAL(12,2) | NO | 0.00 | Precio |
| valid_from | DATE | YES | NULL | Vigencia desde |
| valid_until | DATE | YES | NULL | Vigencia hasta |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (product_id, price_list_id, valid_from)
- INDEX (product_id)
- INDEX (price_list_id)
- FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
- FOREIGN KEY (price_list_id) REFERENCES price_lists(id) ON DELETE CASCADE

---

### MÓDULO 4: PREVENTAS

#### Tabla: `presales`
**Descripción:** Pedidos de preventistas

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código único |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| client_address_id | BIGINT UNSIGNED | YES | NULL | FK a client_addresses |
| salesperson_id | BIGINT UNSIGNED | NO | - | Preventista |
| branch_id | BIGINT UNSIGNED | YES | NULL | Sucursal |
| presale_date | DATE | NO | - | Fecha pedido |
| delivery_date | DATE | YES | NULL | Fecha entrega solicitada |
| status | ENUM | NO | 'draft' | draft, pending, approved, rejected, converted, cancelled, expired |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal |
| discount_percentage | DECIMAL(5,2) | NO | 0.00 | Descuento % |
| discount_amount | DECIMAL(12,2) | NO | 0.00 | Descuento monto |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA |
| total | DECIMAL(12,2) | NO | 0.00 | Total |
| latitude | DECIMAL(10,8) | YES | NULL | GPS visita |
| longitude | DECIMAL(11,8) | YES | NULL | GPS visita |
| photo_url | VARCHAR(255) | YES | NULL | Foto evidencia |
| notes | TEXT | YES | NULL | Observaciones |
| approved_by | BIGINT UNSIGNED | YES | NULL | Usuario aprobador |
| approved_at | TIMESTAMP | YES | NULL | Fecha aprobación |
| rejection_reason | TEXT | YES | NULL | Motivo rechazo |
| converted_to_sale_id | BIGINT UNSIGNED | YES | NULL | FK a sales |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (client_id)
- INDEX (salesperson_id)
- INDEX (status)
- INDEX (presale_date)
- FOREIGN KEY (client_id) REFERENCES clients(id)
- FOREIGN KEY (salesperson_id) REFERENCES users(id)

---

#### Tabla: `presale_items`
**Descripción:** Ítems de preventas

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| presale_id | BIGINT UNSIGNED | NO | - | FK a presales |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| quantity | DECIMAL(10,2) | NO | 1.00 | Cantidad |
| unit_price | DECIMAL(12,2) | NO | 0.00 | Precio unitario |
| discount_percentage | DECIMAL(5,2) | NO | 0.00 | Descuento % |
| discount_amount | DECIMAL(12,2) | NO | 0.00 | Descuento monto |
| tax_percentage | DECIMAL(5,2) | NO | 0.00 | IVA % |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA monto |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal línea |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (presale_id)
- INDEX (product_id)
- FOREIGN KEY (presale_id) REFERENCES presales(id) ON DELETE CASCADE
- FOREIGN KEY (product_id) REFERENCES products(id)

---

### MÓDULO 5: VENTAS

#### Tabla: `payment_terms`
**Descripción:** Condiciones de pago

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código |
| name | VARCHAR(255) | NO | - | Nombre (Contado, 30 días) |
| days | INT | NO | 0 | Plazo en días |
| requires_approval | BOOLEAN | NO | false | Requiere aprobación |
| is_active | BOOLEAN | NO | true | Activa |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

---

#### Tabla: `sales`
**Descripción:** Documentos de venta (cotización, pedido, factura)

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| document_type | ENUM | NO | - | quote, order, invoice |
| code | VARCHAR(50) | NO | - | Número documento (único) |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| client_address_id | BIGINT UNSIGNED | YES | NULL | FK a client_addresses |
| presale_id | BIGINT UNSIGNED | YES | NULL | FK a presales |
| branch_id | BIGINT UNSIGNED | YES | NULL | Sucursal |
| salesperson_id | BIGINT UNSIGNED | YES | NULL | Vendedor |
| issue_date | DATE | NO | - | Fecha emisión |
| due_date | DATE | YES | NULL | Fecha vencimiento |
| payment_term_id | BIGINT UNSIGNED | YES | NULL | FK a payment_terms |
| payment_method | ENUM | YES | NULL | cash, credit, check, transfer, card |
| status | ENUM | NO | 'draft' | draft, confirmed, dispatched, delivered, invoiced, cancelled |
| currency | VARCHAR(3) | NO | 'BOB' | Moneda (BOB, USD) |
| exchange_rate | DECIMAL(10,4) | NO | 1.0000 | Tipo de cambio |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal |
| discount_percentage | DECIMAL(5,2) | NO | 0.00 | Descuento % |
| discount_amount | DECIMAL(12,2) | NO | 0.00 | Descuento monto |
| taxable_amount | DECIMAL(12,2) | NO | 0.00 | Base imponible |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA |
| total | DECIMAL(12,2) | NO | 0.00 | Total |
| notes | TEXT | YES | NULL | Observaciones |
| internal_notes | TEXT | YES | NULL | Notas internas |
| approved_by | BIGINT UNSIGNED | YES | NULL | Usuario aprobador |
| approved_at | TIMESTAMP | YES | NULL | Fecha aprobación |
| cancelled_by | BIGINT UNSIGNED | YES | NULL | Usuario anulador |
| cancelled_at | TIMESTAMP | YES | NULL | Fecha anulación |
| cancellation_reason | TEXT | YES | NULL | Motivo anulación |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| deleted_at | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (client_id)
- INDEX (document_type)
- INDEX (status)
- INDEX (issue_date)
- FOREIGN KEY (client_id) REFERENCES clients(id)

---

#### Tabla: `sale_items`
**Descripción:** Ítems de ventas

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| sale_id | BIGINT UNSIGNED | NO | - | FK a sales |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| lot_id | BIGINT UNSIGNED | YES | NULL | FK a inventory_lots |
| quantity | DECIMAL(10,2) | NO | 1.00 | Cantidad |
| unit_price | DECIMAL(12,2) | NO | 0.00 | Precio unitario |
| discount_percentage | DECIMAL(5,2) | NO | 0.00 | Descuento % |
| discount_amount | DECIMAL(12,2) | NO | 0.00 | Descuento monto |
| tax_percentage | DECIMAL(5,2) | NO | 0.00 | IVA % |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA monto |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal línea |
| cost | DECIMAL(12,2) | NO | 0.00 | Costo unitario |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (sale_id)
- INDEX (product_id)
- FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
- FOREIGN KEY (product_id) REFERENCES products(id)

---

#### Tabla: `credit_notes`
**Descripción:** Notas de crédito

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Número NC (único) |
| sale_id | BIGINT UNSIGNED | NO | - | FK a sales (factura orig) |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| issue_date | DATE | NO | - | Fecha emisión |
| credit_note_type | ENUM | NO | - | return, error, discount, cancellation |
| reason | TEXT | NO | - | Motivo |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA |
| total | DECIMAL(12,2) | NO | 0.00 | Total |
| status | ENUM | NO | 'draft' | draft, approved, applied |
| created_by | BIGINT UNSIGNED | YES | NULL | FK a users |
| approved_by | BIGINT UNSIGNED | YES | NULL | FK a users |
| approved_at | TIMESTAMP | YES | NULL | - |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (sale_id)
- INDEX (client_id)
- FOREIGN KEY (sale_id) REFERENCES sales(id)
- FOREIGN KEY (client_id) REFERENCES clients(id)

---

#### Tabla: `credit_note_items`
**Descripción:** Ítems de notas de crédito

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| credit_note_id | BIGINT UNSIGNED | NO | - | FK a credit_notes |
| sale_item_id | BIGINT UNSIGNED | YES | NULL | FK a sale_items |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| quantity | DECIMAL(10,2) | NO | 1.00 | Cantidad devuelta |
| unit_price | DECIMAL(12,2) | NO | 0.00 | Precio unitario |
| tax_percentage | DECIMAL(5,2) | NO | 0.00 | IVA % |
| tax_amount | DECIMAL(12,2) | NO | 0.00 | IVA monto |
| subtotal | DECIMAL(12,2) | NO | 0.00 | Subtotal |
| return_status | ENUM | YES | NULL | good, damaged, expired |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (credit_note_id)
- FOREIGN KEY (credit_note_id) REFERENCES credit_notes(id) ON DELETE CASCADE

---

### MÓDULO 6: CUENTAS POR COBRAR

#### Tabla: `receivables`
**Descripción:** Cuentas por cobrar (facturas pendientes)

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| sale_id | BIGINT UNSIGNED | NO | - | FK a sales (factura) |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| invoice_number | VARCHAR(50) | NO | - | Número factura |
| issue_date | DATE | NO | - | Fecha emisión |
| due_date | DATE | NO | - | Fecha vencimiento |
| original_amount | DECIMAL(12,2) | NO | 0.00 | Monto original |
| paid_amount | DECIMAL(12,2) | NO | 0.00 | Monto pagado |
| balance | DECIMAL(12,2) | NO | 0.00 | Saldo pendiente |
| status | ENUM | NO | 'pending' | pending, partial, paid, overdue |
| days_overdue | INT | NO | 0 | Días vencidos |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (sale_id)
- INDEX (client_id)
- INDEX (status)
- INDEX (due_date)
- FOREIGN KEY (sale_id) REFERENCES sales(id)
- FOREIGN KEY (client_id) REFERENCES clients(id)

---

#### Tabla: `payments`
**Descripción:** Pagos recibidos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Número recibo (único) |
| client_id | BIGINT UNSIGNED | NO | - | FK a clients |
| payment_date | DATE | NO | - | Fecha pago |
| payment_method | ENUM | NO | - | cash, check, transfer, card |
| reference_number | VARCHAR(100) | YES | NULL | Nro cheque/transfer |
| bank | VARCHAR(255) | YES | NULL | Banco |
| check_date | DATE | YES | NULL | Fecha cheque |
| deposit_date | DATE | YES | NULL | Fecha depósito |
| check_status | ENUM | YES | NULL | received, deposited, cleared, rejected |
| amount | DECIMAL(12,2) | NO | 0.00 | Monto total |
| assigned_amount | DECIMAL(12,2) | NO | 0.00 | Monto asignado |
| unassigned_amount | DECIMAL(12,2) | NO | 0.00 | Monto sin asignar |
| notes | TEXT | YES | NULL | Observaciones |
| receipt_file | VARCHAR(255) | YES | NULL | Comprobante |
| collected_by | BIGINT UNSIGNED | YES | NULL | Cobrador |
| registered_by | BIGINT UNSIGNED | YES | NULL | Usuario registro |
| status | ENUM | NO | 'pending' | pending, applied, reversed |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (client_id)
- INDEX (payment_date)
- INDEX (payment_method)
- FOREIGN KEY (client_id) REFERENCES clients(id)

---

#### Tabla: `payment_applications`
**Descripción:** Aplicación de pagos a facturas

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| payment_id | BIGINT UNSIGNED | NO | - | FK a payments |
| receivable_id | BIGINT UNSIGNED | NO | - | FK a receivables |
| amount | DECIMAL(12,2) | NO | 0.00 | Monto aplicado |
| applied_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha aplicación |
| applied_by | BIGINT UNSIGNED | YES | NULL | Usuario |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (payment_id)
- INDEX (receivable_id)
- FOREIGN KEY (payment_id) REFERENCES payments(id)
- FOREIGN KEY (receivable_id) REFERENCES receivables(id)

---

### MÓDULO 7: INVENTARIO

#### Tabla: `branches`
**Descripción:** Sucursales

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Código (único) |
| name | VARCHAR(255) | NO | - | Nombre |
| address | TEXT | YES | NULL | Dirección |
| city | VARCHAR(100) | YES | NULL | Ciudad |
| phone | VARCHAR(50) | YES | NULL | Teléfono |
| manager_id | BIGINT UNSIGNED | YES | NULL | Responsable |
| branch_type | ENUM | NO | 'branch' | headquarters, branch, warehouse |
| is_active | BOOLEAN | NO | true | Activa |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)

---

#### Tabla: `warehouses`
**Descripción:** Almacenes por sucursal

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| branch_id | BIGINT UNSIGNED | NO | - | FK a branches |
| code | VARCHAR(50) | NO | - | Código (único) |
| name | VARCHAR(255) | NO | - | Nombre |
| location | VARCHAR(255) | YES | NULL | Ubicación física |
| manager_id | BIGINT UNSIGNED | YES | NULL | Responsable |
| warehouse_type | ENUM | NO | 'sales' | sales, transit, quarantine, damaged |
| is_active | BOOLEAN | NO | true | Activo |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (branch_id)
- FOREIGN KEY (branch_id) REFERENCES branches(id)

---

#### Tabla: `inventory`
**Descripción:** Stock por producto y sucursal

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| warehouse_id | BIGINT UNSIGNED | NO | - | FK a warehouses |
| physical_stock | DECIMAL(10,2) | NO | 0.00 | Stock físico |
| reserved_stock | DECIMAL(10,2) | NO | 0.00 | Stock reservado |
| available_stock | DECIMAL(10,2) | NO | 0.00 | Stock disponible |
| in_transit_stock | DECIMAL(10,2) | NO | 0.00 | En tránsito |
| minimum_stock | DECIMAL(10,2) | NO | 0.00 | Stock mínimo |
| maximum_stock | DECIMAL(10,2) | NO | 0.00 | Stock máximo |
| reorder_point | DECIMAL(10,2) | NO | 0.00 | Punto de reorden |
| reorder_quantity | DECIMAL(10,2) | NO | 0.00 | Cantidad reorden |
| average_cost | DECIMAL(12,2) | NO | 0.00 | Costo promedio |
| last_entry_date | DATE | YES | NULL | Última entrada |
| last_exit_date | DATE | YES | NULL | Última salida |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (product_id, warehouse_id)
- INDEX (product_id)
- INDEX (warehouse_id)
- INDEX (available_stock)
- FOREIGN KEY (product_id) REFERENCES products(id)
- FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)

---

#### Tabla: `inventory_lots`
**Descripción:** Lotes de productos

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| warehouse_id | BIGINT UNSIGNED | NO | - | FK a warehouses |
| lot_number | VARCHAR(100) | NO | - | Número de lote |
| manufacture_date | DATE | YES | NULL | Fecha fabricación |
| expiry_date | DATE | YES | NULL | Fecha vencimiento |
| quantity | DECIMAL(10,2) | NO | 0.00 | Cantidad |
| cost | DECIMAL(12,2) | NO | 0.00 | Costo unitario |
| status | ENUM | NO | 'active' | active, expired, quarantine, damaged |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (product_id)
- INDEX (warehouse_id)
- INDEX (lot_number)
- INDEX (expiry_date)
- INDEX (status)
- FOREIGN KEY (product_id) REFERENCES products(id)
- FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)

---

#### Tabla: `inventory_movements`
**Descripción:** Movimientos de inventario

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| movement_type | ENUM | NO | - | entry, exit, adjustment, transfer |
| movement_reason | ENUM | NO | - | purchase, sale, return, adjustment, transfer, waste |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| warehouse_id | BIGINT UNSIGNED | NO | - | FK a warehouses |
| lot_id | BIGINT UNSIGNED | YES | NULL | FK a inventory_lots |
| quantity | DECIMAL(10,2) | NO | 0.00 | Cantidad (+ o -) |
| cost | DECIMAL(12,2) | NO | 0.00 | Costo unitario |
| reference_type | VARCHAR(100) | YES | NULL | Sale, Purchase, Transfer |
| reference_id | BIGINT UNSIGNED | YES | NULL | ID documento ref |
| movement_date | DATE | NO | - | Fecha movimiento |
| notes | TEXT | YES | NULL | Observaciones |
| created_by | BIGINT UNSIGNED | YES | NULL | Usuario |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (product_id)
- INDEX (warehouse_id)
- INDEX (movement_type)
- INDEX (movement_date)
- INDEX (reference_type, reference_id)
- FOREIGN KEY (product_id) REFERENCES products(id)
- FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)

---

#### Tabla: `stock_transfers`
**Descripción:** Transferencias entre sucursales

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| code | VARCHAR(50) | NO | - | Número (único) |
| origin_warehouse_id | BIGINT UNSIGNED | NO | - | FK a warehouses |
| destination_warehouse_id | BIGINT UNSIGNED | NO | - | FK a warehouses |
| transfer_date | DATE | NO | - | Fecha transferencia |
| expected_arrival_date | DATE | YES | NULL | Fecha llegada est. |
| status | ENUM | NO | 'requested' | requested, approved, in_transit, received, rejected |
| reason | TEXT | YES | NULL | Motivo |
| requested_by | BIGINT UNSIGNED | YES | NULL | Usuario solicitante |
| approved_by | BIGINT UNSIGNED | YES | NULL | Usuario aprobador |
| approved_at | TIMESTAMP | YES | NULL | Fecha aprobación |
| received_by | BIGINT UNSIGNED | YES | NULL | Usuario receptor |
| received_at | TIMESTAMP | YES | NULL | Fecha recepción |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (code)
- INDEX (origin_warehouse_id)
- INDEX (destination_warehouse_id)
- INDEX (status)
- FOREIGN KEY (origin_warehouse_id) REFERENCES warehouses(id)
- FOREIGN KEY (destination_warehouse_id) REFERENCES warehouses(id)

---

#### Tabla: `stock_transfer_items`
**Descripción:** Ítems de transferencias

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| stock_transfer_id | BIGINT UNSIGNED | NO | - | FK a stock_transfers |
| product_id | BIGINT UNSIGNED | NO | - | FK a products |
| lot_id | BIGINT UNSIGNED | YES | NULL | FK a inventory_lots |
| quantity_requested | DECIMAL(10,2) | NO | 0.00 | Cant. solicitada |
| quantity_sent | DECIMAL(10,2) | NO | 0.00 | Cant. enviada |
| quantity_received | DECIMAL(10,2) | NO | 0.00 | Cant. recibida |
| notes | TEXT | YES | NULL | Observaciones |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (stock_transfer_id)
- INDEX (product_id)
- FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE
- FOREIGN KEY (product_id) REFERENCES products(id)

---

### MÓDULO 8: AUDITORÍA

#### Tabla: `activity_log`
**Descripción:** Log de actividades (auditoría)

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| log_name | VARCHAR(100) | YES | NULL | Nombre del log |
| description | TEXT | NO | - | Descripción acción |
| subject_type | VARCHAR(100) | YES | NULL | Modelo (Sale, User) |
| subject_id | BIGINT UNSIGNED | YES | NULL | ID del registro |
| causer_type | VARCHAR(100) | YES | NULL | Usuario (User) |
| causer_id | BIGINT UNSIGNED | YES | NULL | ID usuario |
| properties | JSON | YES | NULL | Datos (before, after) |
| event | VARCHAR(50) | YES | NULL | created, updated, deleted |
| ip_address | VARCHAR(45) | YES | NULL | IP |
| user_agent | TEXT | YES | NULL | Navegador |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- INDEX (subject_type, subject_id)
- INDEX (causer_type, causer_id)
- INDEX (log_name)
- INDEX (created_at)

---

### MÓDULO 9: CONFIGURACIÓN

#### Tabla: `system_settings`
**Descripción:** Configuración del sistema

| Campo | Tipo | Nulo | Predeterminado | Descripción |
|-------|------|------|----------------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| key | VARCHAR(100) | NO | - | Clave (único) |
| value | TEXT | YES | NULL | Valor |
| type | ENUM | NO | 'string' | string, integer, boolean, json |
| group | VARCHAR(50) | NO | 'general' | general, fiscal, email, etc. |
| is_public | BOOLEAN | NO | false | Visible en frontend |
| description | TEXT | YES | NULL | Descripción |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - |

**Índices:**
- PRIMARY KEY (id)
- UNIQUE KEY (key)
- INDEX (group)

**Ejemplos de configuraciones:**
- company.name
- company.tax_id
- company.logo
- tax.vat_rate
- email.smtp_host
- currency.default
- locale.timezone

---

## RELACIONES ENTRE TABLAS

### Resumen de Relaciones Principales

```
users (1) ──────── (n) role_user (n) ──────── (1) roles
                                      │
                                      │
                              (n) permission_role (n) ──── (1) permissions

users (1) ──────── (n) clients (como vendedor)
users (1) ──────── (n) clients (como cobrador)

clients (1) ──────── (n) client_contacts
clients (1) ──────── (n) client_addresses
clients (1) ──────── (n) presales
clients (1) ──────── (n) sales
clients (1) ──────── (n) receivables
clients (n) ──────── (1) price_lists

products (1) ──────── (n) product_prices (n) ──────── (1) price_lists
products (1) ──────── (n) product_images
products (n) ──────── (1) product_categories

presales (1) ──────── (n) presale_items (n) ──────── (1) products
presales (1) ──────── (1) sales (conversión)

sales (1) ──────── (n) sale_items (n) ──────── (1) products
sales (1) ──────── (n) credit_notes
sales (1) ──────── (1) receivables

receivables (1) ──────── (n) payment_applications (n) ──────── (1) payments

branches (1) ──────── (n) warehouses
warehouses (1) ──────── (n) inventory (n) ──────── (1) products
warehouses (1) ──────── (n) inventory_lots
warehouses (1) ──────── (n) inventory_movements

warehouses (1) ──────── (n) stock_transfers (como origen)
warehouses (1) ──────── (n) stock_transfers (como destino)
stock_transfers (1) ──────── (n) stock_transfer_items
```

---

## ÍNDICES Y OPTIMIZACIONES

### Índices Recomendados Adicionales

```sql
-- Índices compuestos para consultas frecuentes
CREATE INDEX idx_sales_client_date ON sales(client_id, issue_date);
CREATE INDEX idx_receivables_client_status ON receivables(client_id, status);
CREATE INDEX idx_inventory_product_warehouse ON inventory(product_id, warehouse_id);
CREATE INDEX idx_movements_product_date ON inventory_movements(product_id, movement_date);

-- Índices de texto completo para búsquedas
CREATE FULLTEXT INDEX ft_products_search ON products(generic_name, commercial_name, active_ingredient);
CREATE FULLTEXT INDEX ft_clients_search ON clients(business_name, trade_name, tax_id);

-- Índices para reportes
CREATE INDEX idx_sales_date_status ON sales(issue_date, status);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_lots_expiry ON inventory_lots(expiry_date, status);
```

### Particionamiento (para grandes volúmenes)

```sql
-- Particionar sales por año
ALTER TABLE sales PARTITION BY RANGE (YEAR(issue_date)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Particionar inventory_movements por mes
ALTER TABLE inventory_movements PARTITION BY RANGE (YEAR(movement_date) * 100 + MONTH(movement_date)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    -- etc...
);
```

---

## TRIGGERS Y PROCEDIMIENTOS

### Triggers Importantes

#### 1. Actualizar Stock Disponible
```sql
DELIMITER //
CREATE TRIGGER update_inventory_available_stock
AFTER INSERT ON inventory_movements
FOR EACH ROW
BEGIN
    UPDATE inventory
    SET available_stock = physical_stock - reserved_stock
    WHERE product_id = NEW.product_id
      AND warehouse_id = NEW.warehouse_id;
END//
DELIMITER ;
```

#### 2. Actualizar Saldo de Receivables
```sql
DELIMITER //
CREATE TRIGGER update_receivable_balance
AFTER INSERT ON payment_applications
FOR EACH ROW
BEGIN
    UPDATE receivables
    SET paid_amount = paid_amount + NEW.amount,
        balance = original_amount - (paid_amount + NEW.amount),
        status = CASE
            WHEN (paid_amount + NEW.amount) >= original_amount THEN 'paid'
            WHEN (paid_amount + NEW.amount) > 0 THEN 'partial'
            ELSE 'pending'
        END
    WHERE id = NEW.receivable_id;
END//
DELIMITER ;
```

#### 3. Calcular Días Vencidos
```sql
DELIMITER //
CREATE EVENT update_overdue_days
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE receivables
    SET days_overdue = CASE
        WHEN due_date < CURDATE() THEN DATEDIFF(CURDATE(), due_date)
        ELSE 0
    END,
    status = CASE
        WHEN balance > 0 AND due_date < CURDATE() THEN 'overdue'
        WHEN balance > 0 THEN 'pending'
        WHEN balance = 0 THEN 'paid'
        ELSE status
    END
    WHERE status IN ('pending', 'partial', 'overdue');
END//
DELIMITER ;
```

---

## POLÍTICAS DE DATOS

### Soft Deletes
Tablas con soft delete (deleted_at):
- users
- clients
- products
- presales
- sales

### Timestamps
Todas las tablas incluyen:
- created_at (automático)
- updated_at (automático)

### Foreign Keys
- ON DELETE CASCADE: Para relaciones dependientes (items de documentos)
- ON DELETE SET NULL: Para relaciones opcionales (vendedor asignado)
- ON DELETE RESTRICT: Para prevenir eliminación (clientes con facturas)

### Validaciones a Nivel de Base de Datos

```sql
-- Validar cantidades positivas
ALTER TABLE products ADD CONSTRAINT chk_positive_cost CHECK (cost >= 0);
ALTER TABLE sale_items ADD CONSTRAINT chk_positive_quantity CHECK (quantity > 0);

-- Validar rangos de descuentos
ALTER TABLE clients ADD CONSTRAINT chk_discount_range CHECK (default_discount BETWEEN 0 AND 100);

-- Validar fechas
ALTER TABLE presales ADD CONSTRAINT chk_delivery_date CHECK (delivery_date >= presale_date);
ALTER TABLE receivables ADD CONSTRAINT chk_due_date CHECK (due_date >= issue_date);
```

---

## ESTIMACIÓN DE TAMAÑO

### Proyección de Crecimiento (3 años)

| Tabla | Registros/Año | Total 3 años | Tamaño Est. |
|-------|---------------|--------------|-------------|
| users | 50 | 150 | 50 KB |
| clients | 500 | 1,500 | 500 KB |
| products | 2,000 | 6,000 | 2 MB |
| presales | 50,000 | 150,000 | 50 MB |
| presale_items | 250,000 | 750,000 | 100 MB |
| sales | 40,000 | 120,000 | 40 MB |
| sale_items | 200,000 | 600,000 | 80 MB |
| receivables | 40,000 | 120,000 | 30 MB |
| payments | 60,000 | 180,000 | 50 MB |
| inventory_movements | 300,000 | 900,000 | 150 MB |
| activity_log | 500,000 | 1,500,000 | 500 MB |

**Total estimado:** ~1 GB de datos en 3 años

---

## SEGURIDAD Y BACKUPS

### Estrategia de Backup
- **Full Backup:** Diario (3:00 AM)
- **Incremental:** Cada 6 horas
- **Retención:** 30 días locales, 1 año en cloud
- **Restore Test:** Mensual

### Cifrado
- Datos en tránsito: TLS 1.3
- Datos en reposo: AES-256
- Contraseñas: bcrypt (10 rounds)

---

**Próximos pasos:**
1. Crear migraciones Laravel
2. Crear seeders con datos de prueba
3. Crear modelos Eloquent con relaciones
4. Crear factories para testing

---

**Documento preparado por:** Gabriel
**Para:** Distribuidora de Medicamentos PANDO
**Fecha:** 20 de Octubre, 2025
