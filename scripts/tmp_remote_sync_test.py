import pymysql
import traceback

params = dict(
    host='switchback.proxy.rlwy.net',
    port=10989,
    user='root',
    password='LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    database='railway',
    connect_timeout=20,
    read_timeout=120,
    write_timeout=120,
    autocommit=True,
    charset='utf8mb4',
)

for i in range(2):
    try:
        print('connecting', i)
        conn = pymysql.connect(**params)
        with conn.cursor() as cur:
            cur.execute('DROP TABLE IF EXISTS `audit_logs`')
            print('dropped audit_logs')
            cur.execute('SHOW TABLES')
            print('tables:', [r[0] for r in cur.fetchall()])
        conn.close()
        break
    except Exception as e:
        print('error', i, type(e).__name__, e)
        traceback.print_exc()
