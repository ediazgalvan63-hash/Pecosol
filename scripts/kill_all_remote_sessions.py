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
    cur.execute(
        "SELECT ID FROM INFORMATION_SCHEMA.PROCESSLIST "
        "WHERE ID != CONNECTION_ID() AND USER != 'event_scheduler'"
    )
    pids = [row[0] for row in cur.fetchall()]
    for pid in pids:
        try:
            cur.execute(f'KILL {pid}')
            print(f'KILLED {pid}')
        except Exception as e:
            print(f'FAILED {pid}: {e}')
conn.close()
print('DONE')
