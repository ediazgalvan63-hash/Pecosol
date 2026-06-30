# 📋 GUÍA: SINCRONIZAR PECOSOL A RAILWAY

## Estado Actual en LOCAL ✅
- **Código**: Totalmente sincronizado en GitHub
- **BD**: `pecosol_db` con 3 roles (admin, comercial, supervisor)
- **Usuarios**: admin, lucho, pedrito
- **Commit**: `002f60b` - Estado actual de BD

---

## PASO 1: Redeploy de la Aplicación en Railway

### Opción A: Redeploy Automático (Recomendado)
Si Railway está configurado con GitHub (webhook), debería hacer redeploy automáticamente.

1. Ve a https://railway.app
2. Selecciona tu proyecto **Pecosol**
3. En el servicio **PHP** o **Node.js**, busca la sección de **Deployments**
4. Deberías ver un nuevo deployment iniciándose automáticamente

### Opción B: Forzar Redeploy Manual
1. En Railway, ve al servicio PHP
2. Botón **Deploy** → **Trigger Deploy**
3. Espera ~3-5 minutos a que termine

---

## PASO 2: Obtener Credenciales de Railway

Necesitas las credenciales de tu BD en Railway:

1. Ve a https://railway.app → Tu proyecto **Pecosol**
2. En el servicio de **MySQL** (o PostgreSQL)
3. Haz clic en **Variables** o **Connect**
4. Copia estos datos:
   - `MYSQLHOST` o `DB_HOST`
   - `MYSQLUSER` o `DB_USER` 
   - `MYSQLPASSWORD` o `DB_PASSWORD`
   - `MYSQLPORT` o `DB_PORT` (usualmente 3306)
   - `MYSQLDATABASE` o `DB_NAME` (si no, es `pecosol_db`)

Ejemplo:
```
MYSQLHOST: abc123.railway.app
MYSQLUSER: root
MYSQLPASSWORD: XyZ789...
MYSQLPORT: 3306
MYSQLDATABASE: pecosol_db
```

---

## PASO 3A: Sincronizar BD vía PHP (RECOMENDADO)

Si tienes acceso SSH a Railway o localhost para correr PHP:

```bash
# Windows PowerShell
$env:RAILWAY_DB_HOST = "abc123.railway.app"
$env:RAILWAY_DB_USER = "root"
$env:RAILWAY_DB_PASSWORD = "XyZ789..."
$env:RAILWAY_DB_PORT = "3306"

php sync_to_railway.php
```

```bash
# macOS/Linux
export RAILWAY_DB_HOST="abc123.railway.app"
export RAILWAY_DB_USER="root"
export RAILWAY_DB_PASSWORD="XyZ789..."
export RAILWAY_DB_PORT="3306"

php sync_to_railway.php
```

El script:
- ✅ Se conectará a Railway
- ✅ Creará la BD si no existe
- ✅ Importará todo el SQL
- ✅ Verificará que los datos estén correctos

---

## PASO 3B: Sincronizar BD vía MySQL CLI

Si tienes `mysql` instalado localmente:

```bash
# Opción 1: Importar directamente a Railway
mysql -h abc123.railway.app -u root -p"XyZ789..." pecosol_db < pecosol_db_current.sql

# Opción 2: Primero drop + recreate (recomendado)
mysql -h abc123.railway.app -u root -p"XyZ789..." -e "DROP DATABASE IF EXISTS pecosol_db; CREATE DATABASE pecosol_db DEFAULT CHARACTER SET utf8mb4;"
mysql -h abc123.railway.app -u root -p"XyZ789..." pecosol_db < pecosol_db_current.sql
```

---

## PASO 3C: Sincronizar BD vía phpMyAdmin (Si Railway lo proporciona)

1. Ve a phpMyAdmin de Railway (URL proporcionada en Dashboard)
2. Crea BD `pecosol_db` si no existe
3. Selecciona la BD → **Import**
4. Sube archivo `pecosol_db_current.sql`
5. Haz clic en **Import**

---

## PASO 4: Verificar Sincronización

Una vez importada la BD en Railway:

```bash
# Verifica usuarios
mysql -h abc123.railway.app -u root -p"XyZ789..." pecosol_db -e "SELECT id, username, email, role FROM users;"

# Resultado esperado:
# | 1  | admin  | admin@bodeshop.com | admin      |
# | 9  | lucho  | lucho@gmail.com    | comercial  |
# | 14 | pedrito| pedrito@gmail.com  | supervisor |

# Verifica conteo de datos
mysql -h abc123.railway.app -u root -p"XyZ789..." pecosol_db -e "SHOW TABLES; SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM products; SELECT COUNT(*) FROM sales;"
```

---

## ✅ Checklist Final

- [ ] Código actualizado en GitHub (commit `002f60b`)
- [ ] Redeploy iniciado en Railway
- [ ] Credenciales de BD Railway obtenidas
- [ ] BD importada a Railway
- [ ] Usuarios verificados en Railway
- [ ] Aplicación accesible en Railway
- [ ] Login funciona con credenciales locales

---

## 🐛 Troubleshooting

### Error: "Table 'pecosol_db.roles' doesn't exist"
- ✅ NORMAL - No hay tabla `roles` en este proyecto
- Los roles están definidos como ENUM en la tabla `users`

### Error: "Access denied for user 'root'"
- Verifica la contraseña de Railway
- Asegúrate que el host es correcto
- Comprueba que no haya espacios en blanco

### Error: "Unknown database 'pecosol_db'"
- Ejecuta: `CREATE DATABASE pecosol_db;` primero
- Luego importa el SQL

### La aplicación no conecta a BD en Railway
- Verifica que `config/database.php` lee variables de entorno
- En Railway, establece variables:
  ```
  DB_HOST = xxx.railway.app
  DB_USERNAME = root
  DB_PASSWORD = xxx
  DB_NAME = pecosol_db
  DB_PORT = 3306
  ```

---

## 📞 Próximos Pasos

Una vez completada la sincronización, el sistema en Railway será idéntico al local:
- Mismo código PHP
- Misma BD (3 roles, 3 usuarios)
- Mismo comportamiento de chatbot (timeout 0.2s)
- Misma zona horaria (Perú UTC-5)

¡Tu proyecto está listo para producción! 🚀
