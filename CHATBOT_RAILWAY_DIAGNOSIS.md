# 🔍 Diagnóstico del Chatbot en Railway

## ❌ Problema Actual

El error que ves es:
```
Error inesperado
No se pudo conectar con ningún endpoint del chatbot
```

Esto significa que la variable `CHATBOT_API_URL` **NO está configurada** en Railway o **el servicio chatbot no es accesible**.

---

## ✅ Solución Rápida (5 minutos)

### Paso 1: Verifica el Diagnóstico
Abre en tu navegador (en la web de Railway):
```
https://TU-DOMINIO.railway.app/verify_chatbot.php
```

Deberías ver algo como:
```json
{
  "chatbot_status": "❌ FAILED",
  "checks": {
    "config": {
      "status": "fail",
      "details": {
        "CHATBOT_API_URL": "NOT DEFINED"
      }
    }
  }
}
```

Si ves esto, significa que **`CHATBOT_API_URL` no está configurada en Railway**.

---

### Paso 2: Configura en Railway

1. **Abre Railway Dashboard**: https://railway.app

2. **En tu proyecto Pecosol**, ve al servicio **`pecosol-web`**

3. **Ve a la pestaña "Variables"** (Environment)

4. **Agrega o edita esta variable:**
   ```
   Nombre:  CHATBOT_API_URL
   Valor:   https://pecosol-chatbot.railway.app/api/chat
   ```
   
   ⚠️ **IMPORTANTE**: Reemplaza `pecosol-chatbot` con el nombre EXACTO de tu servicio chatbot en Railway.

5. **Guarda y espera a que haga Deploy** (1-2 minutos)

---

### Paso 3: Verifica que Funciona

Abre nuevamente:
```
https://TU-DOMINIO.railway.app/verify_chatbot.php
```

Ahora deberías ver:
```json
{
  "chatbot_status": "✅ READY",
  "checks": {
    "config": {
      "status": "pass",
      "details": {
        "CHATBOT_API_URL": "https://pecosol-chatbot.railway.app/api/chat"
      }
    },
    "api_reachable": {
      "status": "pass"
    }
  }
}
```

---

## 🐍 Verifica el Servicio Chatbot en Railway

Si `verify_chatbot.php` sigue mostrando **`api_reachable: FAILED`**, entonces el servicio Python **NO está corriendo**.

**Solución:**
1. En Railway, ve al servicio **`pecosol-chatbot`**
2. Verifica que **esté en estado "Running"** (no "Crashed" o "Deploying")
3. Si está roto, revisa los logs
4. Si no existe el servicio, créalo desde `python_api/Dockerfile`

---

## 🔧 Variables de Entorno Requeridas

### En el servicio `pecosol-web`:
```env
CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat
# (las demás ya deberían estar: DB_HOST, DB_PORT, etc.)
```

### En el servicio `pecosol-chatbot`:
```env
# Base de datos (MISMO que pecosol-web)
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=<tu-password>

# IA (OpenAI)
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

# FastAPI Config
API_HOST=0.0.0.0
API_PORT=8000
API_RELOAD=false

# CORS - Permite que la web acceda al chatbot
CHATBOT_ALLOWED_ORIGINS=https://TU-DOMINIO-WEB.railway.app
```

---

## 📊 Comparación: Local vs Railway

| Aspecto | Local | Railway |
|---------|-------|---------|
| **URL del chatbot** | `http://127.0.0.1:8000/api/chat` | `https://pecosol-chatbot.railway.app/api/chat` |
| **Variable de entorno** | Automática (detecta localhost) | **DEBE estar explícita en config** |
| **Base de datos** | localhost | `mysql.railway.internal` |
| **OpenAI key** | (local) | Debe estar en servicio chatbot |
| **Estado** | ✅ Funciona | ❌ Necesita configuración |

---

## 🧪 Prueba Directa del Chatbot en Railway

Para verificar que el servicio Python está corriendo:
```
https://pecosol-chatbot.railway.app/health
```

Deberías ver:
```json
{
  "status": "healthy",
  "database": "connected",
  "service": "Pecosol Chatbot API"
}
```

Si ves error 404 o timeout → **El servicio chatbot NO está disponible en Railway**.

---

## 📞 Si Aún No Funciona

1. **Verifica el nombre exacto del servicio chatbot en Railway**
   - Puede ser `pecosol-chatbot`, `chatbot`, `python-api`, etc.
   - El nombre debe ir en la URL: `https://NOMBRE-EXACTO.railway.app/api/chat`

2. **Revisa los logs del servicio chatbot**
   - Railway → tu proyecto → servicio chatbot → Logs
   - Busca errores de importación o conexión

3. **Verifica que MySQL esté accesible**
   - El servicio chatbot NO puede conectar a la BD
   - Verifícalo en los logs

4. **Comprueba el OpenAI API key**
   - ¿Es válido?
   - ¿Tiene créditos?

---

## ✨ Cuando Todo Funcione

1. Abre tu web en Railway
2. Haz clic en el botón 🤖 (abajo a la derecha)
3. Pregunta: "¿Cuántos productos tenemos?"
4. El chatbot debe responder con datos reales de tu BD

**¡Listo!** 🎉
