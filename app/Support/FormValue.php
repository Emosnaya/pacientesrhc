<?php

namespace App\Support;

/**
 * Normaliza valores de formularios clínicos antes de persistir.
 * Evita 500 por '' en columnas date/time/double y horas HH:MM:SS vs H:i.
 */
class FormValue
{
    /** Claves típicas de relaciones / meta que no deben ir a update(). */
    public const META_KEYS = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'user',
        'paciente',
        'clinica',
        'sucursal',
        'receta',
        'permissions',
        'tipo_exp_label',
        'autor',
        'autor_nombre',
    ];

    public static function nullIfEmpty(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }

    public static function numOrNull(mixed $value): ?float
    {
        $value = self::nullIfEmpty($value);
        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        return $value + 0;
    }

    public static function bool01(mixed $value): int
    {
        return ($value === true || $value === 1 || $value === '1' || $value === 'true') ? 1 : 0;
    }

    /** Normaliza hora a H:i (o null). */
    public static function horaOrNull(mixed $value): ?string
    {
        $value = self::nullIfEmpty($value);
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $parts = explode(':', $value);
            $h = (int) $parts[0];
            $m = (int) $parts[1];
            if ($h > 23 || $m > 59) {
                return null;
            }

            return sprintf('%02d:%02d', $h, $m);
        }

        return null;
    }

    /**
     * Recorre el array y convierte '' → null (incluye anidados).
     * Opcionalmente elimina claves meta (relaciones).
     */
    public static function sanitize(array $data, bool $stripMeta = true): array
    {
        if ($stripMeta) {
            foreach (self::META_KEYS as $key) {
                unset($data[$key]);
            }
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Listas JSON (recomendaciones, etc.) se conservan; objetos asociativos se sanean.
                $isList = array_is_list($value);
                $data[$key] = $isList
                    ? array_map(fn ($item) => is_array($item) ? self::sanitize($item, false) : self::nullIfEmpty($item), $value)
                    : self::sanitize($value, false);
                continue;
            }

            if (is_string($key) && (str_starts_with($key, 'hora') || $key === 'hora_consulta')) {
                $data[$key] = self::horaOrNull($value);
                continue;
            }

            $data[$key] = self::nullIfEmpty($value);
        }

        return $data;
    }

    public static function fromRequest(\Illuminate\Http\Request $request, array $except = []): array
    {
        return self::sanitize($request->except(array_merge(self::META_KEYS, $except)));
    }
}
