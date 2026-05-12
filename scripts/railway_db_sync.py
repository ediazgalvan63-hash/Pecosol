import pymysql
from pathlib import Path

SQL_FILE = Path(__file__).resolve().parent / 'local_roles_upgrade.sql'
if not SQL_FILE.exists():
    raise SystemExit('Missing scripts/local_roles_upgrade.sql')

with open(SQL_FILE, 'r', encoding='utf-8') as f:
    sql = f.read().strip()

conn = pymysql.connect(
    host='switchback.proxy.rlwy.net',
    port=10989,
    user='root',
    password='LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    db='railway',
    charset='utf8mb4',
    autocommit=True,
)

try:
    with conn.cursor() as cur:
        print('Applying local_roles_upgrade.sql...')
        for stmt in [s.strip() for s in sql.split(';') if s.strip()]:
            print('EXECUTING:', stmt.replace('\n', ' ')[:120])
            cur.execute(stmt)

        print('Ensuring sales.client_name exists...')
        cur.execute("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='sales' AND COLUMN_NAME='client_name'")
        if cur.fetchone()[0] == 0:
            cur.execute("ALTER TABLE sales ADD COLUMN client_name VARCHAR(120) NOT NULL DEFAULT 'Cliente General' AFTER total_price")
            print('Added sales.client_name')
        else:
            print('sales.client_name already exists')

        tables = [
            ('purchases', "CREATE TABLE purchases (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, user_id INT NOT NULL, quantity INT NOT NULL, supplier VARCHAR(120) NOT NULL, notes VARCHAR(255) DEFAULT NULL, purchase_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"),
            ('work_orders', "CREATE TABLE work_orders (id INT AUTO_INCREMENT PRIMARY KEY, client_name VARCHAR(120) NOT NULL, service_type VARCHAR(120) NOT NULL, technician_name VARCHAR(120) NOT NULL, materials_used TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL DEFAULT 'pendiente', sale_id INT NULL, notes VARCHAR(255) DEFAULT NULL, created_by INT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"),
            ('audit_logs', "CREATE TABLE audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, action VARCHAR(30) NOT NULL, entity VARCHAR(40) NOT NULL, entity_id INT NULL, details VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"),
        ]
        for table, statement in tables:
            cur.execute("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=%s", (table,))
            if cur.fetchone()[0] == 0:
                print(f'Creating table {table}...')
                cur.execute(statement)
                print(f'{table} created')
            else:
                print(f'{table} already exists')

    print('Database sync completed successfully.')
finally:
    conn.close()
