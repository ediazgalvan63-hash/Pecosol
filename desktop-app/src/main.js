const { app, BrowserWindow, ipcMain } = require("electron");
const path = require("path");
const { initDb, getProducts, saveProduct, getSalesSummary, importFromSqlDump, askOfflineChat } = require("./sqlite");

function createWindow() {
  const win = new BrowserWindow({
    width: 1200,
    height: 760,
    webPreferences: {
      preload: path.join(__dirname, "preload.js"),
      contextIsolation: true,
      nodeIntegration: false
    }
  });

  const localAppUrl = "http://127.0.0.1/pecosol/";
  win.loadURL(localAppUrl).catch(() => {
    win.loadFile(path.join(__dirname, "renderer", "index.html"));
  });
}

app.whenReady().then(() => {
  initDb();
  try {
    const dumpPath = path.resolve(__dirname, "..", "..", "pecosol_db.sql");
    importFromSqlDump(dumpPath);
  } catch (error) {
    console.warn("No se pudo importar pecosol_db.sql al iniciar:", error.message);
  }
  createWindow();

  app.on("activate", () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on("window-all-closed", () => {
  if (process.platform !== "darwin") app.quit();
});

ipcMain.handle("products:list", () => getProducts());
ipcMain.handle("products:save", (_event, payload) => saveProduct(payload));
ipcMain.handle("sales:summary", () => getSalesSummary());
ipcMain.handle("chat:ask", (_event, message) => askOfflineChat(message));
ipcMain.handle("db:import", (_event, sqlPath) => importFromSqlDump(sqlPath));
