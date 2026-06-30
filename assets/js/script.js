// Client-side helpers: responsive tables and nav toggles
document.addEventListener('DOMContentLoaded', function () {
	try {
		// Add data-label attributes to table cells so CSS mobile stacks show labels
		var tables = document.querySelectorAll('table.dashboard-table, table#productsTable');
		tables.forEach(function (table) {
			var headers = [];
			var ths = table.querySelectorAll('thead th');
			if (ths && ths.length) {
				ths.forEach(function (th) { headers.push(th.textContent.trim()); });
			}

			var rows = table.querySelectorAll('tbody tr');
			rows.forEach(function (tr) {
				var cells = tr.querySelectorAll('td');
				cells.forEach(function (td, idx) {
					if (!td.hasAttribute('data-label')) {
						var label = headers[idx] || td.getAttribute('data-label') || '';
						if (label) td.setAttribute('data-label', label);
					}
				});
			});
		});
	} catch (e) {
		console.warn('Responsive table helper error', e);
	}

	// Simple mobile nav collapse/expand toggles (adds/removes body.nav-collapsed)
	try {
		var menuToggle = document.querySelector('.menu-toggle');
		if (menuToggle) {
			menuToggle.addEventListener('click', function (ev) {
				document.body.classList.toggle('nav-collapsed');
			});
		}
		var empToggle = document.querySelector('.emp-toggle');
		if (empToggle) {
			empToggle.addEventListener('click', function (ev) {
				document.body.classList.toggle('nav-collapsed');
			});
		}
	} catch (e) {
		console.warn('Nav toggle helper error', e);
	}
});
