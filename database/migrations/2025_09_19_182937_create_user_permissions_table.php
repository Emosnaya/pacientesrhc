<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que recibe el permiso
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade'); // Usuario admin que otorga el permiso
            $table->morphs('permissionable'); // Polymorphic relation para expedientes o pacientes
            $table->boolean('can_read')->default(false);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'permissionable_type', 'permissionable_id'], 'user_permissions_user_permissionable_idx');
            $table->index(['granted_by'], 'user_permissions_granted_by_idx');
            
            // Evitar permisos duplicados
            $table->unique(['user_id', 'permissionable_type', 'permissionable_id'], 'unique_user_permission');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
    }
};
