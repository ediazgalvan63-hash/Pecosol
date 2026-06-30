<?php
header('Content-Type: text/plain');
// Muestra si la variable existe (no pegues la salida aquí si contiene la clave)
echo 'OPENAI_API_KEY=' . (getenv('OPENAI_API_KEY') ?: '<VACIA>');