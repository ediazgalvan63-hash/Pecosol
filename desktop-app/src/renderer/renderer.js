async function refreshProducts() {
  const products = await window.pecosolApi.listProducts();
  const tbody = document.getElementById("productsBody");
  tbody.innerHTML = "";

  for (const p of products) {
    const row = document.createElement("tr");
    row.innerHTML = `<td>${p.id}</td><td>${p.name}</td><td>${p.price}</td><td>${p.stock}</td>`;
    tbody.appendChild(row);
  }
}

async function refreshSummary() {
  const summary = await window.pecosolApi.getSalesSummary();
  document.getElementById("summary").textContent = JSON.stringify(summary, null, 2);
}

document.getElementById("saveBtn").addEventListener("click", async () => {
  await window.pecosolApi.saveProduct({
    name: document.getElementById("name").value,
    description: document.getElementById("description").value,
    price: document.getElementById("price").value,
    stock: document.getElementById("stock").value,
    stock_minimum: document.getElementById("stockMin").value
  });

  await refreshProducts();
  await refreshSummary();
});

document.getElementById("chatBtn").addEventListener("click", async () => {
  const input = document.getElementById("chatInput");
  const out = document.getElementById("chatOutput");
  const question = input.value.trim();
  if (!question) return;

  const response = await window.pecosolApi.askOfflineChat(question);
  out.textContent = response;
});

refreshProducts();
refreshSummary();
