# 📊 DIAGRAMA VISUAL DE ARQUITECTURA

## 🏗️ Arquitectura del Chatbot IA

```
┌─────────────────────────────────────────────────────────────────┐
│                    BODESHOP ADMIN PANEL                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │             INTERFAZ DEL CHATBOT (Vista)                   │ │
│  │  ┌──────────────────┐  ┌──────────────────────────────┐   │ │
│  │  │ Panel Info Lateral│  │ Área de Chat               │   │ │
│  │  ├──────────────────┤  ├──────────────────────────────┤   │ │
│  │  │ 📦 Productos     │  │ Bot: Hola, ¿en qué puedo   │   │ │
│  │  │ 💰 Ventas       │  │ ayudarte?                  │   │ │
│  │  │ 👥 Empleados    │  │                            │   │ │
│  │  │ 📊 Estadísticas │  │ User: ¿Stock total?        │   │ │
│  │  │ ⚠️ Bajo Stock   │  │                            │   │ │
│  │  │                  │  │ Bot: Tienes 1,250 unidades │   │ │
│  │  └──────────────────┘  │                            │   │ │
│  │                        │ ┌──────────────────────┐   │   │ │
│  │                        │ │ Input: pregunta...   │   │   │ │
│  │                        │ │ [Enviar]             │   │   │ │
│  │                        └──────────────────────────┤   │ │
│  └────────────────────────────────────────────────────────────┘ │
│                         ↓ JavaScript                             │
│                   (chatbot.js envía JSON)                        │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ HTTP POST
                         │ {"message": "¿Stock total?"}
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                    API ENDPOINT                                  │
├─────────────────────────────────────────────────────────────────┤
│  api/chatbot.php                                                │
│  - Recibe POST JSON                                             │
│  - Valida entrada                                               │
│  - Instancia ChatbotController                                  │
│  - Retorna JSON response                                        │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│              CHATBOT CONTROLLER                                  │
├─────────────────────────────────────────────────────────────────┤
│  ChatbotController::apiChat()                                   │
│  - Obtiene el mensaje                                           │
│  - Llama a chat() con el mensaje                                │
│  - Retorna respuesta JSON                                       │
│                                                                  │
│  ChatbotController::chat()                                      │
│  - Obtiene contexto de BD vía ChatbotService                   │
│  - Construye prompt del sistema (buildSystemPrompt)            │
│  - Llama OpenAI API                                            │
│  - Procesa respuesta                                           │
│  - Retorna array con resultado                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
             ┌───────────┴──────────┐
             │ Necesita datos BD    │
             ↓                      ↓
    ┌──────────────────┐  ┌──────────────────────────┐
    │ ChatbotService   │  │ Construcción de Prompt  │
    ├──────────────────┤  ├──────────────────────────┤
    │ getProducts()    │  │ Contexto actual:        │
    │ getSales()       │  │ - Total productos: 234  │
    │ getEmployees()   │  │ - Stock total: 1250     │
    │ getStatistics()  │  │ - Ventas últimos 7d: ..│
    │ getInventory()   │  │ - Empleados: 12         │
    │ getLowStock()    │  │ - Bajo stock: 5 items  │
    └────────┬─────────┘  └──────────┬───────────────┘
             │                       │
             ↓                       ↓
    ┌──────────────────────────────────────────────┐
    │      DATABASE (bodeshop_db)                  │
    ├──────────────────────────────────────────────┤
    │ - products (name, price, stock)              │
    │ - sales (product_id, quantity, price)        │
    │ - employees (name, email, salary)            │
    │ - users (id, name)                           │
    └──────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Datos Completo

```
Usuario escribe pregunta
        │
        ↓
   ┌─────────┐
   │chatbot. │ JavaScript captura
   │js       │ evento y envía AJAX
   └────┬────┘
        │ POST JSON
        │ Content-Type: application/json
        ↓
   ┌──────────────────┐
   │api/chatbot.php   │ Verifica método POST
   └────┬─────────────┘
        │ Instancia ChatbotController
        ↓
   ┌────────────────────────────┐
   │ChatbotController::apiChat()│ Obtiene mensaje del JSON
   └────┬───────────────────────┘
        │ Llama chat($message)
        ↓
   ┌──────────────────────────┐
   │ChatbotController::chat() │ 1. Obtiene contexto
   ├──────────────────────────┤    (ChatbotService)
   │ buildSystemPrompt()      │ 2. Construye prompt
   │ OpenAI API call          │ 3. Llama OpenAI
   └────┬────────────────────┘ 4. Retorna respuesta
        │
        │ Array con:
        │ - success: true/false
        │ - response: texto
        │ - timestamp: fecha
        ↓
   ┌──────────────────────┐
   │api/chatbot.php       │ Convierte array a JSON
   │json_encode()         │ Setea headers correcto
   └────┬─────────────────┘
        │ JSON response
        │ {"success": true, "response": "...", ...}
        ↓
   ┌─────────────┐
   │chatbot.js   │ Recibe respuesta
   │fetch()      │ Parsea JSON
   │then()       │ Valida éxito
   └────┬────────┘
        │ Agrega mensaje a UI
        │ addMessage(response, 'bot')
        ↓
   ┌──────────────────────────┐
   │DOM actualizado           │ Mensaje aparece
   │- Nueva div.message       │ en el chat
   │- Contenido rendereado    │
   │- Scroll automático       │
   └──────────────────────────┘
        │
        ↓
   Usuario ve respuesta
```

---

## 📁 Estructura de Carpetas

```
bodeshop/
│
├── 📄 INICIO_RAPIDO.md                    ← EMPIEZA AQUÍ
├── 📄 GUIA_CHATBOT.md                     ← Guía detallada
├── 📄 RESUMEN_IMPLEMENTACION.md
├── 📄 CHECKLIST_IMPLEMENTACION.md
├── 📄 COMANDOS_POWERSHELL.md
│
├── 🔧 config/
│   ├── config.php                         (existente)
│   ├── database.php                       (existente)
│   └── openai.php                         ⭐ NUEVO - Tu API Key aquí
│
├── 🎮 controllers/
│   ├── AdminController.php                (existente)
│   ├── AuthController.php                 (existente)
│   ├── DashboardController.php            (existente)
│   ├── EmployeeController.php             (existente)
│   └── ChatbotController.php              ⭐ NUEVO
│
├── 📊 models/
│   ├── Employee.php                       (existente)
│   ├── Product.php                        (existente)
│   ├── Sale.php                           (existente)
│   ├── User.php                           (existente)
│   └── ChatbotService.php                 ⭐ NUEVO
│
├── 🌐 views/
│   ├── admin/
│   │   ├── dashboard.php                  (existente)
│   │   ├── chatbot.php                    ⭐ NUEVO
│   │   ├── employee/                      (existente)
│   │   ├── productos/                     (existente)
│   │   ├── ventas/                        (existente)
│   │   └── partials/
│   │       └── header.php                 (MODIFICADO - Botón chatbot)
│   └── employee/                          (existente)
│
├── 🎨 assets/
│   ├── css/
│   │   ├── style.css                      (existente)
│   │   └── chatbot.css                    ⭐ NUEVO
│   ├── js/
│   │   ├── script.js                      (existente)
│   │   ├── chart.umd.js                   (existente)
│   │   └── chatbot.js                     ⭐ NUEVO
│   └── img/                               (existente)
│
├── 🔌 api/
│   ├── chatbot.php                        ⭐ NUEVO - Endpoint
│   └── chatbot_debug.php                  ⭐ NUEVO - Debug
│
├── 🧪 test_chatbot.php                    ⭐ NUEVO - Testing
│
├── composer.json                          (MODIFICADO - OpenAI)
├── vendor/                                (actualizado con composer)
└── ... (otros archivos)
```

---

## 🔐 Seguridad - Capas de Protección

```
┌─────────────────────────────────────┐
│  INPUT VALIDATION                   │
│  - Validar método POST              │
│  - Verificar JSON válido            │
│  - Sanitizar mensaje del usuario    │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│  CHATBOT LOGIC                      │
│  - Construir prompt seguro          │
│  - No exponer datos sensibles       │
│  - Limitar contexto                 │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│  API CALL TO OPENAI                 │
│  - API Key protegida (env var)      │
│  - HTTPS encryption                 │
│  - Rate limiting (optional)         │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│  RESPONSE HANDLING                  │
│  - Validar respuesta JSON           │
│  - Error handling robusto           │
│  - No exponer errores internos      │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│  USER OUTPUT                        │
│  - Escapar HTML en frontend         │
│  - Mostrar solo info relevante      │
│  - Logging de acciones              │
└─────────────────────────────────────┘
```

---

## ⚡ Stack Tecnológico

```
┌─────────────────────────────────────────────────────┐
│              LAYERS ARQUITECTURA                    │
├─────────────────────────────────────────────────────┤
│                                                      │
│  Frontend:                                          │
│  ├─ HTML (chatbot.php)                             │
│  ├─ CSS (chatbot.css) - Responsive, moderno       │
│  └─ JavaScript (chatbot.js) - AJAX, localStorage   │
│                                                      │
│  Backend:                                           │
│  ├─ PHP 7.4+ (Controllers, Models, Services)      │
│  ├─ OpenAI PHP SDK (openai-php/client)            │
│  └─ PDO (Database abstraction)                     │
│                                                      │
│  Database:                                          │
│  └─ MySQL (bodeshop_db)                            │
│     ├─ products                                     │
│     ├─ sales                                        │
│     ├─ employees                                    │
│     └─ users                                        │
│                                                      │
│  External APIs:                                     │
│  └─ OpenAI REST API (GPT-4o Mini)                  │
│                                                      │
└─────────────────────────────────────────────────────┘
```

---

## 📈 Flujo de Respuesta

```
Pregunta: "¿Qué productos tienen bajo stock?"
    │
    ↓
[ChatbotService consulta BD]
Bajo Stock: 
  - Producto A: 3 unidades
  - Producto B: 8 unidades
  - Producto C: 5 unidades
    │
    ↓
[Construye Prompt]
"Eres un asistente... Contexto actual:
 - Total productos: 234
 - Stock total: 1,250
 - Productos bajo stock: 3 items
 
 ¿Qué productos tienen bajo stock?"
    │
    ↓
[OpenAI Procesa]
GPT-4o Mini analiza el contexto
    │
    ↓
[Genera Respuesta]
"Según tu inventario, tienes 3 productos 
con stock crítico:
- Producto A: Solo 3 unidades
- Producto B: 8 unidades
- Producto C: 5 unidades

Te recomiendo hacer un pedido urgente 
de estos items."
    │
    ↓
Usuario ve respuesta en el chat
```

---

## 🎯 Casos de Uso

```
┌──────────────────────────────────────────┐
│         CASOS DE USO DEL CHATBOT        │
├──────────────────────────────────────────┤
│                                          │
│ 1. ANÁLISIS DE INVENTARIO               │
│    "¿Cuántos productos hay?"            │
│    → Datos: Productos totales, stock    │
│                                          │
│ 2. REPORTE DE VENTAS                    │
│    "¿Cuáles fueron las ventas hoy?"     │
│    → Datos: Total, cantidad, empleado   │
│                                          │
│ 3. ALERTAS DE STOCK                     │
│    "¿Bajo stock?"                       │
│    → Datos: Productos < 10 unidades     │
│                                          │
│ 4. ANÁLISIS DE TENDENCIAS               │
│    "¿Evolución de ventas en 7 días?"    │
│    → Datos: Gráfica, promedio, máximo   │
│                                          │
│ 5. CONSULTAS DE EMPLEADOS               │
│    "¿Quiénes son nuestros empleados?"   │
│    → Datos: Nombre, puesto, email       │
│                                          │
│ 6. ANÁLISIS PREDICTIVO                  │
│    "¿Recomendaciones para stock?"       │
│    → Datos: Basado en histórico ventas  │
│                                          │
└──────────────────────────────────────────┘
```

---

## 💰 Modelo de Costos

```
┌────────────────────────────────────────┐
│    ESTIMACIÓN DE COSTOS MENSUALES      │
├────────────────────────────────────────┤
│                                        │
│ Mensajes/mes:        50                │
│ Tokens promedio:     ~200 entrada      │
│                      ~150 salida       │
│                                        │
│ GPT-4o Mini:                          │
│ Entrada: 50 × 200 × $0.00015 = $1.50  │
│ Salida:  50 × 150 × $0.0006  = $4.50  │
│ ─────────────────────────────           │
│ TOTAL:                  ~$6/mes        │
│                                        │
│ Si usas 200 mensajes/mes:   ~$24/mes  │
│                                        │
│ ✅ MUY ECONÓMICO para un negocio       │
│                                        │
└────────────────────────────────────────┘
```

---

Este diagrama visual resume la arquitectura completa del sistema. 🎨

Cualquier pregunta sobre cómo funciona, revisa la **GUIA_CHATBOT.md**.
