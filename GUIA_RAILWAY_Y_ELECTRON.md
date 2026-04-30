# Guia: Railway + Electron Offline

Esta guia te deja el proyecto en dos etapas:

1. Web en Railway con MySQL y chatbot funcionando.
2. App de escritorio con Electron + SQLite para uso sin internet.

---

## 1) Subir a Railway (web + base de datos + chatbot)

### Arquitectura recomendada en Railway

- Servicio A: `pecosol-web` (PHP + Apache) usando `Dockerfile` en raiz.
- Servicio B: `pecosol-chatbot` (FastAPI) usando `python_api/Dockerfile`.
- Servicio C: `MySQL` (plugin de Railway).

### 1.1 Crear proyecto en Railway

1. En Railway crea un proyecto nuevo.
2. Conecta este repositorio GitHub.
3. Agrega el plugin MySQL.
4. Crea 2 servicios desde el mismo repo:
   - `pecosol-web` con `Dockerfile` de la raiz.
   - `pecosol-chatbot` con `python_api/Dockerfile`.

### 1.2 Variables de entorno

#### Servicio `pecosol-web`

- `APP_BASE_URL=https://TU-DOMINIO-WEB.railway.app/`
- `CHATBOT_API_URL=https://TU-DOMINIO-CHATBOT.railway.app/api/chat`
- `DB_HOST=<MYSQLHOST de Railway>`
- `DB_PORT=<MYSQLPORT de Railway>`
- `DB_DATABASE=<MYSQLDATABASE de Railway>`
- `DB_USERNAME=<MYSQLUSER de Railway>`
- `DB_PASSWORD=<MYSQLPASSWORD de Railway>`

#### Servicio `pecosol-chatbot`

- `DB_HOST=<MYSQLHOST de Railway>`
- `DB_PORT=<MYSQLPORT de Railway>`
- `DB_NAME=<MYSQLDATABASE de Railway>`
- `DB_USER=<MYSQLUSER de Railway>`
- `DB_PASSWORD=<MYSQLPASSWORD de Railway>`
- `OPENAI_API_KEY=...` (o `GEMINI_API_KEY=...`)
- `OPENAI_MODEL=gpt-4o-mini`
- `CHATBOT_ALLOWED_ORIGINS=https://TU-DOMINIO-WEB.railway.app`
- `API_HOST=0.0.0.0`
- `API_RELOAD=false`

### 1.3 Importar la base de datos en Railway

Desde tu PC, conecta al MySQL de Railway e importa `pecosol_db.sql`:

```bash
mysql -h <MYSQLHOST> -P <MYSQLPORT> -u <MYSQLUSER> -p <MYSQLDATABASE> < pecosol_db.sql
```

### 1.4 Verificacion

1. Abre `https://TU-DOMINIO-CHATBOT.railway.app/health` y valida `database: connected`.
2. Abre la web en Railway y haz login.
3. Prueba chatbot en Dashboard, Productos o Ventas.
4. Si algo falla, revisa logs de ambos servicios.

### 1.5 Script de despliegue incluido

Tambien puedes usar el script:

```powershell
.\scripts\deploy_railway.ps1 -WebServiceId "<id_web>" -ChatbotServiceId "<id_chatbot>"
```

Plantillas de variables:

- `/.env.railway.web.example`
- `/python_api/.env.railway.chatbot.example`

---

## 2) App de escritorio con Electron + SQLite (offline)

Se agrego un esqueleto funcional en `desktop-app/`.

### 2.1 Instalar y ejecutar

```bash
cd desktop-app
npm install
npm run start
```

### 2.2 Que hace este esqueleto

- Crea base local SQLite en el directorio de usuario.
- Importa automaticamente `pecosol_db.sql` al iniciar.
- Permite registrar productos.
- Muestra productos y resumen de ventas/stock.
- Incluye chatbot offline basado en consultas SQLite.
- Todo corre local, sin servidor externo.

### 2.3 Archivos clave

- `desktop-app/src/main.js` (ventana + IPC)
- `desktop-app/src/sqlite.js` (modelo SQLite)
- `desktop-app/src/preload.js` (puente seguro)
- `desktop-app/src/renderer/*` (UI basica)

### 2.4 Migracion real recomendada

Para que sea equivalente al sistema web actual:

1. Migrar tablas MySQL -> SQLite.
2. Reescribir controladores PHP a logica Node/Electron IPC o API local.
3. Adaptar reportes Excel.
4. Para chatbot offline real:
   - opcion A: modelo local via Ollama.
   - opcion B: modo sin IA con respuestas basadas en SQL.

Sin internet, OpenAI/Gemini no funcionaran.

