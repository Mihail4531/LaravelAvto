<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Доступ запрещён · АвтоСервис</title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink-100: #EAEEF3;
            --ink-200: #C5CCD5;
            --ink-300: #9BA4AF;
            --ink-400: #8C95A0;
            --ink-500: #7C8591;
            --ink-600: #353D47;
            --ink-700: #232A33;
            --ink-800: #161A20;
            --ink-900: #0B0D10;
            --ink-950: #060709;
            --primary-300: #6FA8FF;
            --primary-400: #3D8BFF;
            --primary-500: #1A6DFF;
            --primary-600: #0B57E0;
            --font-sans: 'Inter Tight', ui-sans-serif, system-ui, sans-serif;
            --font-display: 'Sora', 'Inter Tight', sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            min-height: 100vh;
            font-family: var(--font-sans);
            color: var(--ink-200);
            background: var(--ink-900);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Техническая сетка-подложка — как на публичном сайте */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Кобальтовое зарево */
        .glow {
            position: fixed;
            width: 620px;
            height: 620px;
            border-radius: 9999px;
            filter: blur(110px);
            opacity: .45;
            pointer-events: none;
            z-index: 0;
            background: rgba(26, 109, 255, .22);
            top: -240px;
            right: -200px;
            animation: blob 22s ease-in-out infinite;
        }
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(34px, -44px) scale(1.08); }
            66%      { transform: translate(-26px, 26px) scale(.95); }
        }

        .panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            text-align: center;
            animation: snapIn .85s cubic-bezier(.16, 1, .3, 1) backwards;
        }
        @keyframes snapIn {
            0%   { opacity: 0; transform: translateY(40px) scale(.98); filter: blur(6px); }
            60%  { filter: blur(0); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Бейдж-логотип */
        .badge {
            width: 52px; height: 52px;
            margin: 0 auto 2.25rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, var(--primary-400), var(--primary-600));
            color: #fff;
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 24px;
            clip-path: polygon(0 0, 100% 0, 100% 75%, 75% 100%, 0 100%);
            box-shadow: 0 12px 30px -10px rgba(26, 109, 255, .6);
        }

        .code {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: clamp(5.5rem, 22vw, 9rem);
            line-height: .9;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, var(--primary-300) 0%, var(--primary-600) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            margin-top: 1.25rem;
            font-family: var(--font-mono);
            font-size: 11px;
            letter-spacing: .22em;
            text-transform: uppercase;
            font-weight: 500;
            color: var(--primary-400);
        }
        .eyebrow::before,
        .eyebrow::after {
            content: "";
            width: 28px; height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-500));
        }
        .eyebrow::after {
            background: linear-gradient(90deg, var(--primary-500), transparent);
        }

        h1 {
            font-family: var(--font-display);
            font-weight: 700;
            color: var(--ink-100);
            font-size: clamp(1.5rem, 5vw, 2rem);
            letter-spacing: -0.02em;
            margin-top: 1rem;
        }

        .lead {
            margin: 1rem auto 0;
            max-width: 420px;
            font-size: 15px;
            line-height: 1.65;
            color: var(--ink-300);
        }

        /* Сообщение от исключения, если оно задано */
        .detail {
            margin: 1.5rem auto 0;
            max-width: 460px;
            padding: .85rem 1.1rem;
            background: var(--ink-800);
            border: 1px solid var(--ink-700);
            border-left: 2px solid var(--primary-500);
            font-family: var(--font-mono);
            font-size: 12.5px;
            line-height: 1.5;
            color: var(--ink-200);
            text-align: left;
        }

        .actions {
            margin-top: 2.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: .85rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .9rem 1.6rem;
            font-family: var(--font-sans);
            font-weight: 700;
            font-size: 13px;
            letter-spacing: .04em;
            text-transform: uppercase;
            text-decoration: none;
            border-radius: 2px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform .3s cubic-bezier(.16, 1, .3, 1), box-shadow .3s, border-color .3s, color .3s, background .3s;
        }
        .btn svg { width: 16px; height: 16px; }

        .btn-primary {
            background: linear-gradient(145deg, var(--primary-400), var(--primary-600));
            color: #fff;
            box-shadow: 0 10px 30px -12px rgba(26, 109, 255, .6);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px -10px rgba(26, 109, 255, .75);
        }

        .btn-ghost {
            background: transparent;
            color: var(--ink-100);
            border-color: var(--ink-600);
        }
        .btn-ghost:hover {
            border-color: var(--primary-500);
            color: var(--primary-400);
            background: rgba(26, 109, 255, .06);
        }

        @media (prefers-reduced-motion: reduce) {
            .panel, .glow { animation: none !important; }
            .btn { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="glow"></div>

    <main class="panel">
        <div class="badge" aria-hidden="true">A</div>

        <div class="code">403</div>
        <div class="eyebrow">Доступ запрещён</div>

        <h1>У вас нет прав для этой страницы</h1>

        <p class="lead">
            Похоже, ваша роль не позволяет открыть этот раздел. Если вам нужен доступ —
            обратитесь к управляющему.
        </p>

        @if(isset($exception) && $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.')
            <p class="detail">{{ $exception->getMessage() }}</p>
        @endif

        <div class="actions">
            <a href="{{ url('/admin') }}" class="btn btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                В панель управления
            </a>
            <a href="javascript:history.back()" class="btn btn-ghost">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>
    </main>
</body>
</html>
