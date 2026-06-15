#!/usr/bin/env python3
import pathlib
import pymysql

SQL_PATH = pathlib.Path('pecosol_db.sql')
SQL_TEXT = SQL_PATH.read_text(encoding='utf8')


def split_sql(sql_text):
    statements = []
    current = []
    in_single = False
    in_double = False
    in_backtick = False
    in_block_comment = False
    i = 0
    while i < len(sql_text):
        ch = sql_text[i]
        nxt = sql_text[i + 1] if i + 1 < len(sql_text) else ''
        if in_block_comment:
            if ch == '*' and nxt == '/':
                current.append(ch)
                current.append(nxt)
                i += 2
                in_block_comment = False
                continue
            current.append(ch)
            i += 1
            continue
        if not in_single and not in_double and not in_backtick and ch == '/' and nxt == '*':
            # Preserve versioned MySQL comments /*! ... */ as statements
            if i + 2 < len(sql_text) and sql_text[i + 2] == '!':
                current.append(ch)
                i += 1
                continue
            in_block_comment = True
            current.append(ch)
            current.append(nxt)
            i += 2
            continue
        if not in_double and not in_backtick and ch == "'" and not in_single:
            in_single = True
            current.append(ch)
            i += 1
            continue
        if in_single and ch == "'" and not (sql_text[i - 1] == '\\' and sql_text[i - 2:i] != "\\"):
            in_single = False
            current.append(ch)
            i += 1
            continue
        if not in_single and not in_backtick and ch == '"' and not in_double:
            in_double = True
            current.append(ch)
            i += 1
            continue
        if in_double and ch == '"' and not (sql_text[i - 1] == '\\' and sql_text[i - 2:i] != "\\"):
            in_double = False
            current.append(ch)
            i += 1
            continue
        if not in_single and not in_double and ch == '`' and not in_backtick:
            in_backtick = True
            current.append(ch)
            i += 1
            continue
        if in_backtick and ch == '`':
            in_backtick = False
            current.append(ch)
            i += 1
            continue
        if not in_single and not in_double and not in_backtick and ch == ';':
            stmt = ''.join(current).strip()
            if stmt:
                statements.append(stmt)
            current = []
            i += 1
            continue
        current.append(ch)
        i += 1
    last = ''.join(current).strip()
    if last:
        statements.append(last)
    return statements


def normalize_statement(stmt):
    lines = stmt.splitlines()
    first_noncomment = None
    for line in lines:
        stripped = line.strip()
        if not stripped:
            continue
        if stripped.startswith('--') or stripped.startswith('#'):
            continue
        first_noncomment = stripped
        break
    if not first_noncomment:
        return None
    if first_noncomment.upper().startswith('LOCK TABLES'):
        return None
    if first_noncomment.upper().startswith('UNLOCK TABLES'):
        return None
    return stmt


def main():
    statements = split_sql(SQL_TEXT)
    print(f'Parsed {len(statements)} statements from {SQL_PATH.name}')

    conn = pymysql.connect(
        host='switchback.proxy.rlwy.net',
        port=10989,
        user='root',
        password='LTGlzNjlJkBgNAjhYhfSGUpHUTAyQcya',
        database='railway',
        autocommit=True,
        connect_timeout=30,
        read_timeout=300,
        write_timeout=300,
        charset='utf8mb4',
        client_flag=0,
    )
    try:
        with conn.cursor() as cur:
            cur.execute('SET FOREIGN_KEY_CHECKS=0')
            cur.execute('SET UNIQUE_CHECKS=0')
            for idx, stmt in enumerate(statements, 1):
                normalized = normalize_statement(stmt)
                if not normalized:
                    continue
                try:
                    cur.execute(normalized)
                except Exception as e:
                    print('ERROR executing statement', idx)
                    print(normalized[:400])
                    print('EXCEPTION', type(e).__name__, e)
                    raise
                if idx % 10 == 0:
                    print('Executed', idx, 'statements')
            cur.execute('SET FOREIGN_KEY_CHECKS=1')
            cur.execute('SET UNIQUE_CHECKS=1')
        print('IMPORT COMPLETE')
    finally:
        conn.close()


if __name__ == '__main__':
    main()
