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
    for pid in [9579, 9601, 9602, 9603, 9606, 9607, 9609, 9611]:
        try:
            cur.execute(f'KILL {pid}')
            print('KILLED', pid)
        except Exception as e:
            print('FAILED', pid, e)
conn.close()
