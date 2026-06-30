# 🔧 CONFIGURACIÓN URGENTE - Chatbot en Railway

## ❌ Problema Encontrado

**El servicio chatbot SÍ está corriendo** ✅ pero la variable de entorno no está configurada en Railway.

Configuración actual (LOCAL):
```
CHATBOT_API_URL = http://127.0.0.1:8000/api/chat  ❌ INCORRECTO EN RAILWAY
```

Configuración requerida (RAILWAY):
```
CHATBOT_API_URL = https://pecosol-chatbot.railway.app/api/chat  ✅ CORRECTO
```

---

## ✅ Solución Rápida (2 minutos)

### En Railway Dashboard:

1. **Abre tu proyecto**
   - Ve a: https://railway.app
   - Selecciona tu proyecto Pecosol

2. **Servicio 'pecosol-web'**
   - Haz clic en el servicio web
   - Ve a pestaña: **Variables**

3. **Agrega o edita:**
   ```
   Nombre:  CHATBOT_API_URL
   Valor:   https://pecosol-chatbot.railway.app/api/chat
   ```

4. **Guarda y Deploy**
   - El servicio hará redeploy automáticamente
   - Espera 1-2 minutos

5. **Prueba**
   - Abre tu web: `https://tu-dominio.railway.app`
   - Deberías ver botón 🤖
   - ¡Haz una pregunta!

---

## 📋 Verificación Completa

Para asegurarte que todo está correcto, verifica estas variables en Railway:

### En `pecosol-web`:
```env
APP_BASE_URL=https://tu-dominio-web.railway.app/
CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat
DB_HOST=<tu mysql host>
DB_PORT=3306
DB_DATABASE=<tu database>
DB_USERNAME=<tu user>
DB_PASSWORD=<tu password>
```

### En `pecosol-chatbot`:
```env
CHATBOT_ALLOWED_ORIGINS=https://tu-dominio-web.railway.app
API_HOST=0.0.0.0
API_RELOAD=false
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
DB_HOST=<tu mysql host>
DB_PORT=3306
DB_NAME=<tu database>
DB_USER=<tu user>
DB_PASSWORD=<tu password>
```

---

## 🧪 Cómo Verificar

Después de configurar, abre en navegador:
```
https://tu-dominio-web.railway.app/verify_chatbot.php
```

Deberías ver:
```json
{
  "chatbot_status": "✅ READY",
  "checks": {
    "config": {"status": "pass"},
    "api_reachable": {"status": "pass"},
    "cors": {"status": "pass"},
    "database": {"status": "pass"}
  }
}
```

---

## 🆘 Si Aún No Funciona

**Diagnosticar:**
```powershell
# En PowerShell local
$response = Invoke-WebRequest -Uri "https://pecosol-chatbot.railway.app/health" -UseBasicParsing
Write-Host $response.Content
```

**Debería retornar:**
```json
{
  "status": "healthy",
  "database": "connected"
}
```

Si no, revisa los logs en Railway Dashboard:
- Servicio `pecosol-chatbot` → Deployments → Logs

---

## 📞 Soporte Rápido

- ✅ Servicio chatbot: **CORRIENDO**
- ❌ Variable: **NO CONFIGURADA**
- ⏱️ Tiempo para solucionar: **2 minutos**
- 📁 Archivo de referencia: `ENV_CHEAT_SHEET.md`
- 🐛 Troubleshooting: `TROUBLESHOOTING_CHATBOT.md`

---

**Configuralo ahora y el chatbot funcionará inmediatamente.** ✅
