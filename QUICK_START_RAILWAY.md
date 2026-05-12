# ⚡ QUICK START - Chatbot en Railway

> Guía ultra rápida para que funcione el chatbot en Railway

---

## 📋 Checklist (5 minutos)

### ✅ Paso 1: En Railway Dashboard

**Crear nuevo servicio `pecosol-chatbot`:**

```
NEW → Deploy from GitHub → 
  Repository: pecosol
  Name: pecosol-chatbot
  Dockerfile: python_api/Dockerfile
  CREATE
```

**Espera a que termine el deploy (2-3 minutos)**

---

### ✅ Paso 2: Variables de Entorno

**En `pecosol-web` → Environment:**

```
CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat
```

---

**En `pecosol-chatbot` → Environment:**

```
CHATBOT_ALLOWED_ORIGINS=https://pecosol-web.railway.app
API_HOST=0.0.0.0
API_RELOAD=false

OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

DB_HOST=<mysql-host>
DB_PORT=3306
DB_NAME=<mysql-database>
DB_USER=<mysql-user>
DB_PASSWORD=<mysql-password>
```

(Copia valores de MySQL de Railway)

---

### ✅ Paso 3: Verificar

**En navegador:**

```
https://pecosol-chatbot.railway.app/health
```

**Debe retornar:**
```json
{
  "status": "healthy",
  "database": "connected"
}
```

---

### ✅ Paso 4: Probar

**Abre tu web:**

```
https://tu-dominio-web.railway.app
```

**Deberías ver:**
- Botón flotante 🤖 (abajo derecha)
- Abre y prueba: "¿Cuántos productos hay?"

---

## 🚨 Si Algo Falla

### Error: "Servidor no disponible"
- Espera 2-3 minutos a que termine el deploy
- Revisa logs: Railway → pecosol-chatbot → Deployments → Logs

### Error: CORS
- Verifica `CHATBOT_ALLOWED_ORIGINS` sea exacto (sin / al final)
- Redeploy: Railway → Deploy History → Redeploy

### Error: 502 Bad Gateway
- Ve a Railway → Logs
- Busca `ModuleNotFoundError` o errores de conexión

---

## 🔍 Diagnosticar Rápido

**URL de salud:**
```
https://pecosol-chatbot.railway.app/health
```

**Página de diagnóstico:**
```
https://tu-web.railway.app/verify_chatbot.php
```

---

## 💾 Guardar Configuración

```powershell
cd c:\xampp\htdocs\pecosol
.\scripts\deploy_chatbot_to_railway.ps1
```

Te guiará y generará las variables.

---

## 📚 Más Detalles

- 📖 [`SETUP_CHATBOT_RAILWAY.md`](SETUP_CHATBOT_RAILWAY.md) - Guía completa
- 🐛 [`TROUBLESHOOTING_CHATBOT.md`](TROUBLESHOOTING_CHATBOT.md) - Resolver errores
- 📋 [`INDICE_DOCUMENTACION.md`](INDICE_DOCUMENTACION.md) - Todas las guías

---

**¿Listo? ¡Comienza con Paso 1!** 🚀
