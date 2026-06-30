# 🚀 Configuración del Chatbot IA en Railway (PASO A PASO)

> ⚠️ **Estado Actual:** El servidor del chatbot en Railway NO está disponible. Sigue esta guía para activarlo.

---

## 📋 Requisitos Previos

- ✅ Proyecto Pecosol ya en Railway
- ✅ Servicio Web (`pecosol-web`) funcionando
- ✅ MySQL de Railway configurado
- ✅ Repositorio GitHub conectado a Railway

---

## 🔧 Paso 1: Crear el Servicio del Chatbot en Railway

### 1.1 En el Dashboard de Railway

1. Abre tu proyecto en **railway.app**
2. Ve a **NEW** (arriba a la derecha)
3. Selecciona **Deploy from GitHub repo**
4. Elige el repositorio **pecosol**

### 1.2 Configurar el Servicio

En la ventana de creación:
- **Nombre del servicio:** `pecosol-chatbot`
- **Service Settings** → Selecciona `python_api/Dockerfile`

O si usa template:
1. Haz clic en el nuevo servicio
2. Settings → **Dockerfile**
3. Ingresa: `python_api/Dockerfile`

---

## 🌍 Paso 2: Variables de Entorno del Chatbot

En Railway, ve a **Environment** del servicio `pecosol-chatbot` y agrega:

```env
# Base de datos (copia desde tu servicio MySQL de Railway)
DB_HOST=<MYSQLHOST>
DB_PORT=<MYSQLPORT>
DB_NAME=<MYSQLDATABASE>
DB_USER=<MYSQLUSER>
DB_PASSWORD=<MYSQLPASSWORD>

# API de IA (OpenAI o Gemini)
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini

# O si usas Gemini:
GEMINI_API_KEY=AIza...

# FastAPI Configuration
API_HOST=0.0.0.0
API_PORT=8000
API_RELOAD=false

# CORS - Permite que la web acceda al chatbot
CHATBOT_ALLOWED_ORIGINS=https://tu-dominio-web.railway.app

# Conexión a Base de Datos (si la incluyes en la URL)
DATABASE_URL=mysql+pymysql://<DB_USER>:<DB_PASSWORD>@<DB_HOST>:<DB_PORT>/<DB_NAME>
```

### 📌 Cómo obtener los datos de MySQL:

En tu servicio MySQL de Railway:
- Ve a **Connect**
- Copia los valores de:
  - `MYSQLHOST`
  - `MYSQLPORT`
  - `MYSQLDATABASE`
  - `MYSQLUSER`
  - `MYSQLPASSWORD`

---

## 🔐 Paso 3: Variables de Entorno del Servicio Web

Ve a **Environment** del servicio `pecosol-web` y verifica/actualiza:

```env
# URL del chatbot en Railway
CHATBOT_API_URL=https://tu-servicio-chatbot.railway.app/api/chat

# Tus variables existentes
APP_BASE_URL=https://tu-dominio-web.railway.app/
DB_HOST=<MYSQLHOST>
DB_PORT=<MYSQLPORT>
DB_DATABASE=<MYSQLDATABASE>
DB_USERNAME=<MYSQLUSER>
DB_PASSWORD=<MYSQLPASSWORD>
```

---

## ✅ Paso 4: Verificar que el Chatbot Funciona

### 4.1 Verificar Salud del Servicio

Abre en tu navegador:
```
https://tu-servicio-chatbot.railway.app/health
```

Deberías ver:
```json
{
  "status": "healthy",
  "database": "connected",
  "gemini_configured": true
}
```

### 4.2 Verificar CORS

Abre la consola del navegador (F12) en tu web:
- Deberías **NO ver** errores de CORS

Si ves `Access-Control-Allow-Origin` error:
1. Revisa que `CHATBOT_ALLOWED_ORIGINS` sea exacto
2. Verifica que no haya espacios extra

---

## 🐛 Paso 5: Solución de Problemas

### Error: "El servidor de chatbot no está disponible"

**Causas posibles:**

| Síntoma | Solución |
|---------|----------|
| El servicio está "Building" | Espera a que termine el deploy |
| Error 502/503 | Revisa logs en Railway |
| CORS Error | Verifica `CHATBOT_ALLOWED_ORIGINS` |
| DB Connection Error | Verifica credenciales de MySQL |
| API Key inválida | Verifica `OPENAI_API_KEY` |

### Ver Logs del Servicio

En Railway:
1. Selecciona servicio `pecosol-chatbot`
2. Ve a **Deployments**
3. Haz clic en el último deploy
4. Mira **Logs**

Busca errores como:
```
ModuleNotFoundError: No module named 'mysql'
ConnectionError: Can't connect to MySQL
```

---

## 📝 Verificación Final

Cuando todo esté listo:

1. ✅ Ve a tu web en Railway
2. ✅ Inicia sesión
3. ✅ Deberías ver el botón flotante 🤖 (abajo a la derecha)
4. ✅ Haz clic y prueba: "¿Cuántos productos tenemos?"
5. ✅ El chatbot debe responder con datos reales

---

## 🔍 URL para Verificar

```
Chatbot API: https://tu-servicio-chatbot.railway.app/health
Documentación API: https://tu-servicio-chatbot.railway.app/docs
Tu web: https://tu-dominio-web.railway.app
```

---

## ⚡ Si Aún No Funciona

1. **Revisa los logs del chatbot** en Railway (Deployments → Logs)
2. **Verifica MySQL conecta** ejecutando en terminal Railway
3. **Comprueba que OpenAI key es válida**
4. **Mira la consola del navegador** (F12 → Console) para errores CORS

---

**¿Necesitas ayuda?** Comparte:
- URL del chatbot (`/health`)
- Errores de la consola (F12)
- Logs de Railway
