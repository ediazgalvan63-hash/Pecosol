# ⚡ CHATBOT EN RAILWAY - CHECKLIST RÁPIDO

## El Problema

El chatbot funciona perfecto en **local**, pero en **Railway** muestra:
```
Error inesperado
No se pudo conectar con ningún endpoint del chatbot
```

---

## La Solución (3 pasos, 5 minutos)

### ✅ PASO 1: Verifica el Diagnóstico

1. Abre en tu navegador (en la web de Railway):
   ```
   https://TU-DOMINIO.railway.app/chatbot_diagnostic.php
   ```

2. Ve qué error muestra:
   - ❌ **"CHATBOT_API_URL: NO DEFINIDA"** → Ve al Paso 2
   - ❌ **"No accesible"** → El servicio chatbot está roto (ve a Paso 3)
   - ✅ **"Accesible"** → Listo, funciona

---

### ✅ PASO 2: Configura en Railway (si muestra "NO DEFINIDA")

1. Abre **https://railway.app**
2. Selecciona tu proyecto **Pecosol**
3. Haz clic en servicio **`pecosol-web`**
4. Ve a pestaña **"Variables"**
5. **Crea o edita:**
   ```
   Nombre:  CHATBOT_API_URL
   Valor:   https://pecosol-chatbot.railway.app/api/chat
   ```
   
   ⚠️ **Reemplaza `pecosol-chatbot` con el nombre EXACTO de tu servicio**

6. Haz clic **"Save Variables"**
7. Espera el Deploy (1-2 minutos)
8. Actualiza el diagnóstico

---

### ✅ PASO 3: Si el Servicio Está Roto

Si `chatbot_diagnostic.php` muestra **"No accesible"**:

1. Ve a tu proyecto en Railway
2. Abre servicio **`pecosol-chatbot`**
3. Ve a pestaña **"Logs"** y busca errores
4. Verifica que tenga **estas variables configuradas:**

   ```
   # Base de datos (IGUAL a pecosol-web)
   DB_HOST=mysql.railway.internal
   DB_PORT=3306
   DB_NAME=railway
   DB_USER=root
   DB_PASSWORD=<tu-password>
   
   # OpenAI
   OPENAI_API_KEY=sk-<tu-api-key>
   OPENAI_MODEL=gpt-4o-mini
   
   # FastAPI
   API_HOST=0.0.0.0
   API_PORT=8000
   API_RELOAD=false
   
   # CORS
   CHATBOT_ALLOWED_ORIGINS=https://TU-DOMINIO-WEB.railway.app
   ```

5. Si falta algo, agrégalo y guarda
6. Espera el Deploy

---

## Verificación

Después de configurar, abre tu web:
```
https://TU-DOMINIO.railway.app
```

1. Busca el botón 🤖 (abajo a la derecha)
2. Haz clic
3. Pregunta: **"¿Cuántos productos tenemos?"**
4. El chatbot debe responder con datos reales

---

## ¿Aún No Funciona?

### Revisa estos puntos:

| Síntoma | Causa | Solución |
|---------|-------|----------|
| "No se pudo conectar" | CHATBOT_API_URL no definida | Ve a Paso 2 |
| "No se pudo conectar" | Servicio chatbot roto | Ve a Paso 3 |
| Error CORS | CHATBOT_ALLOWED_ORIGINS incorrecto | Ve a Paso 3, revisa CORS |
| Error en chatbot log | Credenciales de BD incorrectas | Revisa DB_HOST, DB_USER, DB_PASSWORD |
| Error OpenAI | API key inválida | Verifica tu OPENAI_API_KEY |

---

## URLs Útiles

- **Diagnóstico:** `https://TU-DOMINIO.railway.app/chatbot_diagnostic.php`
- **Verificador API:** `https://TU-DOMINIO.railway.app/verify_chatbot.php`
- **Health del chatbot:** `https://pecosol-chatbot.railway.app/health`

---

## 🎯 Resumen de Cambios

El código ya está listo. Solo necesitas:

1. ✅ **PHP:** Ya existe `verify_chatbot.php` y `chatbot_diagnostic.php`
2. ✅ **Config:** Detecta automáticamente `CHATBOT_API_URL`
3. ✅ **JS:** El widget ya busca la variable y se conecta
4. ❌ **Railway:** Configura la variable de entorno

**Es decir: Solo tienes que copiar-pegar una variable en Railway. ¡Eso es todo!**

---

## Alternativa: Script PowerShell

Si quieres una guía interactiva, ejecuta:
```powershell
cd c:\xampp\htdocs\pecosol
.\scripts\fix_chatbot_railway.ps1
```

Este script te mostrará exactamente qué configurar en Railway.

---

**¿Necesitas ayuda?** Revisa la consola del navegador (F12) para ver si hay errores CORS o de conexión.
