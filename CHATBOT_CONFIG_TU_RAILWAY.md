# 🎯 CONFIGURACIÓN PARA TU RAILWAY

## Tu Información

- **Web:** https://virtuous-energy-production-d0c4.up.railway.app
- **Chatbot:** https://pecosol-chatbot-production.up.railway.app
- **API Chatbot:** https://pecosol-chatbot-production.up.railway.app/api/chat

---

## ✅ PASO A PASO EN RAILWAY

### 1. Abre https://railway.app

### 2. Ve a tu proyecto Pecosol

### 3. Haz clic en el servicio `pecosol-web` (tu aplicación PHP)

### 4. Ve a la pestaña **"Variables"** (Environment)

### 5. **CREA O EDITA** esta variable:

```
Nombre:  CHATBOT_API_URL
Valor:   https://pecosol-chatbot-production.up.railway.app/api/chat
```

### 6. Haz clic en **"Save Variables"**

### 7. Espera el Deploy automático (1-2 minutos) ⏳

---

## ✔️ VERIFICACIÓN

Una vez guardado, abre en tu navegador:

```
https://virtuous-energy-production-d0c4.up.railway.app/chatbot_diagnostic.php
```

Deberías ver:
- ✅ `CHATBOT_API_URL: https://pecosol-chatbot-production.up.railway.app/api/chat`
- ✅ `api_reachable: PASS`

---

## 🧪 PRUEBA FINAL

Si ves ✅ READY en el diagnóstico:

1. Abre tu web: https://virtuous-energy-production-d0c4.up.railway.app
2. Haz clic en el botón 🤖 (abajo a la derecha)
3. Pregunta: "¿Cuántos productos tenemos?"
4. El chatbot debe responder con datos reales

---

## ⚠️ SI NO FUNCIONA

Si el diagnóstico muestra que no está accesible:

**Verifica el servicio chatbot en Railway:**
1. Ve a Railway → tu proyecto
2. Servicio `pecosol-chatbot-production` (o similar)
3. Revisa la pestaña **"Logs"**
4. Busca errores de conexión a base de datos o OpenAI

**Variables requeridas en el servicio chatbot:**
```
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_NAME=railway
DB_USER=root
DB_PASSWORD=<tu-password>
OPENAI_API_KEY=sk-<tu-key>
OPENAI_MODEL=gpt-4o-mini
API_HOST=0.0.0.0
API_PORT=8000
API_RELOAD=false
CHATBOT_ALLOWED_ORIGINS=https://virtuous-energy-production-d0c4.up.railway.app
```

---

**¡Listo! Solo copia-pega la variable en Railway y espera el Deploy.** 🚀
