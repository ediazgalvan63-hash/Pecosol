#!/usr/bin/env python3
import pymysql

local = pymysql.connect(
    host='127.0.0.1',
    port=3306,
    user='root',
    password='',
    database='pecosol_db',
    autocommit=True,
)
remote = pymysql.connect(
    host='switchback.proxy.rlwy.net',
    port=10989,
    user='root',
    password='LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
    database='railway',
    autocommit=True,
)

tables = [
    'users',
    'employees',
    'stock_movements',
    'products',
    'purchases',
    'sales',
    'work_orders',
    'audit_logs',
]
print('TABLE\tLOCAL\tREMOTE')
for name in tables:
    try:
        with local.cursor() as lc, remote.cursor() as rc:
            lc.execute(f'SELECT COUNT(*) FROM `{name}`')
            rc.execute(f'SELECT COUNT(*) FROM `{name}`')
            print(f'{name}\t{lc.fetchone()[0]}\t{rc.fetchone()[0]}')
    except Exception as e:
        print(f'{name}\tERROR\t{e}')
