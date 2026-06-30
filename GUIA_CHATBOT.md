# 🤖 Guía Completa: Chatbot IA en Bodeshop

## 📋 Tabla de Contenidos
1. [Introducción](#introducción)
2. [Requisitos Previos](#requisitos-previos)
3. [Pasos de Instalación](#pasos-de-instalación)
4. [Configuración de OpenAI](#configuración-de-openai)
5. [Uso del Chatbot](#uso-del-chatbot)
6. [Solución de Problemas](#solución-de-problemas)
7. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## Introducción

Se ha implementado un **chatbot inteligente con acceso a la base de datos de Bodeshop** directamente en el panel de administración. El chatbot utiliza **GPT-4o Mini de OpenAI** para procesar preguntas y proporcionar análisis contextualizados sobre:

- 📦 Inventario y productos
- 💰 Ventas y estadísticas
- 👥 Empleados
- 📊 Análisis de negocio

---

## Requisitos Previos

✅ **XAMPP** instalado y funcionando
✅ **PHP 7.4+** con soporte para cURL
✅ **Composer** instalado (para gestionar dependencias)
✅ **Conexión a Internet** (para comunicarse con OpenAI)
✅ **Cuenta en OpenAI** (gratuita o de pago)

---

## Pasos de Instalación

### Paso 1️⃣: Verificar que Todo Esté en Orden

Abre PowerShell y ejecuta:

```powershell
cd c:\xampp\htdocs\bodeshop
php -v
composer --version
```

Deberías ver las versiones de PHP y Composer. Si no, instálalos.

### Paso 2️⃣: Instalar Dependencias de Composer

```powershell
composer install
```

Esto instalará la librería de OpenAI y otras dependencias necesarias.

**¿Qué esperar?**
- Se creará/actualizará la carpeta `vendor/`
- Se descargarán los paquetes automáticamente
- Debería completarse en 1-2 minutos

### Paso 3️⃣: Obtener tu API Key de OpenAI

1. Abre tu navegador: https://platform.openai.com/api-keys
2. Inicia sesión (o crea una cuenta gratuita)
3. Haz clic en **"Create new secret key"**
4. Copia la clave (ejemplo: `sk-proj-abc123...xyz`)
5. **GUARDA ESTA CLAVE EN UN LUGAR SEGURO**

⚠️ **Importante:**
- No compartas tu API Key
- No la hagas pública
- No la incluyas en controladores de versiones (Git)

### Paso 4️⃣: Configurar la API Key en tu Proyecto

**Opción A: Desarrollo Local (Rápido)**

1. Abre el archivo: `config/openai.php`
2. Busca esta línea:
   ```php
   define('OPENAI_API_KEY', 'tu-api-key-aqui');
   ```
3. Reemplázala con tu clave real:
   ```php
   define('OPENAI_API_KEY', 'sk-proj-tutuclaveaquí...');
   ```
4. Guarda el archivo

**Opción B: Producción (Seguro con Variables de Entorno)**

1. Abre las **Variables de Entorno de Windows**:
   - Panel de Control → Sistema → Configuración avanzada del sistema
   - Variables de entorno → Nueva variable de usuario
   
2. Crea una nueva variable:
   - Nombre: `OPENAI_API_KEY`
   - Valor: Tu API Key

3. Reinicia XAMPP o Apache

4. El código lee automáticamente: `getenv('OPENAI_API_KEY')`

### Paso 5️⃣: Verificar que Todo Funciona

Antes de usar el chatbot, haz un test rápido:

1. Abre: `http://localhost/bodeshop/api/chatbot_debug.php`
2. Verifica que todos los componentes muestren ✅
3. Si hay ❌, revisa los errores

---

## Configuración de OpenAI

### Entender el archivo `config/openai.php`

```php
<?php
// Aquí es donde se define tu API Key
define('OPENAI_API_KEY', 'tu-clave-aquí');

// Aquí se elige qué modelo usar
define('OPENAI_MODEL', 'gpt-4o-mini');
```

### Modelos Disponibles

| Modelo | Velocidad | Precisión | Costo |
|--------|-----------|-----------|-------|
| `gpt-3.5-turbo` | ⚡⚡⚡ Muy rápido | ⭐⭐⭐ Buena | 💰 Muy económico |
| `gpt-4o-mini` | ⚡⚡ Rápido | ⭐⭐⭐⭐ Excelente | 💰 Económico |
| `gpt-4` | ⚡ Lento | ⭐⭐⭐⭐⭐ Perfecto | 💰💰💰 Costoso |

**Recomendación para Bodeshop:** `gpt-4o-mini` (perfecto balance)

---

## Uso del Chatbot

### Acceso

1. **Inicia sesión** en el admin: `http://localhost/bodeshop/`
2. **Haz clic en** el botón **"🤖 Chatbot IA"** en la barra de navegación
3. **Escribe tu pregunta** y presiona Enter o haz clic en Enviar

### Ejemplos de Preguntas

```
✅ "¿Cuántos productos hay en total?"
✅ "¿Cuál fue el total de ventas hoy?"
✅ "Dame las ventas de los últimos 7 días"
✅ "¿Qué productos tienen bajo stock?"
✅ "¿Cuál es el promedio de precio de los productos?"
✅ "Dame un resumen del inventario"
✅ "¿Quiénes son nuestros empleados?"
✅ "Analiza las tendencias de ventas"
```

### Características de la Interfaz

| Elemento | Función |
|----------|---------|
| 💬 Panel de mensajes | Ver conversación |
| 📝 Campo de entrada | Escribir preguntas |
| 🤖 Indicador de escritura | El bot está pensando |
| 🕐 Timestamps | Hora de cada mensaje |
| 📋 Panel de información | Datos disponibles |

### Historial de Chat

- El chat se **guarda automáticamente** en tu navegador
- Cuando vuelvas, verás las conversaciones anteriores
- Solo se guarda en tu computadora (no en servidor)

---

## Solución de Problemas

### ❌ Problema: "OPENAI_API_KEY no está configurada"

**Causas:**
- Olvidaste agregar tu API Key
- Hay un typo en la clave
- El archivo `config/openai.php` no existe

**Solución:**
1. Verifica que `config/openai.php` exista
2. Abre el archivo y busca `'tu-api-key-aqui'`
3. Reemplázalo con tu clave real (sin comillas adicionales)
4. Guarda y recarga la página

---

### ❌ Problema: "Error de conexión"

**Causas:**
- Sin conexión a internet
- API Key inválida
- Límite de API excedido

**Solución:**
1. Verifica tu conexión a internet
2. Comprueba que la API Key sea correcta en:
   https://platform.openai.com/api-keys
3. Revisa tu límite de uso en:
   https://platform.openai.com/account/billing/overview

---

### ❌ Problema: "Composer install no funciona"

**Causas:**
- PHP no está en el PATH
- Composer no está instalado
- Problema de permisos

**Solución:**
```powershell
# Verifica que PHP esté disponible
php -v

# Verifica que Composer esté disponible
composer --version

# Si no, instala Composer desde:
# https://getcomposer.org/download/
```

---

### ❌ Problema: El chatbot devuelve "Método no permitido"

**Causas:**
- El archivo `api/chatbot.php` no existe
- El servidor no permite POST

**Solución:**
1. Verifica que exista `api/chatbot.php`
2. Abre la consola (F12) y mira la pestaña "Network"
3. Verifica que la solicitud sea POST
4. Revisa los errores de Apache en `xampp/apache/logs/error.log`

---

### ❌ Problema: La base de datos no se conecta

**Causas:**
- MySQL no está corriendo
- Datos de conexión incorrectos
- Base de datos no existe

**Solución:**
1. Inicia MySQL desde XAMPP Control Panel
2. Verifica `config/database.php` tiene los datos correctos
3. Verifica que la base de datos `bodeshop_db` exista
4. Ejecuta `bodeshop_db.sql` si es necesario

---

## Preguntas Frecuentes

### 🤔 ¿Es seguro usar la API de OpenAI?

**Sí**, siempre y cuando:
- ✅ Uses variables de entorno para la API Key
- ✅ No incluyas la clave en Git
- ✅ Valides todas las entradas del usuario
- ✅ Uses HTTPS en producción

---

### 🤔 ¿Cuánto me costará usar el chatbot?

**Muy económico:**
- GPT-4o Mini: ~$0.15 por 1M tokens
- Estimación: **< $1/mes** para una pequeña tienda
- Puedes ver todos los costos en:
  https://platform.openai.com/account/billing/overview

---

### 🤔 ¿Puedo usar otros modelos de OpenAI?

**Sí**, cambia en `config/openai.php`:

```php
// Para usar GPT-3.5 (más rápido, menos preciso)
define('OPENAI_MODEL', 'gpt-3.5-turbo');

// Para usar GPT-4 (más preciso, más lento)
define('OPENAI_MODEL', 'gpt-4');
```

---

### 🤔 ¿Dónde se guardan las conversaciones?

- **En tu navegador**: localStorage
- **En el servidor**: no se guardan por defecto
- Puedes implementar guardar en BD si necesitas

---

### 🤔 ¿Funciona sin conexión a internet?

**No**, el chatbot necesita internet porque:
- Debe conectarse con servidores de OpenAI
- Requiere consultar la API en tiempo real

---

### 🤔 ¿Puedo usar esto en producción?

**Sí, pero:**
1. ✅ Usa variables de entorno (no hardcoded)
2. ✅ Implementa autenticación
3. ✅ Usa HTTPS
4. ✅ Agrega rate limiting
5. ✅ Valida todas las entradas
6. ✅ Monitora los costos de API

---

## 📞 Recursos Útiles

- **Documentación de OpenAI:** https://platform.openai.com/docs
- **Modelos disponibles:** https://platform.openai.com/docs/models
- **Estado del sistema:** https://status.openai.com
- **SDK PHP de OpenAI:** https://github.com/openai-php/client

---

## ✅ Checklist Final

Antes de usar en producción:

- [ ] API Key configurada correctamente
- [ ] Composer install ejecutado
- [ ] Acceso al chatbot funciona
- [ ] Base de datos consultable
- [ ] Respuestas coherentes
- [ ] Variables de entorno configuradas
- [ ] HTTPS habilitado
- [ ] Rate limiting implementado
- [ ] Logs monitoreados

---

## 🎉 ¡Listo!

Tu chatbot está completamente funcional. **Comienza a hacer preguntas y descubre el poder del análisis impulsado por IA para tu negocio Bodeshop.**

---

**¿Necesitas ayuda?** Revisa los archivos de debug:
- `api/chatbot_debug.php` - Información del sistema
- `test_chatbot.php` - Test sin interfaz gráfica

**¿Encontraste un error?** Revisa:
- Consola del navegador (F12)
- Logs de PHP
- `chatbot_debug.php`

¡Que disfrutes del chatbot! 🚀
