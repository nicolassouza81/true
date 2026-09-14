<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CompanyLogoModel;
use App\Models\TestimonialModel;
use Core\Auth;
use Core\Cache;
use Core\Request;
use Core\Response;

final class HomeController
{
    public function index(Request $request): void
    {
        // Cache de 10 minutos — dados raramente mudam e evita queries a cada visita
        try {
            $testimonials = Cache::remember('home.testimonials', 600, fn () =>
                (new TestimonialModel())->featured(6)
            );

            $logos = Cache::remember('home.logos', 600, fn () =>
                (new CompanyLogoModel())->active()
            );
            $testimonialModel = new TestimonialModel();
        } catch (\Throwable $e) {
            $testimonials = [];
            $logos = [];
            $testimonialModel = null;
        }

        Response::view('home/index', [
            'title'        => 'Início',
            'user'         => Auth::user(),
            'testimonials' => $testimonials,
            'logos'        => $logos,
            'testimonialModel' => $testimonialModel,
        ]);
    }
}
