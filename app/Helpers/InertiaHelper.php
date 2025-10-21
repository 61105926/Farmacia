<?php

namespace App\Helpers;

class InertiaHelper
{
    /**
     * Sanitizar datos para evitar valores null que causen problemas con Inertia
     */
    public static function sanitizeData($data)
    {
        if ($data === null) {
            return [];
        }
        
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = self::sanitizeData($value);
            }
            return $sanitized;
        }
        
        if (is_object($data)) {
            return self::sanitizeObject($data);
        }
        
        // Convertir valores problemáticos a strings seguros
        if (is_resource($data) || is_callable($data)) {
            return '';
        }
        
        return $data;
    }
    
    /**
     * Sanitizar objetos para evitar valores null
     */
    public static function sanitizeObject($object)
    {
        if ($object === null) {
            return (object) [];
        }
        
        // Si es una Collection de Laravel, convertir a array
        if (method_exists($object, 'toArray')) {
            return self::sanitizeData($object->toArray());
        }
        
        // Si es un objeto estándar, convertir a array
        $array = (array) $object;
        $sanitized = [];
        foreach ($array as $key => $value) {
            $sanitized[$key] = self::sanitizeData($value);
        }
        return $sanitized;
    }
    
    /**
     * Sanitizar filtros de request
     */
    public static function sanitizeFilters($filters)
    {
        return array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
    }
    
    /**
     * Sanitizar roles para Inertia
     */
    public static function sanitizeRoles($roles)
    {
        return $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name ?? 'Sin nombre'
            ];
        });
    }
}
