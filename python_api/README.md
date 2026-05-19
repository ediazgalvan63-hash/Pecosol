# 🤖 FastAPI Chatbot Service - Pecosol

Microservicio Python con FastAPI para el chatbot con IA y acceso directo a la base de datos MySQL.

## 🚀 Características

- **FastAPI**: Framework moderno y rápido para APIs
- **OpenAI Integration**: Respuestas inteligentes con GPT-4o-mini
- **MySQL Direct Access**: Consultas en tiempo real a la base de datos
- **Context-Aware**: El chatbot obtiene contexto de productos, ventas y empleados
- **CORS Enabled**: Compatible con el frontend PHP existente

## 📋 Requisitos

- Python 3.8 o superior
- MySQL (ya configurado con XAMPP)
- OpenAI API Key

## ⚙️ Instalación

### 1. Instalar dependencias

```bash
# Desde el directorio python_api/
python -m pip install -r requirements.txt
```

O usa el script automatizado:
```bash
start.bat
```

### 2. Configurar variables de entorno

Copia `.env.example` a `.env` y configura tus credenciales:

```bash
copy .env.example .env
```

Edita `.env` con tus valores:
```ini
OPENAI_API_KEY=sk-tu-api-key-aqui
OPENAI_MODEL=gpt-4o-mini

DB_HOST=localhost
DB_NAME=bodeshop
DB_USER=root
DB_PASSWORD=
DB_PORT=3306
```

### 3. Iniciar el servidor

```bash
# Opción 1: Con el script normal (muestra la consola)
start.bat

# Opción 2: Iniciar en segundo plano sin ventana
start_hidden.bat

# Opción 3: Manualmente
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

El servidor estará disponible en:
- **API**: http://127.0.0.1:8000
- **Documentación**: http://127.0.0.1:8000/docs
- **Health Check**: http://127.0.0.1:8000/health

## 📡 Endpoints

### `POST /api/chat`
Endpoint principal del chatbot

**Request:**
```json
{
  "message": "¿Cuántas ventas hay en total?",
  "user_id": 1,
  "session_id": "abc123"
}
```

**Response:**
```json
{
  "success": true,
  "response": "Hasta el momento hay 150 ventas registradas...",
  "context_used": {
    "sales_statistics": {...}
  }
}
```

### `GET /health`
Verificar estado del servicio

**Response:**
```json
{
  "status": "healthy",
  "database": "connected",
  "openai_configured": true
}
```

### `GET /api/stats`
Obtener estadísticas del negocio

## 🔧 Integración con el Frontend PHP

El frontend ya está configurado para usar este API. Solo necesitas:

1. Iniciar el servidor FastAPI (puerto 8000)
2. Mantener Apache/PHP corriendo (puerto 80)
3. El frontend automáticamente enviará las peticiones a Python

## 🗂️ Estructura del Proyecto

```
python_api/
├── main.py                      # Punto de entrada FastAPI
├── services/
│   ├── __init__.py
│   ├── chatbot_service.py       # Lógica del chatbot + OpenAI
│   └── database_service.py      # Conexión y queries MySQL
├── requirements.txt             # Dependencias Python
├── .env.example                 # Plantilla de variables
├── .env                         # Variables de entorno (no subir a git)
├── .gitignore
├── start.bat                    # Script para Windows
└── README.md
```

## 🧪 Pruebas

### Desde PowerShell:
```powershell
# Test health
Invoke-RestMethod -Uri "http://127.0.0.1:8000/health"

# Test chat
$body = @{ message = "¿Cuántos productos tengo?" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/chat" -Method Post -Body $body -ContentType "application/json"
```

### Desde el navegador:
Abre http://127.0.0.1:8000/docs para ver la documentación interactiva de Swagger.

## 🔐 Seguridad

- **No subas el archivo `.env` a git** (ya está en `.gitignore`)
- La API Key de OpenAI debe estar en `.env`, no en el código
- Las credenciales de la base de datos también deben estar en `.env`

## 📝 Logs

Los logs se muestran en la consola donde ejecutaste el servidor:
- ✅ Verde: Operaciones exitosas
- ⚠️ Amarillo: Advertencias
- ❌ Rojo: Errores

## 🐛 Troubleshooting

### Error: "No module named 'fastapi'"
```bash
pip install -r requirements.txt
```

### Error: "Can't connect to MySQL server"
Verifica que:
- XAMPP/MySQL esté corriendo
- Las credenciales en `.env` sean correctas
- El puerto 3306 esté disponible

### Error: "OPENAI_API_KEY no configurada"
- Verifica que el archivo `.env` existe
- Confirma que `OPENAI_API_KEY=...` tiene tu clave válida
- Reinicia el servidor después de editar `.env`

## 📚 Documentación Adicional

- [FastAPI Docs](https://fastapi.tiangolo.com/)
- [OpenAI API Docs](https://platform.openai.com/docs)
- [MySQL Connector Python](https://dev.mysql.com/doc/connector-python/en/)

---

**Desarrollado para Bodeshop (Pecosol)** 🛍️
