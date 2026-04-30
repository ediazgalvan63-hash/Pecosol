const { contextBridge, ipcRenderer } = require("electron");

contextBridge.exposeInMainWorld("pecosolApi", {
  listProducts: () => ipcRenderer.invoke("products:list"),
  saveProduct: (payload) => ipcRenderer.invoke("products:save", payload),
  getSalesSummary: () => ipcRenderer.invoke("sales:summary"),
  askOfflineChat: (message) => ipcRenderer.invoke("chat:ask", message),
  importSqlDump: (sqlPath) => ipcRenderer.invoke("db:import", sqlPath)
});
