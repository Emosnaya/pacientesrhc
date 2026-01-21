#!/bin/bash

# Script de Verificación Pre y Post Migración
# Verifica el estado de los datos antes y después de la migración

echo "======================================"
echo "  VERIFICACIÓN DE MIGRACIÓN"
echo "======================================"
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

CLINICA_ID=1

echo "🔍 Verificando estado de la base de datos..."
echo ""

# Verificar si existe la tabla sucursales
echo "1️⃣  Verificando tabla sucursales..."
TABLA_EXISTE=$(php artisan tinker --execute="echo Schema::hasTable('sucursales') ? 'SI' : 'NO';")

if [[ $TABLA_EXISTE == *"SI"* ]]; then
    echo -e "${GREEN}   ✓ Tabla 'sucursales' existe${NC}"
else
    echo -e "${YELLOW}   ⚠️  Tabla 'sucursales' NO existe (ejecutar migraciones)${NC}"
fi

echo ""
echo "2️⃣  Verificando clínica ID=$CLINICA_ID..."

# Verificar que existe la clínica
php artisan tinker --execute="
\$clinica = \App\Models\Clinica::find($CLINICA_ID);
if (\$clinica) {
    echo '✓ Clínica: ' . \$clinica->nombre . PHP_EOL;
    echo '   Email: ' . \$clinica->email . PHP_EOL;
    echo '   Activa: ' . (\$clinica->activa ? 'SI' : 'NO') . PHP_EOL;
} else {
    echo '❌ ERROR: Clínica no encontrada' . PHP_EOL;
}
"

echo ""
echo "3️⃣  Conteo de registros actuales..."

php artisan tinker --execute="
\$clinicaId = $CLINICA_ID;

\$usuarios = \App\Models\User::where('clinica_id', \$clinicaId)->count();
\$usuariosSinSucursal = \App\Models\User::where('clinica_id', \$clinicaId)->whereNull('sucursal_id')->count();

\$pacientes = \App\Models\Paciente::where('clinica_id', \$clinicaId)->count();
\$pacientesSinSucursal = \App\Models\Paciente::where('clinica_id', \$clinicaId)->whereNull('sucursal_id')->count();

\$citas = \DB::table('citas')->where('clinica_id', \$clinicaId)->count();
\$citasSinSucursal = \DB::table('citas')->where('clinica_id', \$clinicaId)->whereNull('sucursal_id')->count();

\$clinicos = \DB::table('clinicos')->where('clinica_id', \$clinicaId)->count();
\$clinicosSinSucursal = \DB::table('clinicos')->where('clinica_id', \$clinicaId)->whereNull('sucursal_id')->count();

echo '📊 USUARIOS' . PHP_EOL;
echo '   Total: ' . \$usuarios . PHP_EOL;
echo '   Sin sucursal: ' . \$usuariosSinSucursal . PHP_EOL;
echo '' . PHP_EOL;

echo '📊 PACIENTES' . PHP_EOL;
echo '   Total: ' . \$pacientes . PHP_EOL;
echo '   Sin sucursal: ' . \$pacientesSinSucursal . PHP_EOL;
echo '' . PHP_EOL;

echo '📊 CITAS' . PHP_EOL;
echo '   Total: ' . \$citas . PHP_EOL;
echo '   Sin sucursal: ' . \$citasSinSucursal . PHP_EOL;
echo '' . PHP_EOL;

echo '📊 EXPEDIENTES CLÍNICOS' . PHP_EOL;
echo '   Total: ' . \$clinicos . PHP_EOL;
echo '   Sin sucursal: ' . \$clinicosSinSucursal . PHP_EOL;
"

echo ""
echo "4️⃣  Verificando sucursales existentes..."

php artisan tinker --execute="
\$sucursales = \App\Models\Sucursal::where('clinica_id', $CLINICA_ID)->get();

if (\$sucursales->count() > 0) {
    echo '✓ Sucursales encontradas: ' . \$sucursales->count() . PHP_EOL;
    echo '' . PHP_EOL;
    foreach (\$sucursales as \$sucursal) {
        echo '   📍 ' . \$sucursal->nombre . PHP_EOL;
        echo '      Código: ' . \$sucursal->codigo . PHP_EOL;
        echo '      Principal: ' . (\$sucursal->es_principal ? 'SI' : 'NO') . PHP_EOL;
        echo '      Activa: ' . (\$sucursal->activa ? 'SI' : 'NO') . PHP_EOL;
        echo '      Usuarios: ' . \$sucursal->usuarios()->count() . PHP_EOL;
        echo '      Pacientes: ' . \$sucursal->pacientes()->count() . PHP_EOL;
        echo '' . PHP_EOL;
    }
} else {
    echo '⚠️  No se encontraron sucursales (normal antes de ejecutar seeder)' . PHP_EOL;
}
"

echo ""
echo "======================================"
echo "  VERIFICACIÓN COMPLETADA"
echo "======================================"
echo ""
echo "📋 SIGUIENTES PASOS:"
echo ""
echo "Si AÚN NO has ejecutado la migración:"
echo "  1. php artisan migrate --force"
echo "  2. php artisan db:seed --class=CrearSucursalProduccionSeeder --force"
echo ""
echo "Si YA ejecutaste la migración, verifica que:"
echo "  • Existe al menos 1 sucursal"
echo "  • Usuarios sin sucursal = 0"
echo "  • Pacientes sin sucursal = 0"
echo "  • Citas sin sucursal = 0"
echo ""
