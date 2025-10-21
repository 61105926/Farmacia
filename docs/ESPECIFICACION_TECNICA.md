# ESPECIFICACIÓN TÉCNICA DEL SISTEMA
## Sistema de Gestión de Preventas, Ventas y Cuentas por Cobrar
### Distribuidora de Medicamentos PANDO

---

**Versión:** 1.0
**Fecha:** 20 de Octubre, 2025
**Desarrollador:** Gabriel
**Cliente:** Distribuidora de Medicamentos PANDO

---

## TABLA DE CONTENIDOS

1. [Introducción](#introducción)
2. [Objetivos del Sistema](#objetivos-del-sistema)
3. [Alcance](#alcance)
4. [Stack Tecnológico](#stack-tecnológico)
5. [Arquitectura del Sistema](#arquitectura-del-sistema)
6. [Requisitos Funcionales por Módulo](#requisitos-funcionales-por-módulo)
7. [Requisitos No Funcionales](#requisitos-no-funcionales)
8. [Modelo de Datos](#modelo-de-datos)
9. [Flujos de Trabajo](#flujos-de-trabajo)
10. [Seguridad](#seguridad)
11. [Integraciones](#integraciones)
12. [Plan de Implementación](#plan-de-implementación)

---

## INTRODUCCIÓN

El Sistema de Gestión Comercial para Distribuidora de Medicamentos PANDO es una solución web integral diseñada para optimizar y automatizar los procesos de preventas, ventas y cuentas por cobrar en la distribución de productos farmacéuticos a farmacias, cadenas y establecimientos de salud.

### Contexto del Negocio
- **Industria:** Distribución farmacéutica
- **Clientes objetivo:** Farmacias minoristas, cadenas de farmacias, hospitales y clínicas
- **Modelo de negocio:** Ventas B2B con crédito comercial
- **Operación:** Multi-sucursal con fuerza de ventas en campo (preventistas)

---

## OBJETIVOS DEL SISTEMA

### Objetivos de Negocio
1. **Incrementar eficiencia de ventas:** Reducir tiempo de procesamiento de pedidos en 60%
2. **Mejorar control de crédito:** Reducir cartera vencida en 40%
3. **Optimizar inventarios:** Reducir mermas por vencimiento en 30%
4. **Aumentar precisión:** Eliminar errores de digitación en 95%
5. **Mejorar trazabilidad:** 100% de operaciones auditables

### Objetivos Técnicos
1. Sistema web responsive (móvil + desktop)
2. Disponibilidad 99.5% (SLA)
3. Tiempo de respuesta < 2 segundos
4. Operación offline para preventistas
5. Integraciones API con sistemas externos

---

## ALCANCE

### Dentro del Alcance
✅ 10 módulos principales completos
✅ Aplicación web responsive
✅ App móvil para preventistas (PWA)
✅ Gestión de usuarios y roles
✅ Gestión de clientes y límites de crédito
✅ Preventas con reserva de stock
✅ Ventas con emisión de documentos
✅ Cuentas por cobrar y cobranzas
✅ Catálogo de productos farmacéuticos
✅ Control de inventario multi-sucursal
✅ Reportes y dashboards
✅ Auditoría completa
✅ Configuración del sistema
✅ Integración con facturación electrónica
✅ API REST para integraciones

### Fuera del Alcance (Fase 1)
❌ Módulo de compras a proveedores
❌ Módulo de producción/manufactura
❌ Integración con sistemas bancarios
❌ E-commerce para clientes finales
❌ App móvil nativa (iOS/Android)
❌ Inteligencia artificial / ML

---

## STACK TECNOLÓGICO

### Backend
- **Framework:** Laravel 11.x (PHP 8.3+)
- **Base de datos:** MySQL 8.0+ / PostgreSQL 15+
- **Cache:** Redis
- **Queue:** Redis Queue / Laravel Horizon
- **API:** RESTful API + GraphQL (opcional)
- **Authentication:** Laravel Sanctum + JWT

### Frontend
- **Framework:** Vue.js 3.5+ con Composition API
- **SSR/Router:** Inertia.js 2.x
- **UI Framework:** Tailwind CSS 4.0
- **Componentes:** Radix Vue + custom components
- **Iconos:** Lucide Vue Next
- **State Management:** Pinia (si es necesario)
- **Build Tool:** Vite 7.x

### Mobile/PWA
- **PWA:** Service Workers para offline
- **Geolocalización:** Navigator API
- **Cámara:** MediaDevices API (fotos de evidencia)

### DevOps
- **Containerización:** Docker + Docker Compose
- **CI/CD:** GitHub Actions / GitLab CI
- **Servidor:** Nginx + PHP-FPM
- **Monitoreo:** Laravel Telescope + Sentry
- **Testing:** PHPUnit + Pest + Cypress

### Servicios Externos
- **Facturación electrónica:** API Siat (Bolivia) / SRI (Ecuador) / SUNAT (Perú)
- **Notificaciones:** Email (SMTP) + WhatsApp Business API
- **Storage:** AWS S3 / MinIO
- **PDF Generation:** DomPDF / Snappy (wkhtmltopdf)

---

## ARQUITECTURA DEL SISTEMA

### Arquitectura en Capas

```
┌─────────────────────────────────────────────────────────┐
│                  CAPA DE PRESENTACIÓN                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Web Desktop  │  │  Web Mobile  │  │     PWA      │  │
│  │   Vue.js 3   │  │   Vue.js 3   │  │ Preventistas │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          ↕ Inertia.js
┌─────────────────────────────────────────────────────────┐
│                   CAPA DE APLICACIÓN                     │
│  ┌──────────────────────────────────────────────────┐   │
│  │              Laravel 11 (Backend)                │   │
│  │  ┌────────────┐  ┌────────────┐  ┌───────────┐  │   │
│  │  │Controllers │  │  Services  │  │   Jobs    │  │   │
│  │  └────────────┘  └────────────┘  └───────────┘  │   │
│  │  ┌────────────┐  ┌────────────┐  ┌───────────┐  │   │
│  │  │  Actions   │  │ Validators │  │  Events   │  │   │
│  │  └────────────┘  └────────────┘  └───────────┘  │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
                          ↕ Eloquent ORM
┌─────────────────────────────────────────────────────────┐
│                   CAPA DE DOMINIO                        │
│  ┌────────────┐  ┌────────────┐  ┌─────────────────┐   │
│  │  Models    │  │Repositories│  │ Business Logic  │   │
│  └────────────┘  └────────────┘  └─────────────────┘   │
└─────────────────────────────────────────────────────────┘
                          ↕ Database Layer
┌─────────────────────────────────────────────────────────┐
│                  CAPA DE PERSISTENCIA                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │    MySQL     │  │     Redis    │  │  File Store  │  │
│  │  (Principal) │  │ (Cache/Queue)│  │   (AWS S3)   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          ↕ APIs
┌─────────────────────────────────────────────────────────┐
│               SERVICIOS EXTERNOS                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Facturación  │  │   WhatsApp   │  │     ERP      │  │
│  │  Electrónica │  │   Business   │  │  Contable    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### Patrón de Diseño: Domain-Driven Design (DDD)

**Dominios principales:**
1. **Sales Domain:** Preventas, Ventas, Cotizaciones
2. **Billing Domain:** Facturas, Notas de Crédito, Documentos fiscales
3. **Receivables Domain:** Cuentas por Cobrar, Pagos, Cobranzas
4. **Inventory Domain:** Stock, Lotes, Movimientos
5. **Customer Domain:** Clientes, Contactos, Límites de crédito
6. **Product Domain:** Catálogo, Precios, Presentaciones
7. **User Domain:** Usuarios, Roles, Permisos

---

## REQUISITOS FUNCIONALES POR MÓDULO

### Módulo 1: USUARIOS Y ROLES

#### 1.1 Gestión de Usuarios

**RF-UR-001:** El sistema debe permitir crear usuarios con datos: nombre completo, email, teléfono, documento identidad, sucursal asignada, estado (activo/inactivo).

**RF-UR-002:** El sistema debe validar que el email sea único en la base de datos.

**RF-UR-003:** El sistema debe permitir asignar uno o múltiples roles a un usuario.

**RF-UR-004:** El sistema debe permitir cambiar contraseña con validación de complejidad:
- Mínimo 8 caracteres
- Al menos 1 mayúscula
- Al menos 1 número
- Al menos 1 carácter especial

**RF-UR-005:** El sistema debe permitir desactivar usuarios (soft delete) manteniendo su historial.

**RF-UR-006:** El sistema debe forzar cambio de contraseña en el primer inicio de sesión.

**RF-UR-007:** El sistema debe bloquear usuarios después de 5 intentos fallidos de login.

#### 1.2 Gestión de Roles

**RF-UR-008:** El sistema debe incluir los siguientes roles predefinidos:
- SuperAdmin
- Administrador
- Vendedor (Preventas)
- Vendedor (Ventas)
- Cobrador
- Bodeguero
- Contabilidad
- Auditor

**RF-UR-009:** El sistema debe permitir crear roles personalizados.

**RF-UR-010:** El sistema debe gestionar permisos granulares por acción:
- Ver (index)
- Crear (create)
- Editar (update)
- Eliminar (delete)
- Aprobar (approve)
- Anular (void)
- Exportar (export)
- Ver reportes (reports)

**RF-UR-011:** El sistema debe validar que un SuperAdmin no pueda ser eliminado si es el único en el sistema.

#### 1.3 Matriz de Permisos por Rol

| Módulo/Acción | SuperAdmin | Admin | Preventista | Vendedor | Cobrador | Bodeguero | Contabilidad | Auditor |
|---------------|-----------|-------|-------------|----------|----------|-----------|--------------|---------|
| **Usuarios** |
| Ver | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Crear | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Editar | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Eliminar | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Clientes** |
| Ver | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Crear | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Editar | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | ❌ |
| **Preventas** |
| Ver | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Crear | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Aprobar | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ventas** |
| Ver | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Crear | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Facturar | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Anular | ✅ | ✅ | ❌ | ⚠️ | ❌ | ❌ | ✅ | ❌ |
| **Cuentas x Cobrar** |
| Ver | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ✅ | ✅ |
| Registrar Pago | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ✅ | ❌ |
| **Inventario** |
| Ver | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| Movimientos | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Ajustes | ✅ | ✅ | ❌ | ❌ | ❌ | ⚠️ | ❌ | ❌ |

**Leyenda:**
- ✅ Permiso completo
- ⚠️ Permiso con restricciones (ej: solo propios registros)
- ❌ Sin permiso

#### 1.4 Auditoría de Usuarios

**RF-UR-012:** El sistema debe registrar historial de sesiones con:
- Fecha y hora de login/logout
- IP de acceso
- Navegador y dispositivo
- Geolocalización (si disponible)

**RF-UR-013:** El sistema debe registrar log de cambios con:
- Usuario que realizó el cambio
- Fecha y hora
- Módulo/tabla afectada
- Registro ID
- Valores anteriores (before)
- Valores nuevos (after)
- Acción (create/update/delete)

**RF-UR-014:** El sistema debe permitir consultar historial de auditoría por:
- Usuario
- Módulo
- Rango de fechas
- Tipo de acción

---

### Módulo 2: GESTIÓN DE CLIENTES

#### 2.1 Tipos de Clientes

**RF-CL-001:** El sistema debe soportar los siguientes tipos de cliente:
- Farmacia minorista
- Cadena de farmacias
- Hospital
- Clínica
- Consultorio
- Otro

**RF-CL-002:** El sistema debe permitir configurar restricciones por tipo de cliente:
- Límite de crédito por defecto
- Plazo de pago por defecto
- Descuento por defecto
- Lista de precios aplicable

#### 2.2 Datos del Cliente

**RF-CL-003:** El sistema debe almacenar datos básicos del cliente:
- Razón social (requerido)
- Nombre comercial
- RUT/NIT/RFC (según país) (requerido, único)
- Tipo de cliente
- Categoría (A, B, C según volumen/riesgo)
- Estado (activo, inactivo, bloqueado)

**RF-CL-004:** El sistema debe almacenar datos de contacto:
- Dirección fiscal completa
- Dirección de entrega (puede ser múltiple)
- Teléfono principal
- Teléfonos alternativos
- Email principal
- Emails adicionales
- Sitio web

**RF-CL-005:** El sistema debe gestionar datos comerciales:
- Lista de precios asignada
- Descuento por defecto (%)
- Condición de pago (contado, 15 días, 30 días, 60 días, 90 días)
- Límite de crédito (monto máximo)
- Plazo máximo de pago (días)
- Día de visita del preventista
- Frecuencia de visita (semanal, quincenal, mensual)
- Vendedor/Preventista asignado
- Cobrador asignado
- Zona geográfica

**RF-CL-006:** El sistema debe validar límite de crédito antes de aprobar ventas:
- Saldo actual pendiente
- Facturas vencidas
- Límite disponible = Límite total - Saldo pendiente

**RF-CL-007:** El sistema debe bloquear automáticamente clientes con:
- Facturas vencidas > 90 días
- Saldo vencido > 50% del límite
- Cheques rechazados sin regularizar

#### 2.3 Contactos y Responsables

**RF-CL-008:** El sistema debe permitir registrar múltiples contactos por cliente:
- Nombre completo
- Cargo
- Teléfono directo
- Email
- Tipo (gerente, encargado compras, encargado pagos, etc.)
- Principal (sí/no)

**RF-CL-009:** El sistema debe permitir asignar responsables de cobro:
- Contacto responsable de pagos
- Horario de atención
- Método de pago preferido
- Observaciones

#### 2.4 Múltiples Direcciones de Entrega

**RF-CL-010:** El sistema debe permitir registrar múltiples direcciones de entrega:
- Nombre de sucursal/punto de entrega
- Dirección completa
- Referencia/punto de referencia
- Coordenadas GPS (opcional)
- Contacto en sitio
- Teléfono
- Horario de recepción
- Instrucciones especiales
- Predeterminada (sí/no)

#### 2.5 Historial del Cliente

**RF-CL-011:** El sistema debe mostrar dashboard del cliente con:
- Resumen de compras (total histórico, últimos 12 meses, tendencia)
- Top 10 productos comprados
- Última compra (fecha, monto)
- Ticket promedio
- Frecuencia de compra
- Antigüedad como cliente
- Score de riesgo crediticio

**RF-CL-012:** El sistema debe mostrar historial de documentos:
- Cotizaciones
- Pedidos
- Facturas
- Notas de crédito
- Pagos recibidos

**RF-CL-013:** El sistema debe permitir adjuntar documentos:
- Cédula de identidad del representante legal
- Registro de comercio
- NIT/RUT
- Certificado de licencia de funcionamiento
- Referencias comerciales
- Contratos

#### 2.6 Validaciones y Reglas

**RF-CL-014:** El sistema debe validar RUT/NIT único en el sistema.

**RF-CL-015:** El sistema debe validar email único por cliente.

**RF-CL-016:** El sistema debe permitir búsqueda de clientes por:
- Razón social
- Nombre comercial
- RUT/NIT
- Vendedor asignado
- Zona
- Estado

**RF-CL-017:** El sistema debe mostrar alertas en ficha del cliente:
- 🔴 Cliente bloqueado
- 🟡 Límite de crédito cerca del tope (>80%)
- 🟡 Facturas próximas a vencer (7 días)
- 🔴 Facturas vencidas
- 🔴 Cliente sin compras en 90 días

---

### Módulo 3: PREVENTAS

#### 3.1 Funcionalidad Móvil / Field Sales

**RF-PV-001:** El sistema debe proporcionar una interfaz móvil optimizada (PWA) para preventistas.

**RF-PV-002:** El sistema debe permitir operación offline:
- Descargar catálogo de productos
- Descargar lista de clientes asignados
- Crear pedidos offline
- Sincronizar al recuperar conexión

**RF-PV-003:** El sistema debe capturar geolocalización al crear pedido:
- Coordenadas GPS del punto de venta
- Fecha/hora de visita
- Validar proximidad al cliente (radio máximo 500m)

**RF-PV-004:** El sistema debe permitir tomar fotos de evidencia:
- Foto del punto de venta
- Foto de productos en exhibición
- Foto de firma del cliente (opcional)

#### 3.2 Creación de Pedidos (Preventas)

**RF-PV-005:** El sistema debe permitir crear pedido con:
- Cliente (búsqueda por nombre o RUT)
- Dirección de entrega (seleccionar de lista)
- Fecha solicitada de entrega
- Observaciones generales

**RF-PV-006:** El sistema debe mostrar información del cliente al seleccionarlo:
- Límite de crédito y disponible
- Saldo pendiente
- Facturas vencidas (alerta)
- Lista de precios asignada
- Descuento por defecto

**RF-PV-007:** El sistema debe permitir agregar productos con:
- Búsqueda por código, nombre o principio activo
- Imagen del producto
- Presentación y concentración
- Stock disponible en sucursal más cercana
- Precio según lista del cliente
- Descuento aplicable
- Cantidad solicitada
- Subtotal

**RF-PV-008:** El sistema debe validar stock en tiempo real:
- Consultar disponibilidad por sucursal
- Mostrar stock disponible
- Alertar si stock insuficiente
- Permitir reserva temporal (15 minutos)

**RF-PV-009:** El sistema debe calcular automáticamente:
- Subtotal por línea
- Descuentos por línea
- Descuento global (si aplica)
- IVA / Impuestos
- Total del pedido

**RF-PV-010:** El sistema debe validar límite de crédito:
- Total pedido + saldo pendiente ≤ límite de crédito
- Mostrar alerta si excede límite
- Permitir override con autorización de supervisor

#### 3.3 Reserva de Stock

**RF-PV-011:** El sistema debe reservar stock automáticamente al crear pedido:
- Estado: Reservado
- Tiempo de reserva: configurable (default 24 horas)
- Liberar automáticamente si no se aprueba/convierte

**RF-PV-012:** El sistema debe permitir cambiar sucursal de despacho:
- Si sucursal original sin stock
- Transferir reserva a otra sucursal

#### 3.4 Descuentos y Ofertas

**RF-PV-013:** El sistema debe aplicar descuentos según reglas:
- Descuento por cliente (porcentaje general)
- Descuento por producto (promoción)
- Descuento por volumen (escalas)
- Descuento por combo (paquetes)

**RF-PV-014:** El sistema debe validar vigencia de ofertas:
- Fecha inicio y fin
- Productos aplicables
- Clientes aplicables
- Stock mínimo para promoción

**RF-PV-015:** El sistema debe mostrar sugerencias de venta cruzada:
- Productos complementarios
- Productos frecuentemente comprados juntos
- Ofertas vigentes aplicables

#### 3.5 Workflow de Aprobación

**RF-PV-016:** El sistema debe gestionar estados del pedido (preventa):

```
Borrador → En Revisión → Aprobado → Convertido a Venta
    ↓           ↓           ↓
Cancelado   Rechazado   Vencido
```

**RF-PV-017:** El sistema debe enviar notificación cuando:
- Pedido creado (a supervisor de ventas)
- Pedido requiere aprobación (excede límite)
- Pedido aprobado (a preventista y cliente)
- Pedido rechazado (a preventista con motivo)

**RF-PV-018:** El sistema debe requerir aprobación si:
- Monto > umbral configurable
- Cliente excede límite de crédito
- Cliente con facturas vencidas
- Descuento > porcentaje permitido
- Cliente nuevo

**RF-PV-019:** El sistema debe permitir conversión a:
- Cotización (sin reserva de stock)
- Pedido de venta (con reserva confirmada)
- Factura directa (si es pago contado)

#### 3.6 Reportes de Preventas

**RF-PV-020:** El sistema debe generar reportes de:
- Pedidos por preventista (día/semana/mes)
- Cumplimiento de visitas programadas
- Efectividad de conversión (preventas → ventas)
- Productos más solicitados
- Clientes atendidos vs. cartera asignada
- Tiempo promedio de aprobación

---

### Módulo 4: VENTAS

#### 4.1 Tipos de Documentos

**RF-VT-001:** El sistema debe gestionar los siguientes tipos de documento:

1. **Cotización**
   - No afecta stock
   - No afecta cuentas
   - Validez configurable (15-30 días)
   - Convertible a pedido

2. **Pedido**
   - Reserva stock
   - No afecta cuentas
   - Pendiente de facturación
   - Genera remito/guía

3. **Remito/Guía de Despacho**
   - Documento de entrega
   - No afecta cuentas
   - Referencia a factura
   - Control de recepción

4. **Factura**
   - Descuenta stock (definitivo)
   - Crea cuenta por cobrar
   - Documento fiscal
   - Correlativo legal

5. **Nota de Crédito**
   - Revierte factura (total/parcial)
   - Devuelve stock (si aplica)
   - Documento fiscal
   - Reduce cuenta por cobrar

#### 4.2 Numeración de Documentos

**RF-VT-002:** El sistema debe generar numeración correlativa por:
- Tipo de documento
- Sucursal
- Año fiscal (opcional reseteo anual)

**RF-VT-003:** El sistema debe validar secuencia sin saltos.

**RF-VT-004:** El sistema debe permitir configurar prefijos:
- COT-001-00001234 (Cotización Sucursal 001)
- PED-001-00001234 (Pedido)
- FAC-001-00001234 (Factura)
- NC-001-00001234 (Nota de Crédito)

#### 4.3 Creación de Documentos de Venta

**RF-VT-005:** El sistema debe permitir crear documento desde:
- Preventa aprobada (conversión automática)
- Creación manual nueva
- Copia de documento anterior
- Importación desde Excel (múltiples líneas)

**RF-VT-006:** El sistema debe capturar datos del documento:
- Fecha de emisión (auto: hoy)
- Fecha de vencimiento (según condición de pago)
- Cliente (obligatorio)
- Dirección de facturación
- Dirección de entrega
- Vendedor responsable
- Condición de pago
- Forma de pago (contado, crédito, cheque, transferencia)
- Moneda (si multi-moneda)
- Tipo de cambio (si aplica)
- Observaciones
- Referencias internas

**RF-VT-007:** El sistema debe permitir agregar líneas de detalle:
- Producto (código/nombre)
- Presentación
- Lote (si aplica control de lotes)
- Fecha vencimiento (si producto controlado)
- Cantidad
- Unidad de medida
- Precio unitario
- Descuento línea (%)
- IVA (sí/no, porcentaje)
- Subtotal

**RF-VT-008:** El sistema debe calcular totales:
- Subtotal (suma líneas sin impuestos)
- Descuento global (% o monto fijo)
- Base imponible
- IVA (según tasas configuradas)
- Total general
- Total en texto (para factura impresa)

#### 4.4 Gestión de Precios

**RF-VT-009:** El sistema debe obtener precio según prioridad:
1. Precio promocional vigente
2. Precio de lista asignada al cliente
3. Precio de lista general
4. Último precio vendido al cliente

**RF-VT-010:** El sistema debe permitir override de precio:
- Con autorización (según límite de descuento)
- Registrar motivo del ajuste
- Auditar cambios de precio

**RF-VT-011:** El sistema debe validar margen mínimo:
- Costo + margen mínimo % ≤ precio venta
- Alertar si venta bajo costo
- Requerir aprobación para pérdida

**RF-VT-012:** El sistema debe mostrar historial de precios del producto:
- Últimas 5 ventas al cliente
- Precio promedio últimos 30 días
- Tendencia de precios

#### 4.5 Impuestos (IVA y otros)

**RF-VT-013:** El sistema debe soportar configuración de impuestos:
- IVA general (ej: 13%, 16%, 18% según país)
- IVA reducido (productos exentos o tasa reducida)
- Otros impuestos (IEPS, ICE, etc.)
- Productos exentos

**RF-VT-014:** El sistema debe calcular impuestos por línea:
- Permitir productos con diferentes tasas en mismo documento
- Totalizar por tipo de impuesto

**RF-VT-015:** El sistema debe generar reporte de impuestos:
- Base imponible
- IVA cobrado
- Ventas exentas
- Período fiscal

#### 4.6 Integración con Stock

**RF-VT-016:** El sistema debe descontar stock al:
- Aprobar pedido (reserva temporal)
- Generar remito (picking)
- Facturar (descuento definitivo - según configuración)

**RF-VT-017:** El sistema debe validar disponibilidad antes de facturar:
- Stock físico disponible
- Stock no comprometido en otros pedidos
- Control por lote y vencimiento (FEFO)

**RF-VT-018:** El sistema debe permitir asignación de lotes:
- Automática (FEFO - First Expire First Out)
- Manual (selección por usuario)

**RF-VT-019:** El sistema debe bloquear facturación si:
- Stock insuficiente
- Lote vencido
- Producto sin stock en sucursal

#### 4.7 Devoluciones y Notas de Crédito

**RF-VT-020:** El sistema debe permitir crear Nota de Crédito por:
- Devolución de mercadería (total o parcial)
- Error en facturación
- Descuento posterior
- Anulación de factura

**RF-VT-021:** El sistema debe validar NC:
- Monto NC ≤ Monto factura original
- Factura no anulada previamente
- Productos devueltos coinciden con facturados

**RF-VT-022:** El sistema debe gestionar devolución física:
- Crear movimiento de entrada a inventario
- Seleccionar estado de producto (bueno, dañado, vencido)
- Si dañado/vencido → no retornar a stock vendible
- Adjuntar motivo de devolución

**RF-VT-023:** El sistema debe afectar cuentas por cobrar:
- Restar NC del saldo pendiente
- Permitir aplicar NC a futura compra (crédito a favor)

#### 4.8 Impresión y Exportación

**RF-VT-024:** El sistema debe generar documentos PDF:
- Formato estándar (carta/A4)
- Logo de empresa
- Datos fiscales
- QR de factura electrónica (si aplica)
- Condiciones de pago y garantía

**RF-VT-025:** El sistema debe permitir reimprimir documentos:
- Con marca de agua "COPIA" o "REIMPRESIÓN"
- Auditar reimpresiones

**RF-VT-026:** El sistema debe exportar a:
- Excel (lista de documentos)
- XML (factura electrónica)
- JSON (integración API)

#### 4.9 Estados del Documento

**RF-VT-027:** El sistema debe gestionar workflow de estados:

**Cotización:**
```
Borrador → Enviada → Aceptada/Rechazada → Vencida
```

**Pedido:**
```
Borrador → Confirmado → En Preparación → Despachado → Entregado
    ↓
Cancelado
```

**Factura:**
```
Borrador → Emitida → Pagada Parcial → Pagada Total
    ↓
Anulada (con NC)
```

**RF-VT-028:** El sistema debe permitir anulación solo si:
- Usuario con permiso
- Documento no tiene pagos aplicados (o tiene NC total)
- Dentro de plazo legal (configurable)

---

### Módulo 5: PRODUCTOS Y CATÁLOGO

#### 5.1 Estructura de Productos Farmacéuticos

**RF-PR-001:** El sistema debe gestionar productos con:
- Código interno (único)
- Código de barras (EAN-13, UPC)
- Nombre genérico
- Nombre comercial
- Principio activo (DCI - Denominación Común Internacional)
- Concentración (ej: 500mg, 10mg/ml)
- Presentación (tableta, cápsula, jarabe, inyectable, crema, etc.)
- Forma farmacéutica
- Vía de administración (oral, tópica, inyectable, etc.)
- Laboratorio fabricante
- Registro sanitario
- Categoría terapéutica
- Clasificación ATC (Anatomical Therapeutic Chemical)

**RF-PR-002:** El sistema debe validar código único:
- Código interno no repetido
- Código de barras único (si se usa)

**RF-PR-003:** El sistema debe permitir búsqueda por:
- Código
- Nombre comercial
- Nombre genérico
- Principio activo
- Laboratorio
- Categoría

#### 5.2 Múltiples Presentaciones y Equivalencias

**RF-PR-004:** El sistema debe gestionar múltiples presentaciones del mismo producto:
- Ejemplo: Paracetamol 500mg:
  - Tableta blister x 10 unidades
  - Tableta caja x 100 unidades
  - Tableta frasco x 500 unidades

**RF-PR-005:** El sistema debe gestionar equivalencias:
- Unidad base (ej: tableta)
- Factor de conversión (1 caja = 100 tabletas)
- Permitir ventas en diferentes unidades

**RF-PR-006:** El sistema debe gestionar productos bioequivalentes:
- Listar genéricos intercambiables
- Sugerir alternativas en caso de falta de stock
- Mismo principio activo y concentración

#### 5.3 Control de Lotes y Vencimientos

**RF-PR-007:** El sistema debe permitir configurar si producto requiere control de:
- Lote: Sí/No
- Fecha de vencimiento: Sí/No
- Número de serie: Sí/No (para equipos médicos)

**RF-PR-008:** El sistema debe registrar por cada lote:
- Número de lote
- Fecha de fabricación
- Fecha de vencimiento
- Cantidad recibida
- Cantidad disponible
- Ubicación en almacén
- Proveedor

**RF-PR-009:** El sistema debe validar vencimientos:
- Alertar productos próximos a vencer (30, 60, 90 días)
- Bloquear venta de productos vencidos
- Aplicar FEFO en despacho (First Expire First Out)

**RF-PR-010:** El sistema debe generar reportes de:
- Productos por vencer
- Productos vencidos (para baja)
- Rotación por lote

#### 5.4 Políticas por Producto

**RF-PR-011:** El sistema debe permitir configurar:
- Venta por unidad suelta: Sí/No
- Venta solo por caja completa: Sí/No
- Cantidad mínima de venta
- Cantidad máxima por pedido
- Permite descuento: Sí/No
- Descuento máximo permitido (%)
- Requiere receta médica: Sí/No
- Producto controlado (psicotrópico): Sí/No

**RF-PR-012:** El sistema debe validar política al vender:
- Bloquear venta si cantidad < mínima
- Alertar si cantidad > máxima
- Requerir carga de receta médica (imagen)

#### 5.5 Gestión de Precios

**RF-PR-013:** El sistema debe gestionar múltiples listas de precios:
- Lista General
- Lista Mayorista
- Lista Cadenas
- Lista Hospitales
- Lista Promocional
- Listas personalizadas

**RF-PR-014:** El sistema debe registrar precio por:
- Lista de precios
- Presentación del producto
- Moneda (si multi-moneda)
- Vigencia (fecha inicio - fecha fin)

**RF-PR-015:** El sistema debe mantener historial de precios:
- Precio anterior
- Precio actual
- Fecha de cambio
- Usuario que modificó
- Motivo del cambio (opcional)

**RF-PR-016:** El sistema debe permitir actualización masiva de precios:
- Por categoría
- Por laboratorio
- Por lista de precios
- Incremento porcentual o monto fijo
- Vista previa antes de aplicar

**RF-PR-017:** El sistema debe calcular precio sugerido:
- Costo + margen % = Precio venta
- Mostrar margen de ganancia
- Comparar con competencia (manual)

#### 5.6 Imágenes y Documentos

**RF-PR-018:** El sistema debe permitir adjuntar:
- Imagen principal del producto
- Imágenes adicionales (hasta 5)
- Ficha técnica (PDF)
- Registro sanitario (imagen/PDF)
- Prospecto (PDF)
- Certificados de calidad

**RF-PR-019:** El sistema debe mostrar vista previa en:
- Catálogo web
- Punto de venta móvil
- Impresión de cotizaciones

#### 5.7 Categorización y Clasificación

**RF-PR-020:** El sistema debe organizar productos por:
- Categoría terapéutica (Analgésicos, Antibióticos, Cardiovasculares, etc.)
- Línea de negocio (Éticos, Genéricos, OTC, Cosméticos)
- Familia de productos
- Marca/Laboratorio
- Estado (activo, descontinuado, temporal)

**RF-PR-021:** El sistema debe permitir filtrado y búsqueda avanzada por:
- Múltiples criterios combinados
- Precio rango
- Stock disponible
- Categoría + laboratorio

#### 5.8 Stock Mínimo y Reorden

**RF-PR-022:** El sistema debe gestionar por producto y sucursal:
- Stock mínimo
- Stock máximo
- Punto de reorden
- Cantidad de reorden

**RF-PR-023:** El sistema debe generar alertas cuando:
- Stock < Stock mínimo
- Stock ≤ Punto de reorden
- Generar sugerencia de compra

---

### Módulo 6: CUENTAS POR COBRAR

#### 6.1 Gestión de Facturas Pendientes

**RF-CC-001:** El sistema debe registrar automáticamente cuenta por cobrar al:
- Emitir factura a crédito
- Generar nota de débito

**RF-CC-002:** El sistema debe calcular:
- Fecha de vencimiento = Fecha factura + Plazo (días)
- Días vencidos = Hoy - Fecha vencimiento (si < 0)
- Estado: Vigente / Vencido / Crítico (>90 días)

**RF-CC-003:** El sistema debe mostrar dashboard de CxC:
- Saldo total por cobrar
- Facturas vigentes (no vencidas)
- Facturas vencidas (1-30 días)
- Facturas vencidas (31-60 días)
- Facturas vencidas (61-90 días)
- Facturas vencidas (>90 días)
- Gráfico de antigüedad de saldos (Aging Report)

**RF-CC-004:** El sistema debe permitir filtrar facturas por:
- Cliente
- Vendedor
- Cobrador asignado
- Rango de fechas
- Estado (vigente/vencido)
- Rango de monto
- Sucursal

#### 6.2 Registro de Pagos (Recibos de Caja)

**RF-CC-005:** El sistema debe permitir registrar pago con:
- Cliente
- Fecha de pago
- Forma de pago (efectivo, cheque, transferencia, tarjeta)
- Monto recibido
- Referencia (número de cheque, transferencia, etc.)
- Banco (si aplica)
- Observaciones

**RF-CC-006:** El sistema debe validar forma de pago:

**Efectivo:**
- Monto recibido
- Cambio (si aplica)

**Cheque:**
- Número de cheque
- Banco emisor
- Fecha de cheque
- Fecha de depósito (diferido)
- Estado (recibido, depositado, cobrado, rechazado)

**Transferencia:**
- Número de operación
- Banco origen
- Banco destino
- Fecha de transferencia
- Comprobante (adjuntar imagen)

**Tarjeta:**
- Número de autorización
- Últimos 4 dígitos
- Tipo de tarjeta
- Comisión (%)

**RF-CC-007:** El sistema debe permitir pago parcial o total:
- Asignar monto a una o múltiples facturas
- Permitir pago mayor (anticipo)
- Permitir pago a cuenta (sin asignación específica)

#### 6.3 Aplicación de Pagos a Facturas

**RF-CC-008:** El sistema debe permitir aplicar pago a facturas:
- Seleccionar factura(s) pendiente(s)
- Asignar monto total o parcial
- Calcular saldo pendiente por factura
- Permitir distribución automática (facturas más antiguas primero)

**RF-CC-009:** El sistema debe registrar asignación:
- Factura ID
- Monto aplicado
- Saldo anterior
- Saldo nuevo
- Fecha de aplicación

**RF-CC-010:** El sistema debe actualizar estado de factura:
- Pendiente → Pago Parcial (si monto aplicado < total)
- Pago Parcial → Pagada (si saldo = 0)

**RF-CC-011:** El sistema debe permitir reversar aplicación de pago:
- Con autorización
- Registrar motivo
- Restaurar saldo anterior
- Auditar cambio

#### 6.4 Anticipos y Crédito a Favor

**RF-CC-012:** El sistema debe gestionar anticipos:
- Cliente paga sin factura asignada
- Registrar como "crédito a favor"
- Aplicar a futuras facturas
- Permitir devolución (nota de crédito)

**RF-CC-013:** El sistema debe mostrar saldo a favor:
- En ficha del cliente
- Disponible para aplicar en nueva venta
- Permitir aplicación manual

#### 6.5 Notas de Crédito y Ajustes

**RF-CC-014:** El sistema debe aplicar NC automáticamente:
- Reducir saldo de factura original
- Permitir aplicar a otras facturas del cliente
- Generar crédito a favor si no hay facturas pendientes

**RF-CC-015:** El sistema debe permitir ajustes manuales:
- Ajustes por diferencias de cambio
- Ajustes por redondeo
- Condonación de saldo (con autorización)
- Castigo de cartera incobrable
- Requiere motivo y aprobación
- Auditar todos los ajustes

#### 6.6 Conciliación de Pagos

**RF-CC-016:** El sistema debe permitir conciliación:
- Listar pagos registrados vs. extracto bancario
- Marcar como conciliado
- Identificar diferencias
- Generar reporte de conciliación

**RF-CC-017:** El sistema debe gestionar cheques rechazados:
- Cambiar estado a "Rechazado"
- Reversar aplicación de pago
- Restaurar saldo de factura
- Notificar a cobrador
- Agregar comisión por rechazo (si aplica)

#### 6.7 Alertas y Notificaciones

**RF-CC-018:** El sistema debe generar alertas por:
- Facturas próximas a vencer (7 días antes)
- Facturas vencidas (diaria)
- Cliente excede límite de crédito
- Cliente con cheques rechazados
- Saldo > 90 días sin regularizar

**RF-CC-019:** El sistema debe enviar notificaciones por:
- Email automático al cliente (facturas vencidas)
- WhatsApp (recordatorio de pago)
- Dashboard del cobrador
- Reporte semanal a gerencia

#### 6.8 Reportes de Cobranza

**RF-CC-020:** El sistema debe generar reportes de:
- Aging Report (antigüedad de saldos) por cliente
- Flujo de caja proyectado (forecast)
- Cobranza por cobrador
- Efectividad de cobranza (% recuperado)
- Clientes morosos (Top 10)
- Estado de cuenta por cliente
- Resumen de pagos recibidos (por día, semana, mes)
- Cheques en cartera (pendientes de cobro)
- Cheques diferidos (por fecha de vencimiento)

**RF-CC-021:** El sistema debe exportar estado de cuenta:
- PDF con logo empresa
- Detalle de facturas pendientes
- Detalle de pagos recibidos
- Saldo actual
- Envío por email al cliente

---

### Módulo 7: REPORTES Y ESTADÍSTICAS

#### 7.1 Reportes de Ventas

**RF-RP-001:** El sistema debe generar reportes de ventas por:
- Cliente (individual o ranking)
- Vendedor / Preventista
- Producto (individual o ranking)
- Categoría de producto
- Sucursal
- Zona geográfica
- Período (día, semana, mes, trimestre, año, rango personalizado)

**RF-RP-002:** El sistema debe mostrar KPIs de ventas:
- Total facturado (monto)
- Total unidades vendidas
- Ticket promedio (facturación / # facturas)
- Cantidad de transacciones
- Tasa de conversión (cotizaciones → ventas)
- Variación vs. período anterior (%)
- Comparativa año actual vs. año anterior

**RF-RP-003:** El sistema debe generar Top productos:
- Top 10/20/50 por facturación
- Top por unidades vendidas
- Top por margen de ganancia
- Productos de menor rotación

**RF-RP-004:** El sistema debe generar ranking de clientes:
- Top clientes por facturación
- Nuevos clientes en el período
- Clientes perdidos (sin compras en X meses)
- Clientes por frecuencia de compra

**RF-RP-005:** El sistema debe generar reporte de vendedores:
- Facturación por vendedor
- Cumplimiento de cuota (%)
- Cantidad de clientes atendidos
- Ticket promedio por vendedor
- Comparativa entre vendedores

#### 7.2 Reportes de Inventario

**RF-RP-006:** El sistema debe generar reportes de stock:
- Stock actual por producto
- Stock por sucursal
- Stock valorizado (cantidad × costo)
- Stock por categoría
- Stock con rotación lenta
- Stock con rotación alta

**RF-RP-007:** El sistema debe calcular rotación de inventario:
- Índice de rotación = Costo de ventas / Stock promedio
- Días de inventario = 365 / Índice rotación
- Clasificación ABC:
  - A: 20% productos con 80% facturación (alta rotación)
  - B: 30% productos con 15% facturación (media rotación)
  - C: 50% productos con 5% facturación (baja rotación)

**RF-RP-008:** El sistema debe generar alertas de stock:
- Productos bajo stock mínimo
- Productos sin stock (quiebres)
- Productos sobre stock máximo
- Productos próximos a vencer
- Productos sin movimiento (obsoletos)

**RF-RP-009:** El sistema debe generar reporte de mermas:
- Productos vencidos dados de baja
- Productos dañados
- Diferencias de inventario (faltantes)
- Costo de mermas por período

#### 7.3 Reportes de Cuentas por Cobrar

**RF-RP-010:** El sistema debe generar Aging Report:
- Saldos por rango de días vencidos:
  - 0-30 días
  - 31-60 días
  - 61-90 días
  - Más de 90 días
- Por cliente
- Por vendedor
- Por sucursal

**RF-RP-011:** El sistema debe generar reporte de cobranza:
- Facturas emitidas en el período
- Pagos recibidos en el período
- Saldo pendiente al cierre
- Efectividad de cobro (%)
- Días promedio de cobro (DSO - Days Sales Outstanding)

**RF-RP-012:** El sistema debe proyectar flujo de caja:
- Facturas a vencer en próximos 7, 15, 30 días
- Anticipos recibidos
- Pagos comprometidos
- Flujo neto proyectado

#### 7.4 Reportes de Rentabilidad

**RF-RP-013:** El sistema debe calcular rentabilidad:
- Por producto: (Precio venta - Costo) / Precio venta × 100
- Por cliente: Facturación - Costo ventas
- Por vendedor: Margen generado
- Por categoría: Margen promedio

**RF-RP-014:** El sistema debe generar reporte de descuentos:
- Total descuentos otorgados (monto)
- Descuentos por tipo (cliente, promoción, volumen)
- Descuentos por vendedor
- Impacto en margen (%)

**RF-RP-015:** El sistema debe comparar:
- Presupuesto vs. Real (si hay presupuesto cargado)
- Año actual vs. Año anterior
- Mes actual vs. Mes anterior
- Sucursal A vs. Sucursal B

#### 7.5 Reportes de Preventas

**RF-RP-016:** El sistema debe generar reportes de preventas:
- Pedidos por preventista (cantidad y monto)
- Tasa de aprobación (aprobados / creados)
- Tasa de conversión (ventas / preventas)
- Tiempo promedio de aprobación
- Cumplimiento de rutas (visitas programadas vs. realizadas)
- Efectividad por zona

**RF-RP-017:** El sistema debe generar reporte de visitas:
- Clientes visitados vs. cartera asignada
- Frecuencia de visita por cliente
- Pedido promedio por visita
- Cobertura de cartera (% clientes activos)

#### 7.6 Dashboards Ejecutivos

**RF-RP-018:** El sistema debe incluir dashboard para Gerencia con:
- Facturación del mes (vs. mes anterior, vs. presupuesto)
- Gráfico de tendencia de ventas (12 meses)
- Top 5 clientes
- Top 5 productos
- Saldo cuentas por cobrar
- Facturas vencidas (monto y cantidad)
- Stock crítico (productos bajo mínimo)
- Productos próximos a vencer
- Alertas importantes

**RF-RP-019:** El sistema debe incluir dashboard para Ventas con:
- Facturación del día/semana/mes
- Pedidos pendientes de facturar
- Cotizaciones pendientes
- Preventas por aprobar
- Ranking de vendedores
- Cumplimiento de cuotas

**RF-RP-020:** El sistema debe incluir dashboard para Cobranza con:
- Saldo total por cobrar
- Facturas vencidas (hoy)
- Pagos recibidos (hoy)
- Cheques por depositar
- Clientes con alertas de crédito
- Top 10 clientes morosos

#### 7.7 Filtros y Exportación

**RF-RP-021:** El sistema debe permitir filtros avanzados en todos los reportes:
- Rango de fechas personalizado
- Múltiples sucursales
- Múltiples vendedores
- Múltiples clientes
- Categorías de productos
- Estado de documentos
- Guardado de filtros favoritos

**RF-RP-022:** El sistema debe permitir exportación a:
- Excel (.xlsx) con formato
- PDF para impresión
- CSV para análisis externo
- JSON para API

**RF-RP-023:** El sistema debe permitir programar envío de reportes:
- Frecuencia (diaria, semanal, mensual)
- Destinatarios (emails)
- Formato (PDF o Excel)
- Filtros predefinidos

---

### Módulo 8: CONFIGURACIÓN

#### 8.1 Datos de la Empresa

**RF-CF-001:** El sistema debe permitir configurar:
- Razón social
- Nombre comercial
- RUT/NIT
- Dirección fiscal
- Teléfono(s)
- Email corporativo
- Sitio web
- Logo (para documentos e interfaz)
- Firma digital (si aplica facturación electrónica)

#### 8.2 Sucursales y Almacenes

**RF-CF-002:** El sistema debe gestionar sucursales con:
- Código de sucursal
- Nombre
- Dirección completa
- Teléfono
- Responsable
- Tipo (matriz, sucursal, bodega)
- Estado (activa, inactiva)

**RF-CF-003:** El sistema debe gestionar almacenes por sucursal:
- Código de almacén
- Nombre
- Ubicación física
- Responsable (bodeguero)
- Tipo (venta, tránsito, cuarentena, dañados)

**RF-CF-004:** El sistema debe permitir transferencias entre sucursales.

#### 8.3 Listas de Precios

**RF-CF-005:** El sistema debe permitir crear múltiples listas:
- Código de lista
- Nombre (General, Mayorista, Cadenas, etc.)
- Descripción
- Vigencia (fecha inicio - fecha fin)
- Estado (activa, inactiva)
- Aplicable a tipos de cliente

**RF-CF-006:** El sistema debe permitir duplicar lista de precios existente.

#### 8.4 Condiciones de Pago

**RF-CF-007:** El sistema debe gestionar condiciones de pago:
- Código
- Descripción (Contado, 15 días, 30 días, etc.)
- Plazo en días
- Requiere aprobación (sí/no)

**RF-CF-008:** El sistema debe permitir descuentos por pronto pago:
- % descuento si paga antes de X días

#### 8.5 Parámetros Fiscales

**RF-CF-009:** El sistema debe configurar tasas de impuestos:
- IVA general (%)
- IVA reducido (%) (si aplica)
- Otros impuestos
- Productos exentos (lista)

**RF-CF-010:** El sistema debe configurar numeración de documentos:
- Prefijo por sucursal
- Número inicial
- Número actual (autoincrementable)
- Rango autorizado (inicio - fin) para facturas electrónicas

#### 8.6 Formatos de Impresión

**RF-CF-011:** El sistema debe permitir personalizar plantillas de:
- Facturas
- Remitos/Guías
- Cotizaciones
- Recibos de pago
- Estado de cuenta

**RF-CF-012:** El sistema debe permitir configurar:
- Tamaño de papel (Carta, A4, Ticket 80mm)
- Orientación (vertical, horizontal)
- Márgenes
- Campos a mostrar/ocultar
- Textos legales en pie de página

#### 8.7 Parámetros del Sistema

**RF-CF-013:** El sistema debe permitir configurar:
- Moneda principal
- Monedas secundarias (si aplica multi-moneda)
- Formato de fecha (dd/mm/yyyy, mm/dd/yyyy)
- Formato de número (separador decimal y miles)
- Idioma (Español, Inglés, Portugués)
- Zona horaria

**RF-CF-014:** El sistema debe permitir configurar límites:
- Límite de crédito por defecto
- Descuento máximo sin autorización (%)
- Días de reserva de stock en preventas
- Días de vencimiento de cotizaciones
- Días de alerta de productos por vencer

**RF-CF-015:** El sistema debe permitir configurar notificaciones:
- Email servidor SMTP (host, puerto, usuario, password)
- Plantillas de emails (bienvenida, factura, recordatorio de pago)
- WhatsApp API (token, número, plantillas)

#### 8.8 Integraciones

**RF-CF-016:** El sistema debe permitir configurar integraciones con:
- **Facturación electrónica:**
  - API Key
  - Endpoint
  - Certificados digitales
  - Ambiente (pruebas, producción)

- **ERP Contable:**
  - URL de conexión
  - Credenciales
  - Mapeo de cuentas contables

- **Pasarelas de pago:**
  - Merchant ID
  - API Key
  - Comisiones

**RF-CF-017:** El sistema debe permitir habilitar/deshabilitar módulos:
- Preventas (sí/no)
- Control de lotes (sí/no)
- Multi-moneda (sí/no)
- Geolocalización en preventas (sí/no)
- Facturación electrónica (sí/no)

---

### Módulo 9: AUDITORÍA

#### 9.1 Log de Acciones

**RF-AU-001:** El sistema debe registrar todas las acciones CRUD:
- Usuario que ejecutó la acción
- Fecha y hora (timestamp)
- IP de origen
- Módulo / Tabla afectada
- ID del registro
- Acción (create, read, update, delete)
- Valores anteriores (before)
- Valores nuevos (after)
- Navegador / User Agent

**RF-AU-002:** El sistema debe auditar operaciones críticas:
- Login/Logout
- Cambios de contraseña
- Cambios en límites de crédito
- Aprobación/rechazo de documentos
- Anulación de facturas
- Modificación de precios
- Ajustes de inventario
- Registro de pagos
- Creación/edición de usuarios

**RF-AU-003:** El sistema debe permitir consultar logs por:
- Usuario
- Módulo/Tabla
- Rango de fechas
- Acción
- Registro específico (ID)

#### 9.2 Registro de Cambios Críticos

**RF-AU-004:** El sistema debe auditar cambios en:
- **Precios:**
  - Producto
  - Precio anterior
  - Precio nuevo
  - % cambio
  - Usuario
  - Motivo

- **Límites de crédito:**
  - Cliente
  - Límite anterior
  - Límite nuevo
  - Usuario autorizador
  - Motivo

- **Documentos financieros:**
  - Tipo de documento
  - Número
  - Estado anterior
  - Estado nuevo
  - Monto
  - Usuario
  - Motivo (si es anulación)

**RF-AU-005:** El sistema debe requerir motivo obligatorio para:
- Anulación de factura
- Ajustes de inventario
- Modificación de precio fuera de rango permitido
- Condonación de deuda
- Override de límite de crédito

#### 9.3 Reportes de Auditoría

**RF-AU-006:** El sistema debe generar reportes de:
- Actividad por usuario (sesiones, acciones)
- Cambios en tabla específica
- Timeline de cambios en un registro
- Accesos fallidos (intentos de login incorrectos)
- Acciones no autorizadas (intentos denegados)
- Modificaciones en período (para auditoría contable)

**RF-AU-007:** El sistema debe mostrar historial de cambios en:
- Ficha de cliente (quién modificó qué y cuándo)
- Ficha de producto
- Documento de venta
- Registro de pago

#### 9.4 Reversión de Cambios

**RF-AU-008:** El sistema debe permitir revertir cambios en:
- Ajustes de inventario (con autorización)
- Aplicación de pagos (desaplicar)
- Cambios de estado (con validaciones)

**RF-AU-009:** El sistema NO debe permitir revertir:
- Facturas emitidas (solo anular con NC)
- Eliminación de registros (soft delete permanente)
- Cambios en auditoría (inmutables)

#### 9.5 Seguridad y Retención

**RF-AU-010:** El sistema debe:
- Almacenar logs de forma inmutable (no editables)
- Cifrar información sensible en logs
- Retener logs por período legal (configurable, ej: 7 años)
- Permitir exportación de logs para auditoría externa
- Generar alertas ante patrones sospechosos:
  - Múltiples intentos de login fallidos
  - Acceso desde IP no habitual
  - Cambios masivos en corto tiempo
  - Acceso fuera de horario laboral

---

### Módulo 10: INVENTARIO

#### 10.1 Stock por Sucursal y Lote

**RF-IN-001:** El sistema debe gestionar stock con:
- Producto
- Sucursal/Almacén
- Lote (si aplica control)
- Fecha de vencimiento (si aplica)
- Cantidad física
- Cantidad disponible (física - reservada)
- Cantidad en tránsito
- Costo promedio ponderado
- Última entrada (fecha)
- Última salida (fecha)

**RF-IN-002:** El sistema debe calcular stock disponible:
```
Stock Disponible = Stock Físico - Stock Reservado - Stock en Cuarentena
```

**RF-IN-003:** El sistema debe soportar múltiples ubicaciones por almacén:
- Pasillo-Estante-Nivel (ej: A-05-3)
- Permitir múltiples ubicaciones por producto

#### 10.2 Movimientos de Inventario

**RF-IN-004:** El sistema debe registrar tipos de movimiento:
- **Entradas:**
  - Compra a proveedor
  - Devolución de cliente
  - Transferencia desde otra sucursal (entrada)
  - Ajuste positivo (faltante físico)
  - Producción (si aplica)

- **Salidas:**
  - Venta (factura)
  - Devolución a proveedor
  - Transferencia a otra sucursal (salida)
  - Ajuste negativo (sobrante físico)
  - Merma (vencido, dañado, pérdida)

**RF-IN-005:** El sistema debe registrar movimiento con:
- Fecha y hora
- Tipo de movimiento
- Documento origen (factura, orden de compra, etc.)
- Producto
- Lote (si aplica)
- Cantidad
- Costo unitario
- Costo total
- Usuario responsable
- Observaciones

**RF-IN-006:** El sistema debe recalcular costo promedio ponderado en cada entrada:
```
Costo Promedio Nuevo = (Stock Anterior × Costo Anterior + Cantidad Entrada × Costo Entrada) / (Stock Anterior + Cantidad Entrada)
```

#### 10.3 Transferencias entre Sucursales

**RF-IN-007:** El sistema debe gestionar transferencias con estados:
```
Solicitada → Aprobada → En Tránsito → Recibida
     ↓
Rechazada
```

**RF-IN-008:** El sistema debe crear documento de transferencia con:
- Sucursal origen
- Sucursal destino
- Fecha de solicitud
- Fecha estimada de llegada
- Usuario solicitante
- Usuario autorizador
- Lista de productos (producto, lote, cantidad)
- Motivo de transferencia

**RF-IN-009:** El sistema debe afectar stock:
- Al aprobar: Descontar de sucursal origen, marcar "en tránsito"
- Al recibir: Sumar a sucursal destino, quitar "en tránsito"

**RF-IN-010:** El sistema debe permitir recepción parcial:
- Registrar cantidad recibida vs. cantidad enviada
- Identificar diferencias (faltantes o sobrantes)
- Generar ajuste automático

#### 10.4 Ajustes de Inventario

**RF-IN-011:** El sistema debe permitir ajustes por:
- Inventario físico (conteo vs. sistema)
- Corrección de errores
- Merma por vencimiento
- Merma por daño
- Robo/pérdida
- Otro (con motivo obligatorio)

**RF-IN-012:** El sistema debe requerir para ajustes:
- Autorización (según monto/cantidad)
- Motivo obligatorio
- Foto de evidencia (opcional)
- Firma del responsable

**RF-IN-013:** El sistema debe generar reporte de diferencias de inventario:
- Producto
- Cantidad en sistema
- Cantidad física contada
- Diferencia (+ o -)
- Costo de diferencia
- Motivo

#### 10.5 Control de Picking y Preparación

**RF-IN-014:** El sistema debe generar orden de picking al:
- Aprobar pedido de venta
- Generar remito

**RF-IN-015:** El sistema debe mostrar en orden de picking:
- Lista de productos con cantidades
- Ubicación en almacén
- Lote sugerido (FEFO)
- Estado (pendiente, en proceso, completado)

**RF-IN-016:** El sistema debe permitir proceso de picking:
- Escaneo de código de barras
- Validación de producto y cantidad
- Marcado como "preparado"
- Impresión de etiquetas

**RF-IN-017:** El sistema debe validar antes de despacho:
- Todos los productos pickados
- Cantidades correctas
- Documentación completa (factura, remito)

#### 10.6 Reserva de Stock

**RF-IN-018:** El sistema debe reservar stock al:
- Crear preventa (reserva temporal)
- Confirmar pedido de venta (reserva confirmada)

**RF-IN-019:** El sistema debe gestionar tipos de reserva:
- **Temporal:** Expira en X horas (configurable)
- **Confirmada:** Hasta despacho o anulación

**RF-IN-020:** El sistema debe liberar stock reservado si:
- Expira tiempo de reserva
- Pedido cancelado
- Producto despachado (pasa a salida)

#### 10.7 Reorden Automático

**RF-IN-021:** El sistema debe calcular punto de reorden:
```
Punto de Reorden = (Consumo Promedio Diario × Tiempo de Reposición) + Stock de Seguridad
```

**RF-IN-022:** El sistema debe generar alertas cuando:
- Stock ≤ Punto de reorden
- Stock < Stock mínimo
- Stock = 0 (quiebre de stock)

**RF-IN-023:** El sistema debe sugerir cantidad de reorden:
```
Cantidad Reorden = Stock Máximo - Stock Actual
```

**RF-IN-024:** El sistema debe generar reporte de sugerencias de compra:
- Productos bajo punto de reorden
- Cantidad sugerida
- Proveedor habitual
- Costo estimado
- Exportable para enviar a compras

#### 10.8 Control de Vencimientos

**RF-IN-025:** El sistema debe aplicar método FEFO:
- First Expire First Out
- Despachar lotes con vencimiento más próximo primero
- Sugerir lote automáticamente en picking

**RF-IN-026:** El sistema debe alertar productos próximos a vencer:
- 90 días antes (alerta amarilla)
- 60 días antes (alerta naranja)
- 30 días antes (alerta roja)
- Generar reporte diario

**RF-IN-027:** El sistema debe bloquear venta de productos vencidos:
- Validar fecha vencimiento < fecha actual
- Permitir override solo para devoluciones/bajas

**RF-IN-028:** El sistema debe gestionar bajas por vencimiento:
- Registrar producto vencido
- Descontar de stock
- Registrar costo de merma
- Adjuntar acta de destrucción (si aplica normativa)

#### 10.9 Inventario Físico

**RF-IN-029:** El sistema debe permitir crear conteo de inventario:
- Tipo: Total (todos los productos) o Cíclico (muestra)
- Sucursal/Almacén
- Fecha programada
- Responsables
- Estado (planificado, en proceso, finalizado)

**RF-IN-030:** El sistema debe generar hoja de conteo:
- Lista de productos a contar
- Ubicación
- Stock según sistema
- Columnas para conteo físico
- Exportable a Excel o impresión

**RF-IN-031:** El sistema debe permitir registro de conteo:
- Por producto
- Cantidad contada
- Lote (si aplica)
- Observaciones

**RF-IN-032:** El sistema debe comparar conteo vs. sistema:
- Generar reporte de diferencias
- Calcular ajustes necesarios
- Requerir aprobación para aplicar ajustes
- Actualizar stock con ajustes aprobados

#### 10.10 Trazabilidad de Lotes

**RF-IN-033:** El sistema debe permitir rastrear lote:
- Entrada (compra a proveedor)
- Movimientos internos (transferencias)
- Salidas (ventas a clientes)
- Estado actual (stock, ubicación)

**RF-IN-034:** El sistema debe generar reporte de trazabilidad:
- Lote específico: ¿A qué clientes se vendió?
- Producto vendido: ¿De qué lote salió?
- Útil para retiros de mercado (recalls)

---

## REQUISITOS NO FUNCIONALES

### RNF-1: Rendimiento

**RNF-001:** El sistema debe responder en menos de 2 segundos para el 95% de las operaciones.

**RNF-002:** El sistema debe soportar al menos 100 usuarios concurrentes sin degradación de rendimiento.

**RNF-003:** El sistema debe cargar el dashboard principal en menos de 3 segundos.

**RNF-004:** Las consultas de reportes complejos no deben exceder 10 segundos.

**RNF-005:** El sistema debe implementar caché para datos frecuentemente consultados (listas de precios, catálogo).

### RNF-2: Escalabilidad

**RNF-006:** El sistema debe ser escalable horizontalmente (agregar servidores).

**RNF-007:** La base de datos debe soportar al menos 1 millón de registros de ventas sin pérdida de rendimiento.

**RNF-008:** El sistema debe permitir agregar nuevas sucursales sin modificación de código.

### RNF-3: Disponibilidad

**RNF-009:** El sistema debe tener disponibilidad del 99.5% (SLA).

**RNF-010:** El sistema debe tener plan de respaldo (backup) diario automático.

**RNF-011:** El sistema debe permitir recuperación ante desastres (RTO < 4 horas, RPO < 1 hora).

**RNF-012:** El sistema debe notificar a administradores en caso de caída de servicio.

### RNF-4: Seguridad

**RNF-013:** El sistema debe cifrar contraseñas usando bcrypt o Argon2.

**RNF-014:** El sistema debe implementar autenticación de dos factores (2FA) para usuarios administradores.

**RNF-015:** El sistema debe implementar JWT para sesiones API.

**RNF-016:** El sistema debe proteger contra ataques comunes (SQL Injection, XSS, CSRF).

**RNF-017:** El sistema debe implementar rate limiting en API (máximo 100 requests/minuto por IP).

**RNF-018:** El sistema debe cifrar comunicación mediante HTTPS/TLS.

**RNF-019:** El sistema debe enmascarar datos sensibles en logs (tarjetas, contraseñas).

**RNF-020:** El sistema debe cumplir con normativas de protección de datos (GDPR, LOPD si aplica).

### RNF-5: Usabilidad

**RNF-021:** El sistema debe ser responsive (adaptable a desktop, tablet, móvil).

**RNF-022:** El sistema debe soportar navegadores Chrome, Firefox, Safari, Edge (últimas 2 versiones).

**RNF-023:** La interfaz debe ser intuitiva, siguiendo principios de UX/UI.

**RNF-024:** El sistema debe proporcionar mensajes de error claros y accionables.

**RNF-025:** El sistema debe incluir ayuda contextual (tooltips, guías).

**RNF-026:** El sistema debe permitir atajos de teclado para operaciones frecuentes.

### RNF-6: Mantenibilidad

**RNF-027:** El código debe seguir estándares PSR-12 (PHP) y Vue.js Style Guide.

**RNF-028:** El código debe tener cobertura de tests unitarios mínima del 70%.

**RNF-029:** El sistema debe tener documentación técnica actualizada.

**RNF-030:** El sistema debe usar control de versiones (Git).

**RNF-031:** El sistema debe permitir despliegue mediante CI/CD.

### RNF-7: Portabilidad

**RNF-032:** El sistema debe funcionar en Linux, Windows Server y macOS.

**RNF-033:** El sistema debe ser independiente de proveedor cloud (portable entre AWS, Azure, GCP).

**RNF-034:** El sistema debe usar contenedores Docker para facilitar despliegue.

### RNF-8: Compatibilidad

**RNF-035:** El sistema debe integrarse con sistemas de facturación electrónica locales.

**RNF-036:** El sistema debe exponer API REST documentada (OpenAPI/Swagger).

**RNF-037:** El sistema debe permitir importación/exportación de datos en formatos estándar (Excel, CSV, JSON, XML).

### RNF-9: Accesibilidad

**RNF-038:** El sistema debe cumplir estándares WCAG 2.1 AA para accesibilidad web.

**RNF-039:** El sistema debe soportar lectores de pantalla.

**RNF-040:** El sistema debe permitir navegación completa por teclado.

### RNF-10: Localización

**RNF-041:** El sistema debe soportar idioma Español (Bolivia/Latinoamérica).

**RNF-042:** El sistema debe permitir agregar idiomas adicionales sin modificar código.

**RNF-043:** El sistema debe formatear fechas, números y moneda según configuración regional.

---

## PRIORIZACIÓN DE MÓDULOS (MVP)

### Fase 1 - MVP (Mínimo Producto Viable) - 3 meses

✅ Módulo 1: Usuarios y Roles (básico)
✅ Módulo 2: Gestión de Clientes
✅ Módulo 5: Productos y Catálogo
✅ Módulo 4: Ventas (Cotización, Pedido, Factura básica)
✅ Módulo 10: Inventario (stock básico, movimientos)
✅ Módulo 7: Reportes básicos (ventas, stock)

### Fase 2 - Comercial Avanzado - 2 meses

✅ Módulo 3: Preventas
✅ Módulo 6: Cuentas por Cobrar
✅ Módulo 7: Reportes avanzados (cobranza, aging)
✅ Módulo 8: Configuración completa

### Fase 3 - Auditoría y Optimización - 1 mes

✅ Módulo 9: Auditoría
✅ Mejoras de rendimiento
✅ Integración facturación electrónica
✅ App móvil PWA optimizada

---

## CRONOGRAMA ESTIMADO

**Duración total:** 6 meses
**Equipo recomendado:**
- 1 Tech Lead / Arquitecto
- 2 Desarrolladores Backend (Laravel)
- 2 Desarrolladores Frontend (Vue.js)
- 1 QA / Tester
- 1 UI/UX Designer
- 1 Product Owner / Business Analyst

---

## PRÓXIMOS PASOS

1. ✅ **Aprobación de especificación técnica**
2. 📋 **Creación de modelo de base de datos (ERD)**
3. 📋 **Wireframes y diseño de UI/UX**
4. 📋 **Configuración de entorno de desarrollo**
5. 📋 **Sprint Planning - Fase 1 (MVP)**

---

**Documento preparado por:** Gabriel
**Para:** Distribuidora de Medicamentos PANDO
**Fecha:** 20 de Octubre, 2025

---

*Este documento es confidencial y propiedad de Distribuidora de Medicamentos PANDO. No puede ser reproducido, distribuido o utilizado sin autorización expresa.*
