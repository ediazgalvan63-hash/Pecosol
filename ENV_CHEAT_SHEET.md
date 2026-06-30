# 📋 CHEAT SHEET - Variables de Entorno Railway

> Copia y pega exacto. No agregues / al final de URLs.

---

## 🌐 SERVICIO: `pecosol-web`

### Environment Variables (Copiar en Railway)

```ini
# URL del API del Chatbot (IMPORTANTE)
CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat

# URL base de la web
APP_BASE_URL=https://tu-dominio-web.railway.app/

# Base de datos (obtén de MySQL en Railway)
DB_HOST=MYSQLHOST_AQUI
DB_PORT=3306
DB_DATABASE=MYSQLDATABASE_AQUI
DB_USERNAME=MYSQLUSER_AQUI
DB_PASSWORD=MYSQLPASSWORD_AQUI
```

---

## 🤖 SERVICIO: `pecosol-chatbot`

### Environment Variables (Copiar en Railway)

```ini
# CORS - Permite que la web acceda al chatbot
CHATBOT_ALLOWED_ORIGINS=https://tu-dominio-web.railway.app

# FastAPI Config
API_HOST=0.0.0.0
API_RELOAD=false
API_PORT=8000

# OpenAI API
OPENAI_API_KEY=sk-AQUI_TU_API_KEY_COMPLETA
OPENAI_MODEL=gpt-4o-mini

# Alternativa: Gemini (si usas Google)
GEMINI_API_KEY=AIza...

# Base de datos (MISMO que pecosol-web)
DB_HOST=MYSQLHOST_AQUI
DB_PORT=3306
DB_NAME=MYSQLDATABASE_AQUI
DB_USER=MYSQLUSER_AQUI
DB_PASSWORD=MYSQLPASSWORD_AQUI
```

---

## 🔍 Verificar Configuración

### 1. Obtener MySQL Variables
```
Railway → MySQL (servicio) → Connect → 
Copia: MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD
```

### 2. Verificar Servicio Web
```
Abre: https://tu-dominio-web.railway.app
Deberías: Acceder sin errores
```

### 3. Verificar Chatbot
```
Abre: https://pecosol-chatbot.railway.app/health
Respuesta esperada:
{
  "status": "healthy",
  "database": "connected",
  "gemini_configured": true
}
```

### 4. Verificar Integración
```
Abre: https://tu-dominio-web.railway.app/verify_chatbot.php
Todo debe mostrar ✅ PASS
```

---

## ⚠️ Errores Comunes

| Problema | Causa | Solución |
|----------|-------|----------|
| CORS Error | `CHATBOT_ALLOWED_ORIGINS` incorrecto | Sin `/` al final, exacto |
| 502 Bad Gateway | Servicio aún iniciando | Espera 2-3 min, revisa logs |
| Database failed | Variables MySQL incorrectas | Copia exactas de Railway |
| API Key invalid | Key expirada o incorrecta | Genera nueva en OpenAI |

---

## 🎯 Checklist Final

- [ ] Crear servicio `pecosol-chatbot` en Railway
- [ ] Agregar variables a `pecosol-web`
- [ ] Agregar variables a `pecosol-chatbot`
- [ ] Esperar deploy (2-3 min)
- [ ] Verificar `/health`
- [ ] Verificar `/verify_chatbot.php`
- [ ] Probar chatbot en la web
- [ ] ¡Listo! ✅

---

## 📞 Soporte Rápido

Si algo falla:
1. Abre: `https://tu-web.railway.app/verify_chatbot.php`
2. Lee el error
3. Busca en: `TROUBLESHOOTING_CHATBOT.md`
4. Aplica la solución

---

**Necesitas más?** → [SETUP_CHATBOT_RAILWAY.md](SETUP_CHATBOT_RAILWAY.md)
