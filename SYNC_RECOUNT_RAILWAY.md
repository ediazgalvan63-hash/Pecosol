# 🔄 Sincronización de Reconteo: LOCAL ↔ RAILWAY

## 📌 Respuesta a tu pregunta

> "¿El módulo de reconteo del panel de admin del local también lo tiene Railway?"

**Respuesta corta:** Sí, el código está ahí, pero **necesita sincronización de datos**.

### Estado Actual (12-06-2026)

| Métrica | LOCAL | RAILWAY |
|---------|-------|---------|
| **Código del reconteo** | ✓ Funciona | ✓ Desplegado |
| **Productos** | 10 | ❓ Por verificar |
| **Stock total** | 196 | ❓ Por verificar |
| **Reconteos registrados** | 6 | ❓ Por verificar |

---

## 🚀 Pasos para Sincronizar

### Opción A: Script Automático (Recomendado)

```powershell
# 1. Abre PowerShell en la carpeta del proyecto
cd c:\xampp\htdocs\pecosol

# 2. Ejecuta el script de verificación
.\scripts\verify_recount_railway.ps1

# 3. Ingresa credenciales de Railway cuando lo pida
# El script comparará LOCAL vs RAILWAY automáticamente
```

**El script:**
- ✓ Conecta a Railway
- ✓ Obtiene datos de Railway
- ✓ Compara con LOCAL
- ✓ Te dice si necesita sincronizar
- ✓ Te da los comandos exactos para sincronizar

---

### Opción B: Manual con MySQL CLI

#### Paso 1: Obtener credenciales de Railway

Ve a https://railway.app:
1. Selecciona proyecto **Pecosol**
2. Haz clic en servicio **MySQL**
3. Tab **Variables** o **Connect**
4. Copia:
   - `MYSQLHOST` (ej: `mysql-prod.railway.internal`)
   - `MYSQLUSER` (ej: `root`)
   - `MYSQLPASSWORD` (ej: `abc123...`)
   - `MYSQLPORT` (ej: `3306`)
   - `MYSQLDATABASE` (ej: `railway`)

#### Paso 2: Exportar datos de LOCAL

```bash
# En PowerShell o CMD
mysqldump -u root -p pecosol_db products stock_movements > recount_backup.sql
# Te pedirá contraseña (XAMPP suele tenerla vacía, solo presiona Enter)
```

#### Paso 3: Importar a RAILWAY

```bash
# En PowerShell (reemplaza con tus credenciales)
mysql -h mysql-prod.railway.internal -u root -p"tu_password" -P 3306 railway < recount_backup.sql
```

---

### Opción C: Usar phpMyAdmin

Si Railway proporciona phpMyAdmin:
1. Abre la URL de Railway phpMyAdmin
2. Selecciona tabla `products` → Exportar
3. Luego importa en RAILWAY
4. Repite con `stock_movements`

---

## 🔍 Verificar que Funcionó

**En LOCAL:**
```bash
php check_recount_status_cli.php
```

**En RAILWAY:**
```bash
# Abre: https://tu-railway-url/check_recount_status_cli.php
# O accede al panel de reconteo:
https://tu-railway-url/index.php?controller=admin&action=inventoryRecountForm
```

Ambas deberían mostrar:
- ✓ 10 productos
- ✓ Stock total 196
- ✓ 6 reconteos registrados

---

## 🛠️ Comando Rápido Todo-en-Uno

Si tienes MySQL CLI instalado en LOCAL y acceso a Railway:

```bash
# Windows (PowerShell)
$host = "mysql-prod.railway.internal"
$user = "root"
$pass = "tu_password"
$db = "railway"

mysqldump -u root -p pecosol_db products stock_movements | mysql -h $host -u $user -p"$pass" $db
```

---

## 📊 ¿Qué se sincroniza?

**Tablas:**
- `products` — Lista de 10 productos con nombre, precio, stock
- `stock_movements` — Historial de 6 reconteos aplicados

**No se sincroniza:**
- `users`, `sales`, `purchases` — Si quieres réplica completa, importa el SQL completo

---

## 🆘 Si Falla

**"mysql: command not found"**
- Instala MySQL CLI: https://dev.mysql.com/downloads/mysql/
- O usa Chocolatey: `choco install mysql-cli`

**"Access denied"**
- Verifica usuario/contraseña en Railway
- Prueba conectar desde Railway UI primero

**"Unknown database"**
- La BD en Railway probablemente sea `railway` no `pecosol_db`
- Ajusta el comando accordingly

---

## 📝 Archivos Relacionados

- `compare_recount_status.php` — Verificación visual en navegador
- `check_recount_status_cli.php` — Verificación en terminal (LOCAL)
- `verify_recount_railway.ps1` — Script de sincronización (ÉSE ARCHIVO)
- `scripts/deploy_railway.ps1` — Deploy automático si hiciste cambios de código

---

## ✅ Resumen

| Paso | Acción |
|------|--------|
| 1️⃣ | Ejecuta `.\scripts\verify_recount_railway.ps1` |
| 2️⃣ | Ingresa credenciales de Railway |
| 3️⃣ | Script compara y te dice si necesita sincronizar |
| 4️⃣ | Si necesita: ejecuta comandos que da el script |
| 5️⃣ | Verifica: accede a Railway y prueba el reconteo |

**Tiempo estimado:** 5-10 minutos

---
