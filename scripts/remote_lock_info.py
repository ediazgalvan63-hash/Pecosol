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
    cur.execute('SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, INFO FROM INFORMATION_SCHEMA.PROCESSLIST ORDER BY ID')
    for row in cur.fetchall():
        print(row)
    print('--- INNODB TRX ---')
    try:
        cur.execute('SELECT trx_id, trx_state, trx_started, trx_mysql_thread_id, trx_query FROM INFORMATION_SCHEMA.INNODB_TRX')
        for row in cur.fetchall():
            print(row)
    except Exception as e:
        print('INNODB_TRX ERROR', e)
    print('--- INNODB LOCKS ---')
    try:
        cur.execute('SELECT lock_id, lock_trx_id, lock_mode, lock_type, lock_table, lock_index, lock_data FROM INFORMATION_SCHEMA.INNODB_LOCKS')
        for row in cur.fetchall():
            print(row)
    except Exception as e:
        print('INNODB_LOCKS ERROR', e)
    print('--- INNODB LOCK WAITS ---')
    try:
        cur.execute('SELECT requesting_trx_id, requested_lock_id, blocking_trx_id FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS')
        for row in cur.fetchall():
            print(row)
    except Exception as e:
        print('INNODB_LOCK_WAITS ERROR', e)
conn.close()
