# 🔧 Troubleshooting - Chatbot en Railway

> Guía rápida para resolver problemas del chatbot IA cuando está en Railway

---

## ❌ Error: "El servidor de chatbot aún no está disponible"

### Causa 1: El servicio del chatbot aún no existe en Railway

**Síntomas:**
- Ves el error en la web
- En Railway no hay servicio llamado "pecosol-chatbot"

**Solución:**
1. Ve a tu proyecto en railway.app
2. **Nuevo Servicio** → Deploy from GitHub
3. Selecciona el repo `pecosol`
4. En **Service Settings**, elige:
   - Dockerfile: `python_api/Dockerfile`
   - Name: `pecosol-chatbot`
5. Agrega las variables de entorno (ver abajo)
6. Deploy

---

## 🚫 Error: CORS - No tienes permiso para acceder

**Síntomas:**
```
Access to XMLHttpRequest has been blocked by CORS policy
```
En consola del navegador (F12 → Console)

**Causas:**
- `CHATBOT_ALLOWED_ORIGINS` no está configurada
- El dominio no coincide exactamente
- Hay espacios extras en la URL

**Solución:**

1. Ve al servicio `pecosol-chatbot` en Railway
2. **Environment** → Busca `CHATBOT_ALLOWED_ORIGINS`
3. Asegúrate que sea **exactamente** tu URL de web:
   ```
   ✅ CORRECTO:  https://pecosol-web.railway.app
   ❌ INCORRECTO: https://pecosol-web.railway.app/
   ❌ INCORRECTO: http://... (debe ser https)
   ❌ INCORRECTO: pecosol-web.railway.app (sin https://)
   ```
4. Guarda y redeploy

---

## 🔴 Error: 502 Bad Gateway

**Síntomas:**
- Ves "502 Bad Gateway" al abrir `/health`
- Logs muestran `Connection refused`

**Causas:**
- Servicio aún está iniciando
- Falta variable de entorno crítica
- Puerto no está configurado

**Solución:**

1. Espera 2-3 minutos a que termine el deploy
2. Verifica estas variables en Railway:
   ```
   API_HOST=0.0.0.0
   API_PORT=8000  (o no especificar)
   API_RELOAD=false
   ```
3. Ve a **Deployments** y mira los **Logs**
4. Si ves `ModuleNotFoundError`, revisa [Error: Python Packages](#error-python-packages)

---

## 🗄️ Error: Database Connection Failed

**Síntomas:**
```json
{
  "status": "unhealthy",
  "database": "disconnected"
}
```

**Causas:**
- Variables de MySQL incorrectas
- MySQL no está disponible
- Firewall de Railway

**Solución:**

1. En Railway, ve al servicio **MySQL**
2. Copia estas variables:
   - MYSQLHOST
   - MYSQLPORT
   - MYSQLDATABASE
   - MYSQLUSER
   - MYSQLPASSWORD

3. En servicio `pecosol-chatbot`, verifica que tengas:
   ```
   DB_HOST=<MYSQLHOST>
   DB_PORT=<MYSQLPORT>
   DB_NAME=<MYSQLDATABASE>
   DB_USER=<MYSQLUSER>
   DB_PASSWORD=<MYSQLPASSWORD>
   ```

4. Redeploy

---

## 🔑 Error: OpenAI API Key Invalid

**Síntomas:**
- Chatbot responde "Error: Invalid API Key"
- En logs: `Invalid API key provided`

**Causas:**
- Clave API expirada
- Clave copiada incorrectamente
- Cuenta de OpenAI sin fondos

**Solución:**

1. Ve a https://platform.openai.com/account/api-keys
2. Genera una **nueva API Key**
3. En Railway, servicio `pecosol-chatbot`:
   ```
   OPENAI_API_KEY=sk-... (completa)
   OPENAI_MODEL=gpt-4o-mini
   ```
4. Verifica no haya espacios extras
5. Redeploy

---

## 📦 Error: Python Packages

**Síntomas:**
```
ModuleNotFoundError: No module named 'mysql'
ModuleNotFoundError: No module named 'fastapi'
```

**Causa:**
- `requirements.txt` no se instaló bien
- Dockerfile tiene problemas

**Solución:**

Verifica que `python_api/requirements.txt` tenga:
```
fastapi==0.109.0
uvicorn[standard]==0.27.0
python-dotenv==1.0.0
mysql-connector-python==8.2.0
pydantic==2.5.0
openai==1.6.0
google-generativeai==0.3.0
```

Si falta algo, actualiza el archivo y redeploy.

---

## 🌐 El chatbot no aparece en la web

**Síntomas:**
- No ves el botón flotante 🤖
- O lo ves pero está gris/deshabilitado

**Causas:**
- `CHATBOT_API_URL` incorrecta
- Widget JavaScript no se cargó
- Página no está usando el widget

**Solución:**

1. En Railway, servicio `pecosol-web`, verifica:
   ```
   CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat
   ```

2. Abre tu web en navegador
3. Abre consola (F12 → Console)
4. Busca errores de JavaScript
5. Verifica que veas: "✅ Chatbot Widget inicializado"

Si no ves esto, el widget no se cargó. Contacta.

---

## 🧪 Verificación Rápida

Usa este script PHP para diagnosticar (en tu web):

```
https://tu-dominio-web.railway.app/verify_chatbot.php
```

Debería mostrar:
- ✅ Configuration: PASS
- ✅ API Reachability: PASS
- ✅ Database Connection: PASS

Si algo falla, aquí tendrás pistas.

---

## 📝 Checklist de Configuración

### Servicio `pecosol-web`
- [ ] APP_BASE_URL = https://tu-dominio-web.railway.app/
- [ ] CHATBOT_API_URL = https://pecosol-chatbot.railway.app/api/chat
- [ ] DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

### Servicio `pecosol-chatbot`
- [ ] CHATBOT_ALLOWED_ORIGINS = https://pecosol-web.railway.app
- [ ] API_HOST = 0.0.0.0
- [ ] API_RELOAD = false
- [ ] OPENAI_API_KEY = sk-...
- [ ] OPENAI_MODEL = gpt-4o-mini
- [ ] DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD

### MySQL
- [ ] Conectado y con datos
- [ ] Variables compartidas correctamente

---

## 📞 ¿Aún no funciona?

Comparte:
1. URL de tu web
2. URL de `/health` del chatbot
3. Captura de error en consola (F12)
4. Logs de Railway (Deployments → Logs)
5. Variables de entorno configuradas

Y podré ayudarte específicamente.
