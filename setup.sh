#!/bin/bash

# setup.sh — Configura o projeto do zero em ambiente local.
# Uso: bash setup.sh [--seed]
#
# O que faz:
#   1. Copia .env.example -> .env (se ainda não existe)
#   2. Verifica o ambiente (check.php)
#   3. Executa as migrations
#   4. Opcionalmente executa os seeds (--seed)

set -e

SEED=false
for arg in "$@"; do
    [ "$arg" = "--seed" ] && SEED=true
done

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo ""
echo "======================================"
echo "   Setup — Projeto Base"
echo "======================================"
echo ""

# 1. .env
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${YELLOW}⚠  .env criado a partir de .env.example. Preencha as credenciais antes de continuar.${NC}"
    echo ""
    echo "   Edite o arquivo .env e execute novamente: bash setup.sh"
    echo ""
    exit 0
else
    echo -e "${GREEN}✓${NC}  .env encontrado"
fi

# 2. Permissões de storage
mkdir -p storage/cache storage/logs
chmod -R 775 storage/ 2>/dev/null || true
echo -e "${GREEN}✓${NC}  Diretórios storage/ com permissão de escrita"

# 3. Verificação de ambiente
echo ""
echo "--- Verificando ambiente ---"
echo ""
php check.php || {
    echo ""
    echo -e "${RED}✗  Corrija os erros acima e execute novamente.${NC}"
    exit 1
}

# 4. Migrations
echo "--- Executando migrations ---"
echo ""

# Tenta criar o banco caso não exista (lê DB_DATABASE do .env)
DB_NAME=$(grep -E '^DB_DATABASE=' .env | cut -d'=' -f2 | tr -d '"'"'"' ')
DB_USER=$(grep -E '^DB_USERNAME=' .env | cut -d'=' -f2 | tr -d '"'"'"' ')
DB_PASS=$(grep -E '^DB_PASSWORD=' .env | cut -d'=' -f2 | tr -d '"'"'"' ')
DB_HOST=$(grep -E '^DB_HOST=' .env | cut -d'=' -f2 | tr -d '"'"'"' ')

if [ -n "$DB_NAME" ] && command -v mysql &>/dev/null; then
    mysql -h"${DB_HOST:-127.0.0.1}" -u"$DB_USER" -p"$DB_PASS" \
      -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null \
      && echo -e "${GREEN}✓${NC}  Banco '$DB_NAME' verificado/criado" \
      || echo -e "${YELLOW}⚠${NC}  Não foi possível criar o banco automaticamente. Crie-o manualmente."
fi

php database/migrate.php

# 5. Seeds (opcional)
if [ "$SEED" = true ]; then
    echo ""
    echo "--- Executando seeds ---"
    php database/seeds/seed.php
fi

echo ""
echo -e "${GREEN}======================================"
echo "   Projeto pronto!"
echo -e "======================================${NC}"
echo ""
echo "   Servidor local: php -S localhost:8000"
echo "   Admin padrão:   admin@example.com / password  (somente com --seed)"
echo ""
