<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reconteo de Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        body { background:#1a1a2e; color:#eaeaea; }
        .container { max-width: 1400px; margin: 32px auto; padding: 0 16px; }
        .table-card {
            background:#16213e;
            border:1px solid rgba(0,255,240,0.16);
            border-radius:14px;
            padding:24px;
            box-shadow: 0 10px 24px rgba(0,0,0,0.25);
        }
        .table-wrap { overflow:auto; border-radius:10px; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; min-width:900px; }
        thead th { background:#0f3460; color:#a0fdfd; padding:14px; text-align:left; font-weight:600; border-bottom:2px solid rgba(0,255,240,0.3); }
        tbody td { padding:12px 14px; border-top:1px solid rgba(255,255,255,0.08); }
        tbody tr:nth-child(even) { background: rgba(255,255,255,0.02); }
        tbody tr:hover { background: rgba(0,255,240,0.08); }
        
        .input-field {
            background:#111b38;
            color:#eaf7ff;
            border:1px solid rgba(0,255,240,0.25);
            border-radius:6px;
            padding:8px;
            width:100%;
            box-sizing:border-box;
            text-align:center;
        }
        .input-field:focus { outline:none; border-color:#00ffff; box-shadow:0 0 8px rgba(0,255,255,0.3); }
        
        .diff-neutral { color:#eaeaea; }
        .diff-positive { color:#9af7c8; font-weight:700; }
        .diff-negative { color:#ff9ea4; font-weight:700; }
        
        .button-group {
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            justify-content:center;
        }
        .button-apply {
            background:#0a5d5d;
            color:#9af7c8;
            border:1px solid #2fa86d;
            padding:10px 16px;
            border-radius:6px;
            cursor:pointer;
            font-weight:600;
            transition:all 0.3s;
        }
        .button-apply:hover { background:#0d7a7a; box-shadow:0 0 12px rgba(47,168,109,0.4); }
        .button-apply:disabled { opacity:0.5; cursor:not-allowed; }
        
        .button-cancel {
            background:#3a1010;
            color:#ff9ea4;
            border:1px solid #a04040;
            padding:10px 16px;
            border-radius:6px;
            cursor:pointer;
            font-weight:600;
            transition:all 0.3s;
        }
        .button-cancel:hover { background:#4a1515; box-shadow:0 0 12px rgba(160,64,64,0.4); }
        
        .stats-bar {
            display:flex;
            gap:24px;
            margin-bottom:20px;
            flex-wrap:wrap;
        }
        .stat-item {
            display:flex;
            flex-direction:column;
            gap:4px;
        }
        .stat-label { color:#a0fdfd; font-size:12px; font-weight:600; text-transform:uppercase; }
        .stat-value { color:#00fff0; font-size:20px; font-weight:700; }
        
        .message {
            padding:12px 16px;
            border-radius:8px;
            margin-bottom:16px;
        }
        .error { background:#3a1010; color:#ff9ea4; border:1px solid #a04040; }
        .success { background:#173f2f; color:#9af7c8; border:1px solid #2fa86d; }
        
        @media (max-width: 1024px) {
            table { min-width:800px; }
            .stats-bar { gap:16px; }
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
    <h1>Reconteo de Inventario</h1>
    <p>Ingresa el stock físico de cada producto. Las diferencias se calcularán automáticamente.</p>

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
                            <th style="width:80px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="recountTableBody">
                        <?php foreach ($productos as $prod): ?>
                            <tr data-product-id="<?php echo (int)$prod->id; ?>" data-system-stock="<?php echo (int)$prod->stock; ?>">
                                <td><?php echo htmlspecialchars($prod->name); ?></td>
                                <td style="text-align:center;font-weight:700;color:#a0fdfd;"><?php echo (int)$prod->stock; ?></td>
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

            <div class="button-group">
                <button type="button" class="button-apply" id="applyAllBtn" disabled>Aplicar Todos los Cambios</button>
                <button type="button" class="button-cancel" id="clearBtn">Limpiar Formulario</button>
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
