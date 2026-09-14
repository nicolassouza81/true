<?php

declare(strict_types=1);

namespace App\Models;

final class CompanyLogoModel extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct('company_logos');
    }

    /** Retorna logos ativos ordenados por sort_order. */
    public function active(): array
    {
        return $this->all(['is_active' => 1], 'sort_order', 'ASC');
    }

    /** Texto alternativo com fallback para o nome da empresa. */
    public function altText(array $logo): string
    {
        return $logo['alt_text'] ?: $logo['name'];
    }
}
