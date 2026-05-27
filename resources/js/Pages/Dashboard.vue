<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Analítico</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-1">{{ currentMonthLabel }} · {{ user?.name }}</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="printProjections" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <Printer class="w-4 h-4" />
            Exportar PDF
          </button>
          <Badge variant="success" size="md">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
            Sistema Activo
          </Badge>
        </div>
      </div>
    </template>

    <div class="space-y-6">

      <!-- Error -->
      <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-start gap-3">
        <AlertCircle class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" />
        <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  KPI CARDS                                                      -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="analyticsKpis" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <!-- Ventas del mes -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-5 text-white shadow-md">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-white/20 rounded-lg p-2"><ShoppingCart class="w-5 h-5" /></div>
            <span :class="analyticsKpis.ventas_growth >= 0 ? 'bg-green-400/25 text-green-100' : 'bg-red-400/25 text-red-100'" class="text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1">
              <TrendingUp v-if="analyticsKpis.ventas_growth >= 0" class="w-3 h-3" />
              <TrendingDown v-else class="w-3 h-3" />
              {{ analyticsKpis.ventas_growth >= 0 ? '+' : '' }}{{ analyticsKpis.ventas_growth }}%
            </span>
          </div>
          <p class="text-blue-100 text-xs font-medium uppercase tracking-wider mb-1">Ventas del Mes</p>
          <p class="text-2xl font-bold">{{ fmtCurrency(analyticsKpis.ventas_mes) }}</p>
          <p class="text-blue-200 text-xs mt-2">{{ analyticsKpis.cant_ventas_mes }} ventas · vs {{ fmtCurrency(analyticsKpis.ventas_anterior) }} mes ant.</p>
        </div>

        <!-- Cobros del mes -->
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl p-5 text-white shadow-md">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-white/20 rounded-lg p-2"><CreditCard class="w-5 h-5" /></div>
            <span :class="analyticsKpis.cobros_growth >= 0 ? 'bg-green-400/25 text-green-100' : 'bg-red-400/25 text-red-100'" class="text-xs font-semibold px-2 py-1 rounded-full flex items-center gap-1">
              <TrendingUp v-if="analyticsKpis.cobros_growth >= 0" class="w-3 h-3" />
              <TrendingDown v-else class="w-3 h-3" />
              {{ analyticsKpis.cobros_growth >= 0 ? '+' : '' }}{{ analyticsKpis.cobros_growth }}%
            </span>
          </div>
          <p class="text-emerald-100 text-xs font-medium uppercase tracking-wider mb-1">Cobros del Mes</p>
          <p class="text-2xl font-bold">{{ fmtCurrency(analyticsKpis.cobros_mes) }}</p>
          <p class="text-emerald-200 text-xs mt-2">vs {{ fmtCurrency(analyticsKpis.cobros_anterior) }} mes anterior</p>
        </div>

        <!-- Por cobrar -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-md">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-white/20 rounded-lg p-2"><Receipt class="w-5 h-5" /></div>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Cartera</span>
          </div>
          <p class="text-amber-100 text-xs font-medium uppercase tracking-wider mb-1">Por Cobrar</p>
          <p class="text-2xl font-bold">{{ fmtCurrency(analyticsKpis.total_por_cobrar) }}</p>
          <p class="text-amber-200 text-xs mt-2">Saldo pendiente total</p>
        </div>

        <!-- Vencido -->
        <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-xl p-5 text-white shadow-md">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-white/20 rounded-lg p-2"><AlertTriangle class="w-5 h-5" /></div>
            <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">⚠ Urgente</span>
          </div>
          <p class="text-red-100 text-xs font-medium uppercase tracking-wider mb-1">Vencido</p>
          <p class="text-2xl font-bold">{{ fmtCurrency(analyticsKpis.total_vencido) }}</p>
          <p class="text-red-200 text-xs mt-2">{{ analyticsKpis.clientes_vencidos }} clientes con deuda vencida</p>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  GRÁFICO TENDENCIA + DONA                                       -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Tendencia 12 meses -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <LineChart class="w-5 h-5 text-blue-500" />
                Tendencia de Ventas y Cobros
              </CardTitle>
              <div class="flex gap-1">
                <button v-for="p in [3, 6, 12]" :key="p"
                  @click="trendPeriod = p; rebuildTrend()"
                  :class="['px-3 py-1 rounded-md text-xs font-medium transition-colors',
                    trendPeriod === p
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700']">
                  {{ p }}m
                </button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div style="position:relative;height:260px">
              <canvas ref="trendRef"></canvas>
            </div>
          </CardContent>
        </Card>

        <!-- Cartera por antigüedad (dona) -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <PieChart class="w-5 h-5 text-amber-500" />
              Cartera por Antigüedad
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div style="position:relative;height:180px">
              <canvas ref="donutRef"></canvas>
            </div>
            <div v-if="receivablesBreakdown" class="mt-4 space-y-2 text-sm">
              <div class="flex justify-between items-center">
                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Al día</span>
                <span class="font-semibold">{{ fmtCurrency(receivablesBreakdown.al_dia) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>1-30 días</span>
                <span class="font-semibold">{{ fmtCurrency(receivablesBreakdown.vencido_30) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>31-60 días</span>
                <span class="font-semibold">{{ fmtCurrency(receivablesBreakdown.vencido_60) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block"></span>>60 días</span>
                <span class="font-semibold text-red-600">{{ fmtCurrency(receivablesBreakdown.vencido_mas60) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  COMPARATIVA ANUAL                                              -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <Card v-if="periodComparison">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <BarChart2 class="w-5 h-5 text-indigo-500" />
              Comparativa de Ventas por Período
            </CardTitle>
            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
              <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-indigo-500 inline-block"></span>{{ periodComparison.current_year }}</span>
              <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-300 dark:bg-gray-600 inline-block"></span>{{ periodComparison.previous_year }}</span>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div style="position:relative;height:260px">
            <canvas ref="compRef"></canvas>
          </div>
        </CardContent>
      </Card>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  PROYECCIONES                                                   -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="proyecciones-pdf">

        <!-- Proyección de ventas -->
        <Card v-if="salesProjection">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <TrendingUp class="w-5 h-5 text-blue-500" />
              Proyección de Ventas
              <span class="text-xs font-normal text-gray-500 dark:text-gray-400">próximos 3 meses</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Base de cálculo</p>
              <p class="text-sm text-blue-800 dark:text-blue-200">Promedio últimos 3 meses: <strong>{{ fmtCurrency(salesProjection.avg_base) }}</strong> · Tendencia: <strong :class="salesProjection.trend_pct >= 0 ? 'text-green-600' : 'text-red-500'">{{ salesProjection.trend_pct >= 0 ? '+' : '' }}{{ salesProjection.trend_pct }}% / mes</strong></p>
            </div>
            <div class="space-y-3">
              <div v-for="(item, i) in salesProjection.projection" :key="i"
                class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white text-sm">{{ item.label }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Estimado</p>
                </div>
                <div class="text-right">
                  <p class="font-bold text-gray-900 dark:text-white">{{ fmtCurrency(item.estimated) }}</p>
                  <p class="text-xs" :class="item.growth >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ item.growth >= 0 ? '↑' : '↓' }} {{ Math.abs(item.growth * (i+1)).toFixed(1) }}% vs base
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Proyección de cobros -->
        <Card v-if="receivablesProjection">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Wallet class="w-5 h-5 text-emerald-500" />
              Proyección de Cobros
              <span class="text-xs font-normal text-gray-500 dark:text-gray-400">próximos 90 días</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
              <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Total proyectado</p>
              <p class="text-lg font-bold text-emerald-800 dark:text-emerald-200">{{ fmtCurrency(receivablesProjection.total_proyectado) }}</p>
            </div>
            <div class="space-y-3">
              <div v-for="(w, i) in receivablesProjection.windows" :key="i"
                :class="['flex items-center justify-between p-3 rounded-lg border transition-colors hover:bg-gray-50 dark:hover:bg-gray-800',
                  i === 0 ? 'border-emerald-200 dark:border-emerald-800' : i === 1 ? 'border-amber-200 dark:border-amber-800' : 'border-orange-200 dark:border-orange-800']">
                <div>
                  <p class="font-medium text-gray-900 dark:text-white text-sm">{{ w.label }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ w.count }} facturas por vencer</p>
                </div>
                <p :class="['font-bold text-sm', i === 0 ? 'text-emerald-600 dark:text-emerald-400' : i === 1 ? 'text-amber-600 dark:text-amber-400' : 'text-orange-600 dark:text-orange-400']">
                  {{ fmtCurrency(w.amount) }}
                </p>
              </div>
            </div>
            <!-- Mini chart de cobros mensuales -->
            <div class="mt-4" style="position:relative;height:100px">
              <canvas ref="cobrosRef"></canvas>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  CLIENTES QUE DEJARON DE COMPRAR                               -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <Card v-if="churnedClients && churnedClients.length > 0">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <UserX class="w-5 h-5 text-red-500" />
              Clientes sin Actividad Reciente
              <span class="bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-semibold px-2 py-0.5 rounded-full">{{ churnedClients.length }}</span>
            </CardTitle>
            <a href="/clientes" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">Ver todos</a>
          </div>
        </CardHeader>
        <CardContent>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Clientes que compraron en los últimos 6 meses pero llevan más de 60 días sin realizar pedidos.</p>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  <th class="text-left pb-3 pr-4">Cliente</th>
                  <th class="text-left pb-3 pr-4">Último pedido</th>
                  <th class="text-left pb-3 pr-4">Monto</th>
                  <th class="text-left pb-3">Días sin comprar</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="c in churnedClients" :key="c.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                  <td class="py-2.5 pr-4 font-medium text-gray-900 dark:text-white">
                    <a :href="`/clientes/${c.id}`" class="hover:text-primary-600 dark:hover:text-primary-400">{{ c.name }}</a>
                  </td>
                  <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ c.last_sale_date ?? '—' }}</td>
                  <td class="py-2.5 pr-4 text-gray-600 dark:text-gray-400">{{ fmtCurrency(c.last_sale_amount) }}</td>
                  <td class="py-2.5">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      c.days_since > 120 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' :
                      c.days_since > 90  ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' :
                                           'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400']">
                      {{ c.days_since }} días
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  ALERTAS                                                        -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div v-if="alerts && alerts.length > 0" class="space-y-2">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alertas del Sistema</h3>
        <div v-for="alert in alerts" :key="alert.title"
             :class="['p-3 rounded-lg border flex items-start gap-3', getAlertClasses(alert.type)]">
          <AlertTriangle v-if="alert.type === 'error'" class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" />
          <AlertCircle v-else-if="alert.type === 'warning'" class="h-4 w-4 text-yellow-500 mt-0.5 flex-shrink-0" />
          <Info v-else class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">{{ alert.title }}</p>
            <p class="text-xs mt-0.5 opacity-80">{{ alert.message }}</p>
          </div>
          <Link v-if="alert.action" :href="alert.action" class="text-xs font-medium underline flex-shrink-0">Ver</Link>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  QUICK STATS                                                    -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-5">
            <div class="flex items-center gap-3">
              <div class="bg-blue-100 dark:bg-blue-900/40 p-2.5 rounded-lg"><Users class="h-5 w-5 text-blue-600 dark:text-blue-400" /></div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Usuarios</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats?.users || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-5">
            <div class="flex items-center gap-3">
              <div class="bg-green-100 dark:bg-green-900/40 p-2.5 rounded-lg"><Building2 class="h-5 w-5 text-green-600 dark:text-green-400" /></div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Clientes</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats?.clients || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-5">
            <div class="flex items-center gap-3">
              <div class="bg-purple-100 dark:bg-purple-900/40 p-2.5 rounded-lg"><Package class="h-5 w-5 text-purple-600 dark:text-purple-400" /></div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Productos</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats?.products || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent class="p-5">
            <div class="flex items-center gap-3">
              <div class="bg-orange-100 dark:bg-orange-900/40 p-2.5 rounded-lg"><ShoppingCart class="h-5 w-5 text-orange-600 dark:text-orange-400" /></div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ventas</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats?.sales || 0 }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  ESTADO DE ÓRDENES + PRODUCTOS                                  -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader><CardTitle class="text-base">Estado de Órdenes</CardTitle></CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-3">
              <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ orderStats?.pending || 0 }}</div>
                <div class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">Pendientes</div>
              </div>
              <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ orderStats?.delivered || 0 }}</div>
                <div class="text-xs text-green-700 dark:text-green-400 mt-1">Entregadas</div>
              </div>
              <div class="text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ orderStats?.unpaid || 0 }}</div>
                <div class="text-xs text-orange-700 dark:text-orange-400 mt-1">Sin pagar</div>
              </div>
              <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ orderStats?.cancelled || 0 }}</div>
                <div class="text-xs text-red-700 dark:text-red-400 mt-1">Canceladas</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle class="text-base">Estado de Productos</CardTitle></CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total activos</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ productStats?.active || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Stock bajo</span>
                <span class="font-semibold text-yellow-600">{{ productStats?.low_stock || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Sin stock</span>
                <span class="font-semibold text-red-600">{{ productStats?.out_of_stock || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Inactivos</span>
                <span class="font-semibold text-gray-500">{{ productStats?.inactive || 0 }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  ACTIVIDAD RECIENTE                                             -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader><CardTitle class="text-base">Ventas Recientes</CardTitle></CardHeader>
          <CardContent>
            <div v-if="recentOrders?.length" class="space-y-2">
              <div v-for="order in recentOrders" :key="order.id"
                   class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ order.client?.business_name || 'Cliente' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.salesperson?.name || '—' }}</p>
                </div>
                <div class="text-right">
                  <Badge :variant="getOrderStatusVariant(order.status)">{{ getOrderStatusText(order.status) }}</Badge>
                  <p class="text-xs text-gray-400 mt-1">{{ fmtDate(order.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-400 dark:text-gray-500 py-8 text-sm">Sin ventas recientes</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle class="text-base">Clientes Principales</CardTitle></CardHeader>
          <CardContent>
            <div v-if="topClients?.length" class="space-y-2">
              <div v-for="client in topClients" :key="client.id"
                   class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ client.business_name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ client.trade_name }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ client.orders_count }} ventas</p>
                  <p class="text-xs text-gray-400">{{ client.invoices_count }} facturas</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-400 dark:text-gray-500 py-8 text-sm">Sin datos</div>
          </CardContent>
        </Card>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  PRODUCTOS POR VENCER                                           -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <Card v-if="expiringProducts?.length">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <AlertTriangle class="h-5 w-5 text-orange-500" />
            Productos Próximos a Vencer
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Producto</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Código</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vence</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="p in expiringProducts" :key="p.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                  <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ p.name }}</td>
                  <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ p.code }}</td>
                  <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ p.stock_quantity }}</td>
                  <td class="px-4 py-3" :class="p.is_expired ? 'text-red-600 font-medium' : p.is_expiring_soon ? 'text-orange-600 font-medium' : 'text-gray-600 dark:text-gray-400'">{{ fmtDate(p.expiry_date) }}</td>
                  <td class="px-4 py-3">
                    <Badge :variant="p.is_expired ? 'danger' : p.is_expiring_soon ? 'warning' : 'info'">
                      {{ p.is_expired ? `Vencido hace ${Math.abs(p.days_until_expiry)}d` : `Vence en ${p.days_until_expiry}d` }}
                    </Badge>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <!--  ACCIONES RÁPIDAS                                               -->
      <!-- ═══════════════════════════════════════════════════════════════ -->
      <Card>
        <CardHeader><CardTitle class="text-base">Acciones Rápidas</CardTitle></CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <Link href="/preventas/crear" class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
              <FileText class="h-7 w-7 text-blue-600 dark:text-blue-400 mb-2" />
              <span class="text-xs font-medium text-blue-900 dark:text-blue-200">Nueva Preventa</span>
            </Link>
            <Link href="/ventas/crear" class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
              <ShoppingCart class="h-7 w-7 text-green-600 dark:text-green-400 mb-2" />
              <span class="text-xs font-medium text-green-900 dark:text-green-200">Nueva Venta</span>
            </Link>
            <Link href="/clientes/crear" class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors">
              <Building2 class="h-7 w-7 text-purple-600 dark:text-purple-400 mb-2" />
              <span class="text-xs font-medium text-purple-900 dark:text-purple-200">Nuevo Cliente</span>
            </Link>
            <Link href="/usuarios/crear" class="flex flex-col items-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
              <Users class="h-7 w-7 text-orange-600 dark:text-orange-400 mb-2" />
              <span class="text-xs font-medium text-orange-900 dark:text-orange-200">Nuevo Usuario</span>
            </Link>
          </div>
        </CardContent>
      </Card>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Chart, registerables } from 'chart.js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import Badge from '@/Components/ui/Badge.vue'
import {
  Users, Building2, Package, ShoppingCart, FileText,
  AlertCircle, AlertTriangle, Info,
  TrendingUp, TrendingDown, CreditCard, Receipt,
  LineChart, BarChart2, PieChart, Printer, Wallet, UserX
} from 'lucide-vue-next'

Chart.register(...registerables)

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  user: Object, stats: Object, monthlyStats: Object, orderStats: Object,
  clientStats: Object, salesChart: Array, recentOrders: Array, topClients: Array,
  productStats: Object, userStats: Object, alerts: Array, performanceMetrics: Object,
  expiringProducts: Array, error: String,
  // Analytics
  analyticsKpis: Object, monthlyChartData: Array, periodComparison: Object,
  receivablesBreakdown: Object, salesProjection: Object, receivablesProjection: Object,
  churnedClients: Array,
})

// ── State ─────────────────────────────────────────────────────────────────────
const trendRef  = ref(null)
const compRef   = ref(null)
const donutRef  = ref(null)
const cobrosRef = ref(null)

let trendChart = null, compChart = null, donutChart = null, cobrosChart = null

const trendPeriod = ref(12)

// ── Helpers ───────────────────────────────────────────────────────────────────
const MONTH_NAMES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
const currentMonthLabel = computed(() => {
  const d = new Date()
  return `${MONTH_NAMES[d.getMonth()]} ${d.getFullYear()}`
})

const fmtCurrency = (v) =>
  new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 }).format(v || 0)

const fmtDate = (d) => {
  if (!d) return '—'
  try { return new Date(d).toLocaleDateString('es-BO') } catch { return '—' }
}

const isDark = () => document.documentElement.classList.contains('dark')
const axisColors = () => ({
  text: isDark() ? '#9CA3AF' : '#6B7280',
  grid: isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)',
})

const tooltipDefaults = {
  backgroundColor: 'rgba(15,23,42,0.92)',
  titleColor: '#F1F5F9',
  bodyColor: '#CBD5E1',
  borderColor: 'rgba(255,255,255,0.08)',
  borderWidth: 1,
  padding: 12,
  cornerRadius: 8,
}

// ── Chart: Tendencia ──────────────────────────────────────────────────────────
const buildTrend = () => {
  if (trendChart) { trendChart.destroy(); trendChart = null }
  if (!trendRef.value || !props.monthlyChartData?.length) return

  const ax    = axisColors()
  const slice = props.monthlyChartData.slice(-trendPeriod.value)

  trendChart = new Chart(trendRef.value, {
    type: 'line',
    data: {
      labels: slice.map(d => d.label),
      datasets: [
        {
          label: 'Ventas',
          data: slice.map(d => d.ventas),
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59,130,246,0.10)',
          fill: true, tension: 0.45,
          pointBackgroundColor: '#3B82F6', pointRadius: 4, pointHoverRadius: 7,
          borderWidth: 2.5,
        },
        {
          label: 'Cobros',
          data: slice.map(d => d.cobros),
          borderColor: '#10B981',
          backgroundColor: 'rgba(16,185,129,0.10)',
          fill: true, tension: 0.45,
          pointBackgroundColor: '#10B981', pointRadius: 4, pointHoverRadius: 7,
          borderWidth: 2.5,
        },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', labels: { color: ax.text, usePointStyle: true, boxWidth: 8, padding: 16, font: { size: 12 } } },
        tooltip: { ...tooltipDefaults, callbacks: { label: c => `  ${c.dataset.label}: ${fmtCurrency(c.parsed.y)}` } },
      },
      scales: {
        x: { grid: { color: ax.grid }, ticks: { color: ax.text, font: { size: 11 } } },
        y: { grid: { color: ax.grid }, ticks: { color: ax.text, font: { size: 11 }, callback: v => v >= 1000 ? 'Bs ' + (v / 1000).toFixed(0) + 'k' : 'Bs ' + v } },
      },
    },
  })
}

const rebuildTrend = () => buildTrend()

// ── Chart: Comparativa anual ──────────────────────────────────────────────────
const buildComparison = () => {
  if (compChart) { compChart.destroy(); compChart = null }
  if (!compRef.value || !props.periodComparison?.months?.length) return

  const ax = axisColors()
  const m  = props.periodComparison.months

  compChart = new Chart(compRef.value, {
    type: 'bar',
    data: {
      labels: m.map(d => d.month),
      datasets: [
        {
          label: String(props.periodComparison.current_year),
          data: m.map(d => d.current_year),
          backgroundColor: 'rgba(99,102,241,0.75)',
          borderColor: '#6366F1',
          borderWidth: 1.5,
          borderRadius: 4,
        },
        {
          label: String(props.periodComparison.previous_year),
          data: m.map(d => d.previous_year),
          backgroundColor: 'rgba(148,163,184,0.5)',
          borderColor: '#94A3B8',
          borderWidth: 1.5,
          borderRadius: 4,
        },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', labels: { color: ax.text, usePointStyle: true, boxWidth: 8, padding: 16, font: { size: 12 } } },
        tooltip: { ...tooltipDefaults, callbacks: { label: c => `  ${c.dataset.label}: ${fmtCurrency(c.parsed.y)}` } },
      },
      scales: {
        x: { grid: { color: ax.grid }, ticks: { color: ax.text, font: { size: 11 } } },
        y: { grid: { color: ax.grid }, ticks: { color: ax.text, font: { size: 11 }, callback: v => v >= 1000 ? 'Bs ' + (v / 1000).toFixed(0) + 'k' : 'Bs ' + v } },
      },
    },
  })
}

// ── Chart: Dona antigüedad ────────────────────────────────────────────────────
const buildDonut = () => {
  if (donutChart) { donutChart.destroy(); donutChart = null }
  if (!donutRef.value || !props.receivablesBreakdown) return

  const rb = props.receivablesBreakdown
  const total = rb.al_dia + rb.vencido_30 + rb.vencido_60 + rb.vencido_mas60

  donutChart = new Chart(donutRef.value, {
    type: 'doughnut',
    data: {
      labels: ['Al día', '1-30 días', '31-60 días', '>60 días'],
      datasets: [{
        data: [rb.al_dia, rb.vencido_30, rb.vencido_60, rb.vencido_mas60],
        backgroundColor: ['#10B981', '#FBBF24', '#F97316', '#EF4444'],
        borderColor: isDark() ? '#1F2937' : '#FFFFFF',
        borderWidth: 3,
        hoverOffset: 6,
      }],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: {
          ...tooltipDefaults,
          callbacks: {
            label: c => `  ${c.label}: ${fmtCurrency(c.parsed)} (${total > 0 ? ((c.parsed / total) * 100).toFixed(1) : 0}%)`,
          },
        },
      },
    },
  })
}

// ── Chart: Mini cobros futuros ────────────────────────────────────────────────
const buildCobros = () => {
  if (cobrosChart) { cobrosChart.destroy(); cobrosChart = null }
  if (!cobrosRef.value || !props.receivablesProjection?.monthly?.length) return

  const ax   = axisColors()
  const data = props.receivablesProjection.monthly

  cobrosChart = new Chart(cobrosRef.value, {
    type: 'bar',
    data: {
      labels: data.map(d => d.label),
      datasets: [{
        label: 'Cobros proyectados',
        data: data.map(d => d.amount),
        backgroundColor: 'rgba(16,185,129,0.65)',
        borderColor: '#10B981',
        borderWidth: 1.5,
        borderRadius: 4,
      }],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { ...tooltipDefaults, callbacks: { label: c => ` ${fmtCurrency(c.parsed.y)}` } } },
      scales: {
        x: { grid: { display: false }, ticks: { color: ax.text, font: { size: 10 } } },
        y: { grid: { color: ax.grid }, ticks: { color: ax.text, font: { size: 10 }, callback: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v } },
      },
    },
  })
}

// ── PDF / Print ───────────────────────────────────────────────────────────────
const printProjections = () => window.print()

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  buildTrend()
  buildComparison()
  buildDonut()
  buildCobros()
})

onUnmounted(() => {
  trendChart?.destroy()
  compChart?.destroy()
  donutChart?.destroy()
  cobrosChart?.destroy()
})

// ── Status helpers ────────────────────────────────────────────────────────────
const getAlertClasses = (type) => ({
  error:   'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
  warning: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
  info:    'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
}[type] || 'bg-blue-50 border-blue-200')

const getOrderStatusVariant = (s) => ({ pending: 'warning', confirmed: 'info', completed: 'success', delivered: 'success', cancelled: 'danger' }[s] || 'secondary')
const getOrderStatusText    = (s) => ({ pending: 'Pendiente', confirmed: 'Confirmada', processing: 'Procesando', completed: 'Completada', delivered: 'Entregada', cancelled: 'Cancelada' }[s] || s)
</script>

<style>
@media print {
  /* Ocultar todo menos las secciones de proyecciones */
  body * { visibility: hidden; }
  #proyecciones-pdf, #proyecciones-pdf * { visibility: visible; }
  #proyecciones-pdf { position: fixed; top: 0; left: 0; width: 100%; padding: 2rem; }
}
</style>
