-- Ejecutar SOLO en local para habilitar perfiles por rol.
-- Ajusta la columna users.role para permitir nuevos perfiles.
-- Nota: si tu columna no es ENUM, adapta este script.

ALTER TABLE users
  MODIFY COLUMN role ENUM(
    'admin',
    'employee',
    'gerencia',
    'comercial',
    'logistica',
    'finanzas',
    'estrategico'
  ) NOT NULL;

