# 📋 Scripts de Utilidad - Pecosol Chatbot

## 📁 Scripts Disponibles

### 1. **`deploy_chatbot_to_railway.ps1`** 🚀 (NEW!)

**Propósito:** Guía interactiva para configurar el chatbot en Railway

**Uso:**
```powershell
cd c:\xampp\htdocs\pecosol
.\scripts\deploy_chatbot_to_railway.ps1
```

**O con parámetros:**
```powershell
.\scripts\deploy_chatbot_to_railway.ps1 `
  -WebServiceUrl "https://pecosol-web.railway.app" `
  -OpenAIApiKey "sk-..."
```

**Qué hace:**
- Verifica que estés en la raíz del proyecto
- Te pide URLs de servicio web y chatbot
- Genera variables de entorno correctas
- Te guía paso a paso por Railway
- Opcionalmente guarda config en `.env.railway.local`

**Requisitos:**
- PowerShell 5.1+ (incluido en Windows 10+)
- Estar en la raíz del proyecto Pecosol

---

### 2. **`deploy_railway.ps1`** 🌐

**Propósito:** Deploy automático a Railway con control de versiones

**Uso:**
```powershell
.\scripts\deploy_railway.ps1 -WebServiceId "..." -ChatbotServiceId "..."
```

**Parámetros:**
- `-WebServiceId`: ID del servicio web en Railway (ej: `srv_123`)
- `-ChatbotServiceId`: ID del servicio chatbot en Railway
- `-CommitMessage`: Mensaje custom para git (opcional)

---

## 🆚 Diferencia entre Scripts

| Feature | `deploy_chatbot_to_railway.ps1` | `deploy_railway.ps1` |
|---------|----------------------------------|-------------------|
| **Objetivo** | Configurar variables de entorno | Deploy de código |
| **Interactivo** | ✅ Sí | ❌ No |
| **Requiere IDs** | ❌ No | ✅ Sí |
| **Cambia código** | ❌ No | ✅ Sí |
| **Genera config** | ✅ Sí | ❌ No |
| **Para principiantes** | ✅ Mejor | ❌ Avanzado |

---

## 🧪 Ejemplo Completo

```powershell
# Paso 1: Ejecutar configurador
.\scripts\deploy_chatbot_to_railway.ps1

# Te pedirá:
# - URL de servicio web: https://pecosol-web.railway.app
# - URL de servicio chatbot: https://pecosol-chatbot.railway.app
# - OpenAI API Key: sk-...

# El script te mostrará qué variables agregar a Railway Dashboard

# Paso 2: Ve a Railway Dashboard y configura manualmente

# Paso 3: Verifica que funcione
# https://tu-dominio.railway.app/verify_chatbot.php

# Paso 4: Si todo está bien
.\scripts\deploy_railway.ps1 -WebServiceId "srv_xxx" -ChatbotServiceId "srv_yyy"
```

---

## 🔧 Troubleshooting de Scripts

### Error: "El archivo no se puede ejecutar porque está deshabilitado"

Ejecuta esto una sola vez:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Luego:
```powershell
.\scripts\deploy_chatbot_to_railway.ps1
```

### Error: "El archivo no existe"

Asegúrate que:
1. Abriste PowerShell en la raíz del proyecto
2. El archivo existe en `scripts/deploy_chatbot_to_railway.ps1`
3. Usas rutas correctas: `.\scripts\...`

```powershell
# Navega a la carpeta raíz
cd c:\xampp\htdocs\pecosol

# Verifica que existe
Get-ChildItem scripts/

# Ahora ejecuta
.\scripts\deploy_chatbot_to_railway.ps1
```

---

## 📝 Variables de Entorno Que Genera

El script `deploy_chatbot_to_railway.ps1` te ayuda a generar:

**Para servicio `pecosol-web`:**
```
CHATBOT_API_URL=https://pecosol-chatbot.railway.app/api/chat
```

**Para servicio `pecosol-chatbot`:**
```
CHATBOT_ALLOWED_ORIGINS=https://pecosol-web.railway.app
API_HOST=0.0.0.0
API_RELOAD=false
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
```

---

## 📚 Más Ayuda

- 📖 Lee [`SETUP_CHATBOT_RAILWAY.md`](../SETUP_CHATBOT_RAILWAY.md) para guía completa
- 🐛 Lee [`TROUBLESHOOTING_CHATBOT.md`](../TROUBLESHOOTING_CHATBOT.md) para resolver problemas
- ✅ Usa [`verify_chatbot.php`](../verify_chatbot.php) para diagnóstico web
