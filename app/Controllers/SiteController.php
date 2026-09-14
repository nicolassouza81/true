<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CompanyLogoModel;
use Core\Request;
use Core\Response;

final class SiteController
{
    public function home(Request $request): void
    {
        $logos = (new CompanyLogoModel())->active();
        Response::partial('site/home', ['logos' => $logos]);
    }

    public function about(Request $request): void
    {
        $logos = (new CompanyLogoModel())->active();
        Response::partial('site/about', ['logos' => $logos]);
    }

    public function orcamento(Request $request): void
    {
        Response::partial('site/orcamento');
    }

    public function solucao(Request $request): void
    {
        $slug = (string) $request->param('slug', '');
        // Limpar slug caso venha com extensão .html
        $slug = str_replace('.html', '', $slug);

        $allowed = ['som', 'iluminacao', 'paineis-led', 'estruturas'];
        if (!in_array($slug, $allowed, true)) {
            Response::abort(404);
        }

        Response::partial("site/solucoes/{$slug}");
    }
}
