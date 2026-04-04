<?php

namespace App\Traits;

use App\Models\Clinica;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para manejar el scope de clínica en controladores
 * Proporciona métodos helper para filtrar datos por clínica
 */
trait ClinicaScope
{
    /**
     * Obtener el ID de la clínica actual del usuario autenticado
     */
    protected function getClinicaId(): ?int
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }
        
        // Si tiene clinica_id en sesión (multi-tenant)
        if (session()->has('clinica_id')) {
            return session('clinica_id');
        }
        
        // Si el usuario tiene una clínica asociada
        if ($user->clinica_id) {
            return $user->clinica_id;
        }
        
        // Si tiene clínicas asociadas, usar la primera
        if (method_exists($user, 'clinicas') && $user->clinicas->isNotEmpty()) {
            return $user->clinicas->first()->id;
        }
        
        return null;
    }

    /**
     * Obtener la clínica actual
     */
    protected function getClinica(): ?Clinica
    {
        $clinicaId = $this->getClinicaId();
        
        if (!$clinicaId) {
            return null;
        }
        
        return Clinica::find($clinicaId);
    }

    /**
     * Aplicar scope de clínica a una query
     */
    protected function applyClinicaScope(Builder $query, string $column = 'clinica_id'): Builder
    {
        $clinicaId = $this->getClinicaId();
        
        if ($clinicaId) {
            return $query->where($column, $clinicaId);
        }
        
        return $query;
    }

    /**
     * Verificar si el usuario puede acceder a un recurso de una clínica específica
     */
    protected function canAccessClinica(int $clinicaId): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Si es superadmin global, puede acceder a todo
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        
        // Verificar si la clínica coincide
        if ($user->clinica_id === $clinicaId) {
            return true;
        }
        
        // Verificar en clínicas asociadas
        if (method_exists($user, 'clinicas')) {
            return $user->clinicas->contains('id', $clinicaId);
        }
        
        return false;
    }
}
