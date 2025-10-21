<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInertiaData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Solo procesar respuestas de Inertia
        if ($response->headers->get('X-Inertia')) {
            $content = $response->getContent();
            $data = json_decode($content, true);
            
            if ($data && isset($data['props'])) {
                $data['props'] = $this->sanitizeData($data['props']);
                $response->setContent(json_encode($data));
            }
        }
        
        return $response;
    }
    
    /**
     * Sanitizar datos recursivamente para evitar valores null
     */
    private function sanitizeData($data)
    {
        if ($data === null) {
            return [];
        }
        
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = $this->sanitizeData($value);
            }
            return $sanitized;
        }
        
        if (is_object($data)) {
            return $this->sanitizeObject($data);
        }
        
        // Convertir valores problemáticos a strings seguros
        if (is_resource($data) || is_callable($data)) {
            return '';
        }
        
        return $data;
    }
    
    /**
     * Sanitizar objetos
     */
    private function sanitizeObject($object)
    {
        if ($object === null) {
            return (object) [];
        }
        
        // Si es una Collection de Laravel, convertir a array
        if (method_exists($object, 'toArray')) {
            return $this->sanitizeData($object->toArray());
        }
        
        // Si es un objeto estándar, convertir a array
        $array = (array) $object;
        $sanitized = [];
        foreach ($array as $key => $value) {
            $sanitized[$key] = $this->sanitizeData($value);
        }
        return $sanitized;
    }
}
