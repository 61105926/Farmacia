<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaNullValues
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
                $data['props'] = $this->deepSanitize($data['props']);
                $response->setContent(json_encode($data));
            }
        }
        
        return $response;
    }
    
    /**
     * Sanitización profunda para evitar valores null problemáticos
     */
    private function deepSanitize($data)
    {
        if ($data === null) {
            return [];
        }
        
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = $this->deepSanitize($value);
            }
            return $sanitized;
        }
        
        if (is_object($data)) {
            return $this->sanitizeObject($data);
        }
        
        // Manejar tipos problemáticos
        if (is_resource($data)) {
            return '';
        }
        
        if (is_callable($data)) {
            return '';
        }
        
        return $data;
    }
    
    /**
     * Sanitizar objetos específicamente
     */
    private function sanitizeObject($object)
    {
        if ($object === null) {
            return (object) [];
        }
        
        // Si es una Collection de Laravel
        if (method_exists($object, 'toArray')) {
            return $this->deepSanitize($object->toArray());
        }
        
        // Si es un objeto estándar
        $array = (array) $object;
        $sanitized = [];
        foreach ($array as $key => $value) {
            $sanitized[$key] = $this->deepSanitize($value);
        }
        return $sanitized;
    }
}
