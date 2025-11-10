@echo off
echo Iniciando servidores de desarrollo...
echo.
echo Servidor Laravel: http://localhost:8000
echo Servidor Vite: http://localhost:5173
echo.
echo Presiona Ctrl+C para detener los servicios
echo.

start "Laravel Server" cmd /k "php artisan serve"
timeout /t 2 /nobreak >nul
start "Vite Dev Server" cmd /k "npm run dev"

echo.
echo Servidores iniciados en ventanas separadas.
echo Cierra las ventanas para detener los servicios.

