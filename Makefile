# Makefile — Atalhos de desenvolvimento
# Uso: make <comando>
#
# Requer: make, php, bash

.PHONY: setup seed migrate check serve cache-clear help

## Configura o projeto (copia .env, migrations)
setup:
	@bash setup.sh

## Configura o projeto + popula com dados de exemplo
seed:
	@bash setup.sh --seed

## Executa apenas as migrations pendentes
migrate:
	@php database/migrate.php

## Verifica se o ambiente está pronto
check:
	@php check.php

## Sobe servidor de desenvolvimento na porta 8000
serve:
	@echo "Servidor rodando em http://localhost:8000"
	@php -S localhost:8000

## Limpa o cache de arquivos (storage/cache)
cache-clear:
	@find storage/cache -name "*.cache" -delete 2>/dev/null || true
	@echo "Cache limpo."

## Exibe esta ajuda
help:
	@echo ""
	@echo "Comandos disponíveis:"
	@grep -E '^##' Makefile | sed 's/## /  /'
	@echo ""
