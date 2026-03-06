# Actualización: Egresos en Analíticas y Reportes Excel

## Cambios Implementados

### Backend - FinanzasController.php

#### 1. Endpoint `estadisticas()` Actualizado

**Antes**: Solo mostraba ingresos (pagos)

**Ahora**: Muestra ingresos, egresos y balance completo

**Nuevos campos en la respuesta**:
```json
{
  "total_ingresos": 23500.00,
  "total_egresos": 3500.00,
  "balance": 20000.00,
  "promedio_ingreso_diario": 783.33,
  "promedio_egreso_diario": 116.67,
  "promedio_balance_diario": 666.67,
  "cantidad_pagos": 45,
  "cantidad_egresos": 8,
  "egresos_por_tipo": {
    "compra_material": { "cantidad": 3, "total": 1500.00 },
    "servicios_publicos": { "cantidad": 2, "total": 850.00 }
  },
  "top_egresos": [
    { "tipo": "compra_material", "cantidad": 3, "total": 1500.00 }
  ]
}
```

#### 2. Exportación Excel `exportarCorte()` Actualizada

**Estructura del archivo CSV ahora incluye**:

**Sección 1: INGRESOS (PAGOS)**
- Tabla de pagos con todas las columnas
- Totales por método (Efectivo, Tarjeta, Transferencia)
- Total Ingresos
- Cantidad de pagos

**Sección 2: EGRESOS (GASTOS/RETIROS)** ← NUEVO
- Tabla de egresos con columnas:
  - No., Fecha, Hora
  - Tipo, Concepto, Proveedor, Factura
  - Monto, Registró, Sucursal, Notas
- Totales por tipo:
  - Compra de Material
  - Servicio
  - Mantenimiento
  - Nómina
  - Renta
  - Servicios Públicos
  - Otro
- Total Egresos
- Cantidad de egresos

**Sección 3: BALANCE GENERAL** ← NUEVO
- Total Ingresos
- Total Egresos
- BALANCE (Ingresos - Egresos)

### Frontend - AnalyticasFinancieras.jsx

#### Cards de Resumen Actualizados

**Antes** (2 cards principales):
1. Total del período
2. Promedio diario

**Ahora** (4 cards principales):
1. **Ingresos** (verde) - Total de pagos recibidos
2. **Egresos** (rojo) - Total de gastos/retiros
3. **Balance** (azul/rojo) - Diferencia ingresos - egresos
4. **Prom. Balance/día** (índigo) - Promedio del balance diario

**El card Balance cambia de color**:
- Azul (`border-blue-100`) si balance ≥ 0
- Rojo (`border-red-100`) si balance < 0

#### Nuevas Secciones

**1. Distribución de Ingresos por Método de Pago**
- Mantiene la funcionalidad original
- Barras de progreso con colores por método
- Porcentajes calculados sobre total de ingresos

**2. Distribución de Egresos por Tipo** ← NUEVO
- Icono: FaMinusCircle (rojo)
- Barras de progreso con 7 colores por tipo:
  - Compra Material: Azul
  - Servicio: Morado
  - Mantenimiento: Amarillo
  - Nómina: Verde
  - Renta: Rojo
  - Servicios Públicos: Índigo
  - Otro: Gris
- Muestra cantidad de egresos por categoría
- Porcentajes calculados sobre total de egresos

**3. Top 5 Categorías de Egresos** ← NUEVO
- Tabla ordenada por monto
- Columnas: #, Categoría (badge), Cantidad, Total
- Color coding por tipo de egreso

#### Constantes Actualizadas

```jsx
const ESTADISTICAS_VACIAS = {
  total_ingresos: 0,
  total_egresos: 0,
  balance: 0,
  promedio_ingreso_diario: 0,
  promedio_egreso_diario: 0,
  promedio_balance_diario: 0,
  cantidad_pagos: 0,
  cantidad_egresos: 0,
  por_metodo: {},
  egresos_por_tipo: {},
  top_pacientes: [],
  top_egresos: [],
  // Legacy fields (compatibilidad)
  total_periodo: 0,
  promedio_diario: 0
};

const tipoEgresoConfig = {
  compra_material: { 
    label: 'Compra Material', 
    color: 'bg-blue-100 text-blue-800', 
    barColor: 'bg-blue-500' 
  },
  servicio: { 
    label: 'Servicio', 
    color: 'bg-purple-100 text-purple-800', 
    barColor: 'bg-purple-500' 
  },
  // ... resto de configuraciones
};
```

## Uso

### Ver Analíticas con Egresos
1. Navegar a la sección de Analíticas/Inicio
2. Seleccionar período: Hoy, Última semana, Mes actual, o Personalizado
3. Visualizar:
   - Cards resumen: Ingresos, Egresos, Balance
   - Distribución de ingresos por método
   - **NUEVO**: Distribución de egresos por tipo
   - Top 5 pacientes (ingresos)
   - **NUEVO**: Top 5 categorías de egresos

### Exportar Reporte con Egresos
1. Ir a vista Caja
2. Click en "Cortes y Reportes"
3. Seleccionar tipo: Día, Mes, o Año
4. Seleccionar fecha
5. Click en "Exportar a Excel/CSV"
6. El archivo descargado incluirá:
   - Sección de Ingresos (pagos)
   - **NUEVO**: Sección de Egresos (con totales por tipo)
   - **NUEVO**: Balance General (Ingresos - Egresos)

## Ejemplo de Reporte CSV

```csv
# INGRESOS (PAGOS)
No. Recibo,Fecha,Hora,Paciente,Método,Monto
000045,2026-02-14,10:30,Juan Pérez,efectivo,500.00
...
TOTAL INGRESOS,,,,,15000.00

# EGRESOS (GASTOS/RETIROS)
No.,Fecha,Hora,Tipo,Concepto,Proveedor,Monto
000008,2026-02-14,15:30,Servicios Públicos,Pago luz,CFE,850.00
...
TOTAL EGRESOS,,,,,3500.00

# BALANCE GENERAL
Total Ingresos,15000.00
Total Egresos,3500.00
BALANCE,11500.00
```

## Compatibilidad

Los campos legacy (`total_periodo`, `promedio_diario`) se mantienen para compatibilidad con código existente:
- `total_periodo` = `total_ingresos`
- `promedio_diario` = `promedio_ingreso_diario`

El frontend renderiza correctamente con datos nuevos o legacy usando fallbacks:
```jsx
{formatMonto(stats.total_ingresos || stats.total_periodo)}
```

## Testing

### Backend
```bash
# Probar endpoint estadísticas
curl -H "Authorization: Bearer {token}" \
  "http://localhost:8000/api/finanzas/estadisticas?fecha_inicio=2026-02-01&fecha_fin=2026-02-14"

# Verificar respuesta incluye:
# - total_ingresos, total_egresos, balance
# - egresos_por_tipo, top_egresos
```

### Frontend
1. Navegar a Analíticas
2. Verificar 4 cards de resumen (Ingresos, Egresos, Balance, Promedio)
3. Verificar sección "Distribución de Egresos por Tipo" aparece
4. Verificar sección "Top 5 Categorías de Egresos" aparece
5. Verificar colores del card Balance (azul si positivo, rojo si negativo)

### Excel Export
1. Exportar corte de caja
2. Abrir CSV en Excel/Numbers
3. Verificar estructura:
   - Sección INGRESOS
   - Sección EGRESOS (nueva)
   - Sección BALANCE GENERAL (nueva)

---
**Versión**: 1.1  
**Fecha**: Febrero 2026  
**Cambios**: Integración completa de egresos en analíticas y reportes
