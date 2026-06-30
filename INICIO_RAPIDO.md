# 🚀 INICIO RÁPIDO - CHATBOT IA BODESHOP

## ⏱️ 5 Minutos para Tener el Chatbot Funcionando

---

## Paso 1: Instalar (2 minutos)

Abre **PowerShell** en `C:\xampp\htdocs\bodeshop\` y ejecuta:

```powershell
composer install
```

Espera a que termine (verás líneas que dicen "Installing" y luego "generating autoload files").

---

## Paso 2: Obtener API Key (1 minuto)

1. Ve a: https://platform.openai.com/api-keys
2. Inicia sesión (o crea cuenta gratis)
3. Haz clic en **"Create new secret key"**
4. Copia la clave que te genera (ej: `sk-proj-abc123...`)
5. **Guarda esta clave** - la necesitarás en el siguiente paso

---

## Paso 3: Configurar API Key (1 minuto)

1. Abre el archivo: `config/openai.php`
2. Busca esta línea:
   ```php
   define('OPENAI_API_KEY', 'tu-api-key-aqui');
   ```
3. Reemplaza `'tu-api-key-aqui'` con tu clave real
4. Guarda el archivo

Ejemplo:
```php
define('OPENAI_API_KEY', 'sk-proj-abc123xyz789...');
```

---

## Paso 4: Verificar Instalación (1 minuto)

Abre tu navegador y ve a:
```
http://localhost/bodeshop/api/chatbot_debug.php
```

**Deberías ver:**
- ✅ PHP Version
- ✅ Database Connected
- ✅ ChatbotService Functional
- ✅ Todos los archivos presentes

Si todo está ✅, ¡continúa!

Si ves ❌, revisa `GUIA_CHATBOT.md` en la sección de troubleshooting.

---

## ¡LISTO! Usa el Chatbot

### Opción A: Con Interfaz Admin (Recomendado)

1. Inicia sesión en: `http://localhost/bodeshop/`
2. Haz clic en el botón **"🤖 Chatbot IA"** en el menú
3. ¡Escribe tu pregunta y presiona Enter!

### Opción B: Página de Test (Sin Login)

1. Ve a: `http://localhost/bodeshop/test_chatbot.php`
2. Escribe tu pregunta
3. ¡Obtén respuesta instantánea!

---

## 💬 Ejemplo de Primera Pregunta

**Escribe en el chatbot:**
```
¿Cuántos productos hay en stock?
```

**Respuesta esperada:**
```
Basándome en la información de tu base de datos, tienes 
[X] productos en total con un stock combinado de [Y] unidades.
El precio promedio es de S/. [Z]...
```

---

## ✨ Ejemplos de Preguntas

```
📦 Inventario:
   "¿Cuántos productos tengo?"
   "¿Qué productos tienen bajo stock?"
   
💰 Ventas:
   "¿Cuál fue el total de ventas hoy?"
   "Dame un resumen de las últimas 7 días"
   
👥 Empleados:
   "¿Cuántos empleados tenemos?"
   
📊 Análisis:
   "Analiza las tendencias de ventas"
   "¿Cuál es el producto más caro?"
```

---

## 🆘 Algo No Funciona

### Opción 1: Verificar Debug
```
http://localhost/bodeshop/api/chatbot_debug.php
```

### Opción 2: Ver si hay Errores en PowerShell

Abre PowerShell y ejecuta:
```powershell
cd c:\xampp\htdocs\bodeshop
php -r "
require 'config/openai.php';
echo 'API Key está: ' . (OPENAI_API_KEY === 'tu-api-key-aqui' ? 'NO CONFIGURADA ❌' : 'CONFIGURADA ✅');
"
```

### Opción 3: Leer la Guía Completa
```
GUIA_CHATBOT.md
```

---

## 📊 Información Importante

| Aspecto | Detalle |
|---------|---------|
| **Modelos** | gpt-4o-mini (recomendado) |
| **Velocidad** | 1-2 segundos por respuesta |
| **Costo** | ~$0.10-$0.50/mes típicamente |
| **Datos Accesibles** | Productos, ventas, empleados |
| **Seguridad** | API Key privada, datos valorizados |

---

## 🎯 Checklist Rápido

- [ ] `composer install` ejecutado
- [ ] API Key obtenida
- [ ] API Key configurada en `config/openai.php`
- [ ] Debug page muestra todo ✅
- [ ] Página de test funciona
- [ ] Chatbot en admin visible
- [ ] Primera pregunta respondida

---

## 📞 Próximos Pasos

1. **Explorar:** Haz muchas preguntas diferentes
2. **Personalizar:** Ajusta prompts en `ChatbotController.php`
3. **Monitorear:** Revisa costos en OpenAI
4. **Mejorar:** Agrega más datos o funcionalidades

---

## 🎉 ¡Felicidades!

Tu chatbot IA está 100% funcional. 

**Has completado la implementación de un sistema de IA en tu negocio.** 🚀

---

## 📚 Más Información

| Documento | Contenido |
|-----------|-----------|
| `GUIA_CHATBOT.md` | Guía completa en español (20+ páginas) |
| `RESUMEN_IMPLEMENTACION.md` | Resumen técnico |
| `COMANDOS_POWERSHELL.md` | Comandos útiles |
| `CHECKLIST_IMPLEMENTACION.md` | Verificación de implementación |
| `api/chatbot_debug.php` | Debug en navegador |
| `test_chatbot.php` | Test sin login |

---

**¿Preguntas?** Revisa los documentos incluidos o abre `api/chatbot_debug.php` para diagnosticar.

**¿Lista para usar?** ¡Adelante! 💪

---

*Implementado: 12 de noviembre de 2025*  
*Estado: ✅ Producción Lista*  
*Versión: 1.0*
