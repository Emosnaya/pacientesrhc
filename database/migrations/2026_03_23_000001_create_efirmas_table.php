<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para almacenar certificados de e.firma (SAT) de los usuarios.
     * Los archivos .cer y .key se almacenan encriptados.
     */
    public function up(): void
    {
        Schema::create('efirmas', function (Blueprint $table) {
            $table->id();
            
            // Puede pertenecer a un usuario (firma personal) O a una clínica (firma fiscal)
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinica_id')->nullable()->constrained('clinicas')->cascadeOnDelete();
            
            // Tipo de e.firma: 'personal' (recetas) o 'fiscal' (facturas)
            $table->enum('tipo', ['personal', 'fiscal'])->default('personal');
            
            // Certificado público (.cer) - base64 encoded
            $table->text('certificado_cer')->nullable();
            
            // Llave privada (.key) - base64 encoded y encriptada
            $table->text('llave_key_encrypted')->nullable();
            
            // Datos extraídos del certificado
            $table->string('numero_serie', 50)->nullable(); // Número de serie del certificado
            $table->string('rfc', 15)->nullable(); // RFC del titular
            $table->string('nombre_titular')->nullable(); // Nombre completo del titular
            $table->string('curp', 20)->nullable(); // CURP si está disponible
            $table->timestamp('vigencia_inicio')->nullable();
            $table->timestamp('vigencia_fin')->nullable();
            
            // Estado
            $table->boolean('activa')->default(true);
            $table->boolean('verificada')->default(false); // Si se verificó que funciona
            $table->timestamp('ultima_verificacion')->nullable();
            
            // Quién la subió (para auditoría)
            $table->foreignId('subida_por')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices únicos
            $table->unique(['user_id', 'tipo']); // Un usuario = una e.firma personal
            $table->unique(['clinica_id', 'tipo']); // Una clínica = una e.firma fiscal
        });

        // Agregar campos de firma a recetas
        Schema::table('recetas', function (Blueprint $table) {
            $table->text('firma_digital')->nullable()->after('indicaciones_generales'); // Sello PKCS#7
            $table->string('cadena_original', 2000)->nullable()->after('firma_digital');
            $table->timestamp('firmada_at')->nullable()->after('cadena_original');
            $table->string('numero_serie_certificado', 50)->nullable()->after('firmada_at');
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn(['firma_digital', 'cadena_original', 'firmada_at', 'numero_serie_certificado']);
        });
        
        Schema::dropIfExists('efirmas');
    }
};
