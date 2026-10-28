<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Erro interno | TRUE Eventos</title>
    <link rel="icon" href="/assets/brand/true-square-white-on-black.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:ital,opsz,wght@0,9..40,300..800;1,9..40,300..800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            background: #080808;
            color: #ffffff;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        .error-page__code {
            font-family: "Archivo Black", Impact, sans-serif;
            font-weight: 400;
            font-synthesis: none;
            font-size: clamp(5rem, 15vw, 9rem);
            color: transparent;
            -webkit-text-stroke: 1.5px #ff4b19;
            line-height: 0.9;
            margin-bottom: 1rem;
        }
        .error-page__title {
            font-family: "Archivo Black", Impact, sans-serif;
            font-weight: 400;
            font-synthesis: none;
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            text-transform: uppercase;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }
        .error-page__message {
            color: #aaa8a2;
            margin: 0 auto 2rem;
            max-width: 400px;
            line-height: 1.55;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.9rem 1.6rem;
            background: #ff4b19;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transition: background 0.2s, color 0.2s;
        }
        .btn-back:hover {
            background: #dcff36;
            color: #080808;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <p class="error-page__code">500</p>
        <h1 class="error-page__title">Algo deu errado</h1>
        <p class="error-page__message">
            Ocorreu um erro interno. Nossa equipe foi notificada.
            Tente novamente em instantes.
        </p>
        <a href="/" class="btn-back">Voltar ao início <span>→</span></a>
    </div>
</body>
</html>
