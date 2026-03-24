<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para almacenar datos fiscales de pacientes.
     * Preparado para integración futura con PAC (Facturama, Finkok, etc.)
     */
    public function up(): void
    {
        // Datos fiscales del paciente
        Schema::create('datos_fiscales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            
            // Datos requeridos por SAT para CFDI 4.0
            $table->string('rfc', 13); // RFC del receptor
            $table->string('razon_social'); // Nombre o razón social
            $table->string('codigo_postal', 5); // Código postal del domicilio fiscal
            $table->string('regimen_fiscal', 3); // Clave del régimen fiscal (catálogo SAT c_RegimenFiscal)
            $table->string('uso_cfdi', 4)->default('D01'); // Uso del CFDI (catálogo SAT c_UsoCFDI) - D01 = Honorarios médicos
            
            // Datos de contacto para envío de factura
            $table->string('email_facturacion')->nullable();
            
            // Dirección fiscal completa (opcional, pero útil)
            $table->string('calle')->nullable();
            $table->string('numero_exterior')->nullable();
            $table->string('numero_interior')->nullable();
            $table->string('colonia')->nullable();
            $table->string('localidad')->nullable();
            $table->string('municipio')->nullable();
            $table->string('estado')->nullable();
            $table->string('pais', 3)->default('MEX');
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Un paciente puede tener múltiples datos fiscales (diferentes RFC)
            // pero solo uno activo por clínica
            $table->unique(['paciente_id', 'clinica_id', 'rfc']);
        });

        // Solicitudes de factura
        Schema::create('solicitudes_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('datos_fiscales_id')->constrained('datos_fiscales')->cascadeOnDelete();
            $table->foreignId('pago_id')->nullable()->constrained('pagos')->nullOnDelete(); // Relación con el pago
            
            // Quién factura (clínica o doctor individual)
            $table->enum('emisor_tipo', ['clinica', 'doctor'])->default('clinica');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete(); // Si es doctor individual
            
            // Datos del concepto
            $table->string('concepto'); // Descripción del servicio
            $table->string('clave_prod_serv', 8)->default('85121800'); // Catálogo SAT - 85121800 = Servicios de médicos
            $table->string('clave_unidad', 3)->default('E48'); // Catálogo SAT - E48 = Unidad de servicio
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2)->default(0); // Servicios médicos generalmente exentos
            $table->decimal('total', 12, 2);
            
            // Estado de la solicitud
            $table->enum('estado', [
                'pendiente',      // Solicitada, pendiente de procesar
                'en_proceso',     // En proceso de timbrado
                'facturada',      // CFDI generado exitosamente
                'cancelada',      // Cancelada por usuario
                'error'           // Error al timbrar
            ])->default('pendiente');
            
            // Datos del CFDI (se llenan al timbrar)
            $table->string('uuid', 36)->nullable(); // UUID del CFDI timbrado
            $table->string('folio_fiscal')->nullable();
            $table->text('cadena_original_sat')->nullable();
            $table->text('sello_sat')->nullable();
            $table->text('sello_cfdi')->nullable();
            $table->timestamp('fecha_timbrado')->nullable();
            $table->string('serie', 10)->nullable();
            $table->integer('folio')->nullable();
            
            // Almacenamiento de archivos
            $table->string('xml_path')->nullable(); // Ruta al XML del CFDI
            $table->string('pdf_path')->nullable(); // Ruta al PDF
            
            // Envío
            $table->timestamp('enviada_at')->nullable();
            $table->string('enviada_a')->nullable(); // Email al que se envió
            
            // Cancelación
            $table->timestamp('cancelada_at')->nullable();
            $table->string('motivo_cancelacion')->nullable();
            $table->string('uuid_sustitucion', 36)->nullable(); // Si se sustituyó por otro CFDI
            
            // Auditoría
            $table->foreignId('solicitada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('procesada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->text('error_mensaje')->nullable(); // Mensaje de error si falló
            
            $table->timestamps();
            
            // Índices
            $table->index('uuid');
            $table->index('estado');
            $table->index(['clinica_id', 'estado']);
        });

        // Catálogo de regímenes fiscales SAT (para select en frontend)
        Schema::create('cat_regimenes_fiscales', function (Blueprint $table) {
            $table->string('clave', 3)->primary();
            $table->string('descripcion');
            $table->boolean('persona_fisica')->default(false);
            $table->boolean('persona_moral')->default(false);
            $table->boolean('activo')->default(true);
        });

        // Insertar catálogo de regímenes fiscales más comunes
        DB::table('cat_regimenes_fiscales')->insert([
            ['clave' => '601', 'descripcion' => 'General de Ley Personas Morales', 'persona_fisica' => false, 'persona_moral' => true, 'activo' => true],
            ['clave' => '603', 'descripcion' => 'Personas Morales con Fines no Lucrativos', 'persona_fisica' => false, 'persona_moral' => true, 'activo' => true],
            ['clave' => '605', 'descripcion' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '606', 'descripcion' => 'Arrendamiento', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '607', 'descripcion' => 'Régimen de Enajenación o Adquisición de Bienes', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '608', 'descripcion' => 'Demás ingresos', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '610', 'descripcion' => 'Residentes en el Extranjero sin Establecimiento Permanente en México', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => '611', 'descripcion' => 'Ingresos por Dividendos (socios y accionistas)', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '612', 'descripcion' => 'Personas Físicas con Actividades Empresariales y Profesionales', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '614', 'descripcion' => 'Ingresos por intereses', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '615', 'descripcion' => 'Régimen de los ingresos por obtención de premios', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '616', 'descripcion' => 'Sin obligaciones fiscales', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '620', 'descripcion' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', 'persona_fisica' => false, 'persona_moral' => true, 'activo' => true],
            ['clave' => '621', 'descripcion' => 'Incorporación Fiscal', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '622', 'descripcion' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => '623', 'descripcion' => 'Opcional para Grupos de Sociedades', 'persona_fisica' => false, 'persona_moral' => true, 'activo' => true],
            ['clave' => '624', 'descripcion' => 'Coordinados', 'persona_fisica' => false, 'persona_moral' => true, 'activo' => true],
            ['clave' => '625', 'descripcion' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => '626', 'descripcion' => 'Régimen Simplificado de Confianza', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
        ]);

        // Catálogo de usos de CFDI SAT
        Schema::create('cat_usos_cfdi', function (Blueprint $table) {
            $table->string('clave', 4)->primary();
            $table->string('descripcion');
            $table->boolean('persona_fisica')->default(true);
            $table->boolean('persona_moral')->default(true);
            $table->boolean('activo')->default(true);
        });

        // Insertar usos de CFDI más comunes para servicios médicos
        DB::table('cat_usos_cfdi')->insert([
            ['clave' => 'G01', 'descripcion' => 'Adquisición de mercancías', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'G02', 'descripcion' => 'Devoluciones, descuentos o bonificaciones', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'G03', 'descripcion' => 'Gastos en general', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'D01', 'descripcion' => 'Honorarios médicos, dentales y gastos hospitalarios', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D02', 'descripcion' => 'Gastos médicos por incapacidad o discapacidad', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D03', 'descripcion' => 'Gastos funerales', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D04', 'descripcion' => 'Donativos', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D05', 'descripcion' => 'Intereses reales efectivamente pagados por créditos hipotecarios', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D06', 'descripcion' => 'Aportaciones voluntarias al SAR', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D07', 'descripcion' => 'Primas por seguros de gastos médicos', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D08', 'descripcion' => 'Gastos de transportación escolar obligatoria', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D09', 'descripcion' => 'Depósitos en cuentas para el ahorro, primas de pensiones', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'D10', 'descripcion' => 'Pagos por servicios educativos (colegiaturas)', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
            ['clave' => 'P01', 'descripcion' => 'Por definir', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'S01', 'descripcion' => 'Sin efectos fiscales', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'CP01', 'descripcion' => 'Pagos', 'persona_fisica' => true, 'persona_moral' => true, 'activo' => true],
            ['clave' => 'CN01', 'descripcion' => 'Nómina', 'persona_fisica' => true, 'persona_moral' => false, 'activo' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_factura');
        Schema::dropIfExists('datos_fiscales');
        Schema::dropIfExists('cat_usos_cfdi');
        Schema::dropIfExists('cat_regimenes_fiscales');
    }
};
