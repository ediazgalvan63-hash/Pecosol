# 🎯 INICIO RÁPIDO - Chatbot IA Python

## ✅ Estado del Proyecto

**TODO EL SISTEMA HA SIDO MIGRADO A PYTHON + FASTAPI**

### Lo que se hizo:

1. ✅ **API FastAPI completa en Python**
   - Servidor en `python_api/main.py`
   - Servicios de base de datos y chatbot
   - Conexión directa a MySQL
   - Integración con OpenAI

2. ✅ **Widget flotante moderno**
   - Botón flotante en esquina inferior derecha
   - Ventana de chat pequeña y responsive
   - CSS y JavaScript modernos
   - Integrado en todas las vistas principales

3. ✅ **Archivos PHP del chatbot eliminados/obsoletos**
   - Ya NO se usa `api/chatbot.php`
   - Ya NO se usa `controllers/ChatbotController.php`
   - Ya NO se usa la vista de página completa
   
4. ✅ **Documentación completa**
   - Ver `CHATBOT_PYTHON_GUIA.md` para guía detallada

---

## 🚀 Cómo iniciar (3 pasos)

### 1. Asegúrate de que XAMPP esté corriendo
- MySQL debe estar activo
- Apache debe estar activo

### 2. Configurar API Key de OpenAI

Edita el archivo `python_api/.env` y reemplaza:

```env
OPENAI_API_KEY=tu_api_key_aqui
```

Con tu clave real de OpenAI (obtener en: https://platform.openai.com/api-keys)

### 3. Iniciar el servidor Python

```powershell
cd C:\xampp\htdocs\bodeshop\python_api
.\start.bat
```

O manualmente:

```powershell
cd C:\xampp\htdocs\bodeshop\python_api
python -m uvicorn main:app --host 127.0.0.1 --port 8000 --reload
```

---

## 🎨 Cómo usar el chatbot

1. Abre la aplicación en tu navegador:
   ```
   http://localhost/bodeshop
   ```

2. Inicia sesión (admin o empleado)

3. Verás un **botón flotante** (🤖) en la esquina inferior derecha

4. Haz clic en el botón para abrir el chat

5. Escribe tu pregunta:
   - "¿Cuántos productos tengo en stock?"
   - "¿Cuáles son las ventas del mes?"
   - "Muéstrame los productos con stock bajo"
   - etc.

---

## 🔍 Verificar que todo funciona

### Test 1: Health Check del servidor Python

```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/health' -Method Get
```

Deberías ver:
```
status  : healthy
database: connected
openai_configured: True
```

### Test 2: Probar el endpoint del chatbot

```powershell
$body = @{ message = '¿Cuántos productos tengo?' } | ConvertTo-Json
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/chat' -Method Post -Body $body -ContentType 'application/json'
```

### Test 3: Ver documentación interactiva

Abre en tu navegador:
```
http://127.0.0.1:8000/docs
```

---

## 📂 Estructura de archivos (nuevo chatbot)

```
bodeshop/
├── python_api/              ← SERVIDOR PYTHON (NUEVO)
│   ├── main.py             ← FastAPI principal
│   ├── .env                ← Configuración (API keys, DB)
│   ├── start.bat           ← Script de inicio
│   ├── requirements.txt    ← Dependencias
│   └── services/
│       ├── chatbot_service.py    ← Lógica del chatbot + OpenAI
│       └── database_service.py   ← Consultas MySQL
│
├── assets/
│   ├── css/
│   │   └── chatbot-widget.css   ← Estilos del widget flotante
│   └── js/
│       └── chatbot-widget.js    ← Cliente JavaScript del widget
│
└── views/
    ├── admin/
    │   ├── dashboard.php         ← Widget integrado ✅
    │   ├── productos/
    │   │   └── list_products.php ← Widget integrado ✅
    │   ├── employee/
    │   │   └── list_employees.php← Widget integrado ✅
    │   └── ventas/
    │       └── list_sales.php    ← Widget integrado ✅
    └── employee/
        └── dashboard.php         ← Widget integrado ✅
```

---

## ⚠️ Archivos OBSOLETOS (ya no se usan)

Estos archivos eran del chatbot PHP anterior y ahora están reemplazados:

```
❌ api/chatbot.php
❌ api/chatbot_debug.php
❌ controllers/ChatbotController.php
❌ models/ChatbotService.php
❌ views/admin/chatbot.php
❌ assets/css/chatbot.css (antiguo)
❌ assets/js/chatbot.js (antiguo)
❌ test_chatbot.php
❌ check_openai.php
```

Puedes eliminarlos si quieres limpiar el proyecto.

---

## 🐛 Solución rápida de problemas

### Problema: "Error de conexión al servidor Python"

**Solución:** Inicia el servidor Python:
```powershell
cd C:\xampp\htdocs\bodeshop\python_api
.\start.bat
```

### Problema: "You didn't provide an API key"

**Solución:** Edita `python_api/.env` y agrega tu OPENAI_API_KEY real.

### Problema: "Error de conexión a base de datos"

**Solución:** 
1. Verifica que MySQL esté corriendo en XAMPP
2. Verifica que la base de datos se llame `bodeshop_db`
3. Comprueba las credenciales en `python_api/.env`

---

## 📖 Documentación completa

Lee el archivo **`CHATBOT_PYTHON_GUIA.md`** para:
- Detalles de arquitectura
- Todos los comandos disponibles
- Troubleshooting avanzado
- Personalización del chatbot

---

## ✨ ¡Listo para usar!

Una vez que el servidor Python esté corriendo:

1. Abre http://localhost/bodeshop
2. Inicia sesión
3. Haz clic en el botón flotante 🤖
4. ¡Chatea con tu asistente IA!

**Disfruta tu nuevo chatbot potenciado por Python + FastAPI + OpenAI! 🚀**
