<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconteo de Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body { background:#091123; color:#e8f5ff; }
        .container { max-width: 1320px; margin: 32px auto; padding: 0 18px; }
        .page-header {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:18px;
            margin-bottom:24px;
            flex-wrap:wrap;
        }
        .page-header h1 {
            margin:0;
            font-size:2.2rem;
            letter-spacing:0.02em;
            color:#f1fbff;
        }
        .page-description {
            margin:12px 0 0;
            color:#b5dfff;
            max-width:760px;
            line-height:1.6;
        }
        .page-actions {
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        .page-actions .button-secondary {
            background:rgba(255,255,255,0.08);
            color:#a0fdfd;
            border:1px solid rgba(160,255,255,0.25);
            padding:12px 18px;
            border-radius:10px;
            cursor:pointer;
            min-width:180px;
            transition:all 0.3s ease;
        }
        .page-actions .button-secondary:hover { background:rgba(255,255,255,0.14); }

        .table-card {
            background:linear-gradient(180deg, rgba(9,17,35,0.94) 0%, rgba(17,25,56,0.98) 100%);
            border:1px solid rgba(0,255,240,0.14);
            border-radius:24px;
            padding:28px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
        }
        .table-wrap { overflow:auto; border-radius:18px; margin-bottom:20px; border:1px solid rgba(255,255,255,0.08); }
        table { width:100%; border-collapse:collapse; min-width:980px; }
        thead th { background:rgba(7,18,46,0.96); color:#b7faff; padding:18px 16px; text-align:left; font-weight:700; border-bottom:1px solid rgba(0,255,240,0.16); letter-spacing:0.01em; }
        tbody td { padding:16px; border-top:1px solid rgba(255,255,255,0.05); color:#dbeeff; }
        tbody tr { transition:background 0.25s ease; }
        tbody tr:nth-child(even) { background: rgba(255,255,255,0.015); }
        tbody tr:hover { background: rgba(0,255,240,0.08); }
        tbody td:first-child { font-weight:600; color:#f5fbff; }
        tbody td:nth-child(2), tbody td:nth-child(4), tbody td:nth-child(5) { text-align:center; }
        tbody td:nth-child(5) button { min-width:100px; }

        .input-field {
            background:rgba(0,20,50,0.85);
            color:#dffbff;
            border:1px solid rgba(0,255,240,0.16);
            border-radius:12px;
            padding:10px 12px;
            width:100%;
            box-sizing:border-box;
            text-align:center;
            transition:all 0.2s ease;
        }
        .input-field:focus { outline:none; border-color:#38f6ff; box-shadow:0 0 18px rgba(0,255,255,0.18); }

        .diff-neutral { color:#d2e8ff; font-weight:600; }
        .diff-positive { color:#7af5aa; font-weight:700; }
        .diff-negative { color:#ff92a4; font-weight:700; }

        .button-group {
            display:flex;
            gap:14px;
            flex-wrap:wrap;
            justify-content:flex-end;
            margin-top:12px;
        }
        .button-apply {
            background:linear-gradient(135deg, #0aa6a6 0%, #0f9292 100%);
            color:#ecfffd;
            border:1px solid rgba(50,230,205,0.35);
            padding:12px 18px;
            border-radius:14px;
            cursor:pointer;
            font-weight:700;
            letter-spacing:0.01em;
            transition:all 0.22s ease;
        }
        .button-apply:hover { transform:translateY(-1px); box-shadow:0 12px 30px rgba(10,166,166,0.22); }
        .button-apply:disabled { opacity:0.45; cursor:not-allowed; transform:none; box-shadow:none; }

        .button-secondary {
            background:rgba(255,255,255,0.06);
            color:#bdefff;
            border:1px solid rgba(90,205,235,0.18);
            padding:12px 18px;
            border-radius:14px;
            cursor:pointer;
            font-weight:700;
            transition:all 0.2s ease;
        }
        .button-secondary:hover { background:rgba(255,255,255,0.12); }

        .stats-bar {
            display:grid;
            grid-template-columns:repeat(3,minmax(180px,1fr));
            gap:18px;
            margin-bottom:24px;
        }
        .stat-item {
            padding:18px 20px;
            border-radius:18px;
            background:rgba(255,255,255,0.03);
            border:1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }
        .stat-label { color:#95e8ff; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; }
        .stat-value { color:#8cffdf; font-size:2rem; font-weight:800; }

        .message { padding:14px 18px; border-radius:16px; margin-bottom:20px; }
        .error { background:rgba(255,37,81,0.14); color:#ffb7d0; border:1px solid rgba(255,37,81,0.24); }
        .success { background:rgba(42,190,152,0.14); color:#d8fff2; border:1px solid rgba(42,190,152,0.24); }

        @media (max-width: 1080px) {
            .stats-bar { grid-template-columns:1fr; }
            .page-header { align-items:flex-start; }
            .page-actions { justify-content:flex-start; }
            table { min-width:720px; }
        }
        @media (max-width: 720px) {
            .container { padding: 0 12px; }
            thead th, tbody td { padding:12px 10px; }
            .page-header h1 { font-size:1.75rem; }
            .button-group { justify-content:center; }
        }
    </style>
</head>
<body>
<?php
$role = $_SESSION['role'] ?? '';
$useEmployeeHeader = in_array($role, ['comercial', 'logistica', 'finanzas', 'estrategico', 'gerencia', 'supervisor'], true);
if ($useEmployeeHeader) {
    include __DIR__ . '/../../employee/partials/header.php';
} else {
    include __DIR__ . '/../partials/header.php';
}
?>
<div class="container">
    <div class="page-header">
        <div>
            <h1>Reconteo de Inventario</h1>
            <p class="page-description">Ingresa el stock físico de cada producto. Las diferencias se calcularán automáticamente para que puedas aplicar ajustes con un solo clic.</p>
        </div>
        <div class="page-actions">
            <button type="button" class="button-secondary" id="clearBtn">Limpiar Formulario</button>
            <button type="button" class="button-apply" id="applyAllBtn" disabled>Aplicar Todos los Cambios</button>
        </div>
    </div>

    <?php 
        $error = $error ?? '';
        $success = $success ?? '';
        
        if (isset($_SESSION['recount_error'])) {
            $error = $_SESSION['recount_error'];
            unset($_SESSION['recount_error']);
        }
        if (isset($_SESSION['recount_success'])) {
            $success = $_SESSION['recount_success'];
            unset($_SESSION['recount_success']);
        }
        if (isset($_SESSION['recount_warning'])) {
            $warning = $_SESSION['recount_warning'];
            unset($_SESSION['recount_warning']);
        }
    ?>

    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php if (isset($_SESSION['recount_error_details'])): ?>
            <div class="message error" style="font-size:12px;margin-top:8px;">Detalles: <?php echo htmlspecialchars($_SESSION['recount_error_details']); ?></div>
            <?php unset($_SESSION['recount_error_details']); ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($warning)): ?>
        <div class="message success"><?php echo htmlspecialchars($warning); ?></div>
        <?php if (isset($_SESSION['recount_error_details'])): ?>
            <div class="message error" style="font-size:12px;margin-top:8px;">Detalles: <?php echo htmlspecialchars($_SESSION['recount_error_details']); ?></div>
            <?php unset($_SESSION['recount_error_details']); ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="table-card">
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-label">Total de productos</span>
                <span class="stat-value" id="totalProducts">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Con diferencias</span>
                <span class="stat-value" id="productsWithDiff">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total diferencia</span>
                <span class="stat-value" id="totalDiff">0</span>
            </div>
        </div>

        <form id="recountForm" method="post" action="<?php echo BASE_URL; ?>index.php?controller=admin&action=processInventoryRecountBatch">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock Sistema</th>
                            <th>Stock Físico</th>
                            <th>Diferencia</th>
                            <th style="width:120px; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="recountTableBody">
                        <?php foreach ($productos as $prod): ?>
                            <tr data-product-id="<?php echo (int)$prod->id; ?>" data-system-stock="<?php echo (int)$prod->stock; ?>">
                                <td><?php echo htmlspecialchars($prod->name); ?></td>
                                <td style="text-align:center;font-weight:700;color:#8cffdf;"><?php echo (int)$prod->stock; ?></td>
                                <td>
                                    <input 
                                        type="number" 
                                        class="input-field physical-stock" 
                                        min="0" 
                                        placeholder="0"
                                        data-product-id="<?php echo (int)$prod->id; ?>"
                                    >
                                </td>
                                <td style="text-align:center;">
                                    <span class="difference-value diff-neutral">—</span>
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="button-apply apply-single" data-product-id="<?php echo (int)$prod->id; ?>" disabled>Aplicar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('recountForm');
    const tableBody = document.getElementById('recountTableBody');
    const totalProductsEl = document.getElementById('totalProducts');
    const productsWithDiffEl = document.getElementById('productsWithDiff');
    const totalDiffEl = document.getElementById('totalDiff');
    const applyAllBtn = document.getElementById('applyAllBtn');
    const clearBtn = document.getElementById('clearBtn');

    totalProductsEl.textContent = tableBody.querySelectorAll('tr').length;

    function updateDifference(row) {
        const systemStock = parseInt(row.dataset.systemStock);
        const physicalInput = row.querySelector('.physical-stock');
        const diffSpan = row.querySelector('.difference-value');
        const applyBtn = row.querySelector('.apply-single');

        if (physicalInput.value === '') {
            diffSpan.textContent = '—';
            diffSpan.className = 'difference-value diff-neutral';
            applyBtn.disabled = true;
            return;
        }

        const physicalStock = parseInt(physicalInput.value);
        const difference = physicalStock - systemStock;

        diffSpan.textContent = difference > 0 ? '+' + difference : difference;
        if (difference === 0) {
            diffSpan.className = 'difference-value diff-neutral';
        } else if (difference > 0) {
            diffSpan.className = 'difference-value diff-positive';
        } else {
            diffSpan.className = 'difference-value diff-negative';
        }

        applyBtn.disabled = false;
    }

    function updateStats() {
        let productsWithDiff = 0;
        let totalDiff = 0;

        tableBody.querySelectorAll('tr').forEach(row => {
            const systemStock = parseInt(row.dataset.systemStock);
            const physicalInput = row.querySelector('.physical-stock');

            if (physicalInput.value !== '') {
                const physicalStock = parseInt(physicalInput.value);
                const difference = physicalStock - systemStock;
                if (difference !== 0) {
                    productsWithDiff++;
                    totalDiff += difference;
                }
            }
        });

        productsWithDiffEl.textContent = productsWithDiff;
        totalDiffEl.textContent = totalDiff > 0 ? '+' + totalDiff : totalDiff;
        applyAllBtn.disabled = productsWithDiff === 0;
    }

    // Event listeners for physical stock inputs
    tableBody.querySelectorAll('.physical-stock').forEach(input => {
        input.addEventListener('input', function() {
            updateDifference(this.closest('tr'));
            updateStats();
        });
    });

    // Apply single product
    tableBody.querySelectorAll('.apply-single').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = parseInt(this.dataset.productId);
            const row = this.closest('tr');
            const physicalStock = row.querySelector('.physical-stock').value;

            if (physicalStock === '' || physicalStock < 0) {
                alert('Ingresa un stock físico válido');
                return;
            }

            const singleForm = document.createElement('form');
            singleForm.method = 'post';
            singleForm.action = '<?php echo BASE_URL; ?>index.php?controller=admin&action=processInventoryRecount';
            
            singleForm.innerHTML = `
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="physical_stock" value="${physicalStock}">
            `;

            document.body.appendChild(singleForm);
            singleForm.submit();
        });
    });

    // Apply all
    applyAllBtn.addEventListener('click', function(e) {
        e.preventDefault();

        const changes = [];
        tableBody.querySelectorAll('tr').forEach(row => {
            const physicalInput = row.querySelector('.physical-stock');
            if (physicalInput.value !== '') {
                const productId = parseInt(row.dataset.productId);
                const physicalStock = parseInt(physicalInput.value);
                const systemStock = parseInt(row.dataset.systemStock);
                const difference = physicalStock - systemStock;

                if (difference !== 0) {
                    changes.push({
                        product_id: productId,
                        physical_stock: physicalStock,
                        difference: difference
                    });
                }
            }
        });

        if (changes.length === 0) {
            alert('No hay cambios para aplicar');
            return;
        }

        const batchForm = document.createElement('form');
        batchForm.method = 'post';
        batchForm.action = form.action;
        batchForm.innerHTML = '<input type="hidden" name="batch_mode" value="1">';

        changes.forEach((change, index) => {
            batchForm.innerHTML += `
                <input type="hidden" name="changes[${index}][product_id]" value="${change.product_id}">
                <input type="hidden" name="changes[${index}][physical_stock]" value="${change.physical_stock}">
            `;
        });

        document.body.appendChild(batchForm);
        batchForm.submit();
    });

    // Clear form
    clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        tableBody.querySelectorAll('.physical-stock').forEach(input => {
            input.value = '';
            updateDifference(input.closest('tr'));
        });
        updateStats();
    });
});
</script>
</body>
</html>
