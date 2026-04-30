const Database = require("better-sqlite3");
const path = require("path");
const fs = require("fs");
const { app } = require("electron");

let db;

function getDbPath() {
  const dataDir = app.getPath("userData");
  if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });
  return path.join(dataDir, "pecosol.sqlite");
}

function initDb() {
  db = new Database(getDbPath());
  db.pragma("journal_mode = WAL");
  db.pragma("foreign_keys = ON");

  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY,
      username TEXT NOT NULL UNIQUE,
      password TEXT NOT NULL,
      full_name TEXT NOT NULL,
      email TEXT NOT NULL UNIQUE,
      role TEXT NOT NULL,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP,
      updated_at TEXT
    );

    CREATE TABLE IF NOT EXISTS employees (
      id INTEGER PRIMARY KEY,
      user_id INTEGER NOT NULL UNIQUE,
      address TEXT,
      phone TEXT,
      hired_date TEXT,
      FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS products (
      id INTEGER PRIMARY KEY,
      name TEXT NOT NULL,
      description TEXT DEFAULT '',
      price REAL NOT NULL DEFAULT 0,
      stock INTEGER NOT NULL DEFAULT 0,
      stock_minimum INTEGER NOT NULL DEFAULT 0,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP,
      updated_at TEXT
    );

    CREATE TABLE IF NOT EXISTS sales (
      id INTEGER PRIMARY KEY,
      user_id INTEGER NOT NULL,
      product_id INTEGER NOT NULL,
      quantity INTEGER NOT NULL,
      unit_price REAL NOT NULL DEFAULT 0,
      total_price REAL NOT NULL,
      description TEXT,
      sale_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP,
      updated_at TEXT,
      FOREIGN KEY(user_id) REFERENCES users(id),
      FOREIGN KEY(product_id) REFERENCES products(id)
    );

    CREATE TABLE IF NOT EXISTS stock_movements (
      id INTEGER PRIMARY KEY,
      product_id INTEGER NOT NULL,
      user_id INTEGER NOT NULL,
      quantity_change INTEGER NOT NULL,
      movement_type TEXT NOT NULL,
      notes TEXT,
      movement_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
      FOREIGN KEY(user_id) REFERENCES users(id)
    );
  `);
}

function getProducts() {
  return db.prepare("SELECT * FROM products ORDER BY name ASC").all();
}

function saveProduct(payload) {
  const stmt = db.prepare(`
    INSERT INTO products(name, description, price, stock, stock_minimum, created_at)
    VALUES (@name, @description, @price, @stock, @stock_minimum, datetime('now'))
  `);
  const info = stmt.run({
    name: payload.name,
    description: payload.description || "",
    price: Number(payload.price || 0),
    stock: Number(payload.stock || 0),
    stock_minimum: Number(payload.stock_minimum || 0)
  });
  return { id: info.lastInsertRowid };
}

function getSalesSummary() {
  const totals = db.prepare(`
    SELECT
      COUNT(*) as total_sales,
      COALESCE(SUM(total_price), 0) as total_revenue,
      COALESCE(AVG(total_price), 0) as average_sale
    FROM sales
  `).get();

  const lowStock = db.prepare(`
    SELECT id, name, stock, stock_minimum
    FROM products
    WHERE stock <= stock_minimum
    ORDER BY stock ASC
  `).all();

  return { totals, lowStock };
}

function importFromSqlDump(sqlDumpPath) {
  const absolutePath = path.resolve(sqlDumpPath);
  if (!fs.existsSync(absolutePath)) {
    throw new Error(`No existe el SQL dump: ${absolutePath}`);
  }

  const raw = fs.readFileSync(absolutePath, "utf8");
  const tableInsertRegex = /INSERT INTO `(\w+)` \(([^)]+)\) VALUES\s*([\s\S]*?);/g;
  const tx = db.transaction(() => {
    let match;
    while ((match = tableInsertRegex.exec(raw)) !== null) {
      const table = match[1];
      if (!["users", "employees", "products", "sales", "stock_movements"].includes(table)) {
        continue;
      }

      const cols = match[2].split(",").map((c) => c.replace(/`/g, "").trim());
      const rowsBlob = match[3];
      const rowMatches = rowsBlob.match(/\([^\)]*\)/g) || [];
      const placeholders = cols.map(() => "?").join(", ");
      const sql = `INSERT OR REPLACE INTO ${table} (${cols.join(", ")}) VALUES (${placeholders})`;
      const stmt = db.prepare(sql);

      for (const row of rowMatches) {
        const values = splitSqlValues(row.slice(1, -1)).map(parseSqlValue);
        stmt.run(values);
      }
    }
  });
  tx();

  return {
    users: db.prepare("SELECT COUNT(*) as total FROM users").get().total,
    products: db.prepare("SELECT COUNT(*) as total FROM products").get().total,
    sales: db.prepare("SELECT COUNT(*) as total FROM sales").get().total
  };
}

function splitSqlValues(valueList) {
  const parts = [];
  let current = "";
  let inQuotes = false;
  let escape = false;

  for (const ch of valueList) {
    if (escape) {
      current += ch;
      escape = false;
      continue;
    }
    if (ch === "\\") {
      current += ch;
      escape = true;
      continue;
    }
    if (ch === "'") {
      inQuotes = !inQuotes;
      current += ch;
      continue;
    }
    if (ch === "," && !inQuotes) {
      parts.push(current.trim());
      current = "";
      continue;
    }
    current += ch;
  }
  if (current.trim().length > 0) parts.push(current.trim());
  return parts;
}

function parseSqlValue(rawValue) {
  if (rawValue === "NULL") return null;
  if (/^'.*'$/.test(rawValue)) {
    return rawValue
      .slice(1, -1)
      .replace(/\\'/g, "'")
      .replace(/\\\\/g, "\\");
  }
  const asNumber = Number(rawValue);
  return Number.isNaN(asNumber) ? rawValue : asNumber;
}

function askOfflineChat(message) {
  const msg = String(message || "").toLowerCase();
  const summary = getSalesSummary();
  const products = getProducts();

  if (msg.includes("stock") || msg.includes("inventario")) {
    const list = products
      .slice(0, 12)
      .map((p) => `- ${p.name}: ${p.stock} u. (min ${p.stock_minimum})`)
      .join("\n");
    return `Inventario local:\n${list}\n\nTotal productos: ${products.length}.`;
  }

  if (msg.includes("venta") || msg.includes("ingreso")) {
    return `Ventas locales:\n- Total ventas: ${summary.totals.total_sales}\n- Ingresos: $${Number(summary.totals.total_revenue).toFixed(2)}\n- Promedio por venta: $${Number(summary.totals.average_sale).toFixed(2)}`;
  }

  if (msg.includes("bajo") || msg.includes("alerta")) {
    if (!summary.lowStock.length) {
      return "No hay productos con stock bajo en la base local.";
    }
    const low = summary.lowStock.map((p) => `- ${p.name}: ${p.stock}/${p.stock_minimum}`).join("\n");
    return `Alertas de stock local:\n${low}`;
  }

  return "Estoy en modo offline local. Puedo ayudarte con inventario, ventas y alertas de stock usando SQLite.";
}

module.exports = { initDb, getProducts, saveProduct, getSalesSummary, importFromSqlDump, askOfflineChat };
