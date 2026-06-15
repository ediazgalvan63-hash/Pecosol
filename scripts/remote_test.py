#!/usr/bin/env python3
import pymysql

conn = pymysql.connect(
    host='switchback.proxy.rlwy.net',
    port=10989,
    user='root',
    password='LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    database='railway',
    autocommit=True,
)
with conn.cursor() as cur:
    cur.execute('SELECT 1')
    print('SELECT 1 ->', cur.fetchone())
    cur.execute('SHOW PROCESSLIST')
    rows = cur.fetchall()
    print('PROCESSLIST rows', len(rows))
    for r in rows[:10]:
        print(r)
    cur.execute('SELECT table_name FROM information_schema.tables WHERE table_schema=%s', ('railway',))
    tables = [row[0] for row in cur.fetchall()]
    print('TABLES', tables)
conn.close()
