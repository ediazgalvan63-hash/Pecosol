# ⚡ Comandos Rápidos para PowerShell

Esta guía contiene los comandos que necesitas para instalar y usar el chatbot.

## 📍 Abrir PowerShell en la Carpeta del Proyecto

1. Abre File Explorer
2. Navega a: `C:\xampp\htdocs\bodeshop\`
3. Haz clic en la barra de direcciones
4. Tipo: `powershell` y presiona Enter

O simplemente:
```powershell
cd c:\xampp\htdocs\bodeshop
```

---

## 🔧 Instalación Inicial

### Paso 1: Verificar que Tengas Todo

```powershell
# Verificar versión de PHP
php -v

# Verificar Composer
composer --version

# Verificar que XAMPP esté corriendo
# (MySQL y Apache deben estar en verde en XAMPP Control Panel)
```

### Paso 2: Instalar Dependencias

```powershell
# En la carpeta bodeshop
composer install

# Esto descarga la librería de OpenAI
```

Si hay problemas, intenta:
```powershell
composer install --no-cache
```

### Paso 3: Verificar la Instalación

```powershell
# Ir a la carpeta y verificar que vendor exista
ls vendor

# Verificar que OpenAI se instaló
ls vendor | grep openai
```

---

## 🧪 Testing

### Verificar que Todo Funcione

Abre tu navegador en:
```
http://localhost/bodeshop/api/chatbot_debug.php
```

Deberías ver:
- ✅ PHP Version
- ✅ Database Connected
- ✅ ChatbotService Functional
- ✅ Archivos del Chatbot (all present)

---

## 🔑 Gestionar tu API Key

### Opción 1: En el Archivo (Desarrollo Local)

```powershell
# Abre el archivo con el editor por defecto
notepad config/openai.php

# O con VS Code si tienes instalado
code config/openai.php
```

Luego reemplaza:
```php
define('OPENAI_API_KEY', 'tu-api-key-aqui');
```

Con tu clave de OpenAI.

### Opción 2: Variables de Entorno (Producción)

```powershell
# Ver variables de entorno actuales
Get-ChildItem -Path Env: | Select-Object Name, Value

# Crear variable de entorno permanente
[Environment]::SetEnvironmentVariable("OPENAI_API_KEY", "tu-clave-aqui", "User")

# Verificar que se creó
$env:OPENAI_API_KEY

# Nota: Debes reiniciar Apache/XAMPP después
```

---

## 🚀 Ejecutar el Chatbot

### Opción 1: Desde el Admin (Recomendado)

1. Inicia sesión en: `http://localhost/bodeshop/`
2. Haz clic en el botón **"🤖 Chatbot IA"**
3. ¡Escribe tu pregunta!

### Opción 2: Página de Test (Sin Login)

```powershell
# Simplemente abre en el navegador:
# http://localhost/bodeshop/test_chatbot.php
```

---

## 🐛 Troubleshooting por Comandos

### Revisar Logs de PHP

```powershell
# Ver los últimos 50 líneas del log de PHP
Get-Content -Path "C:\xampp\php\logs\php_error_log" -Tail 50

# O con Apache
Get-Content -Path "C:\xampp\apache\logs\error.log" -Tail 50
```

### Verificar que MySQL Esté Corriendo

```powershell
# Conectar a MySQL desde PowerShell
mysql -u root -h localhost

# Si funciona, verás el prompt mysql>
# Para salir, escribe: exit
```

### Reiniciar Apache

```powershell
# Detener Apache
net stop Apache2.4

# Iniciar Apache
net start Apache2.4
```

### Limpiar Caché de Composer

```powershell
composer clear-cache

composer install --no-cache
```

---

## 📁 Estructura de Carpetas (Verificar)

```powershell
# Verificar que existan todos los archivos

# Archivos creados
ls api/chatbot.php
ls api/chatbot_debug.php
ls config/openai.php
ls controllers/ChatbotController.php
ls models/ChatbotService.php
ls views/admin/chatbot.php
ls assets/css/chatbot.css
ls assets/js/chatbot.js

# Si todos existen, debería mostrar sin errores
```

---

## 🔍 Debug Paso a Paso

### 1. Verificar Configuración

```powershell
php -r "require 'config/openai.php'; echo OPENAI_API_KEY;"
```

### 2. Verificar Conexión a BD

```powershell
php -r "
require 'config/database.php';
try {
    \$db = Database::connect();
    echo 'Base de datos conectada!';
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"
```

### 3. Verificar Instalación de OpenAI

```powershell
php -r "
require 'vendor/autoload.php';
echo 'OpenAI Library Loaded Successfully!';
"
```

---

## 📊 Monitorear Uso de API

```powershell
# Ver la facturación de OpenAI (en tu navegador)
# Pero puedes obtener info vía API:

php -r "
require 'vendor/autoload.php';
\$client = new \OpenAI\Client('tu-api-key');
\$response = \$client->models()->list();
print_r(\$response);
"
```

---

## 🎯 Comandos de Productividad

### Editar Archivos Rápidamente

```powershell
# Editar config de OpenAI
code config/openai.php

# Editar el controlador
code controllers/ChatbotController.php

# Editar la vista
code views/admin/chatbot.php

# Editar CSS
code assets/css/chatbot.css

# Editar JavaScript
code assets/js/chatbot.js
```

### Ver Tamaño de Archivos

```powershell
ls -lh api/chatbot.php
ls -lh config/openai.php
ls -lh models/ChatbotService.php
```

### Buscar en Archivos

```powershell
# Buscar por API_KEY en todos los archivos
grep -r "API_KEY" .

# Buscar por "OpenAI" en controllers
grep -r "OpenAI" controllers/
```

---

## 🔄 Actualizar Dependencias

```powershell
# Ver versiones instaladas
composer show

# Actualizar todas las dependencias
composer update

# Actualizar solo OpenAI
composer update openai-php/client
```

---

## 💾 Backup y Restauración

```powershell
# Hacer backup del proyecto
Copy-Item -Path "." -Destination "bodeshop_backup_$(Get-Date -f yyyyMMdd)" -Recurse

# Copiar solo archivos importantes
Copy-Item -Path "config/", "controllers/", "models/", "views/", "assets/" -Destination "bodeshop_backup" -Recurse
```

---

## 🧹 Limpieza

```powershell
# Eliminar caché de Composer
composer clear-cache

# Eliminar node_modules si existen
rm -r node_modules

# Eliminar archivos de test después de ir a producción
rm test_chatbot.php
rm api/chatbot_debug.php
```

---

## 📝 Crear un Script de Instalación Automática

Copia esto en un archivo llamado `install.ps1`:

```powershell
# install.ps1 - Script de instalación automática

Write-Host "🚀 Instalando Chatbot IA para Bodeshop..." -ForegroundColor Green

# Paso 1: Composer Install
Write-Host "`n1️⃣ Instalando dependencias..." -ForegroundColor Yellow
composer install

# Paso 2: Verificar archivos
Write-Host "`n2️⃣ Verificando archivos..." -ForegroundColor Yellow
$files = @(
    "config/openai.php",
    "controllers/ChatbotController.php",
    "models/ChatbotService.php",
    "api/chatbot.php",
    "views/admin/chatbot.php",
    "assets/css/chatbot.css",
    "assets/js/chatbot.js"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ FALTA: $file" -ForegroundColor Red
    }
}

# Paso 3: Instrucciones finales
Write-Host "`n3️⃣ Instrucciones finales:" -ForegroundColor Yellow
Write-Host "   1. Obtén tu API Key en: https://platform.openai.com/api-keys"
Write-Host "   2. Abre config/openai.php"
Write-Host "   3. Reemplaza 'tu-api-key-aqui' con tu clave"
Write-Host "   4. ¡Listo! Accede a: http://localhost/bodeshop/"
Write-Host "`n✨ Instalación completada!" -ForegroundColor Green
```

Ejecutar:
```powershell
.\install.ps1
```

---

## 🆘 Pedir Ayuda

Si tienes problemas, ejecuta esto y guarda la salida:

```powershell
# Recopilar información de debug
Write-Host "=== INFORMACIÓN DE SISTEMA ===" 
php -v
composer --version

Write-Host "`n=== ESTRUCTURA DE ARCHIVOS ===" 
ls config/openai.php
ls controllers/ChatbotController.php
ls models/ChatbotService.php

Write-Host "`n=== ESTADO DE BD ===" 
php -r "
require 'config/database.php';
try {
    \$db = Database::connect();
    echo 'BD: OK';
} catch (Exception \$e) {
    echo 'BD ERROR: ' . \$e->getMessage();
}
"

Write-Host "`n=== ESTADO DE OPENAI ===" 
php config/openai.php
```

---

## 📚 Resumen de Comandos Importantes

```powershell
# Instalación
composer install

# Testing
# Abre: http://localhost/bodeshop/api/chatbot_debug.php

# Editar
code config/openai.php

# Logs
Get-Content -Path "C:\xampp\apache\logs\error.log" -Tail 50

# Reiniciar Apache
net stop Apache2.4
net start Apache2.4

# Ver estructura
ls -R config controllers models api views/admin assets
```

---

¡Ahora estás listo para usar el chatbot! 🎉

Cualquier duda, ejecuta el script de debug o revisa los logs.
