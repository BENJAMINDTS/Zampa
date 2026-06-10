{{--
 * Error Pages — Base Layout
 *
 * Standalone layout (no Vite dependency) for all HTTP error pages.
 * Uses inline critical CSS so it renders even when the app is broken.
 *
 * @author BenjaminDTS
 --}}
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') — Zampa</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|playfair-display:400,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #1A1714;
            color: #F1EFEC;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem 5rem;
            position: relative;
            overflow: hidden;
        }

        /* Background */
        .z-bg-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .z-bg-glow-top {
            width: 600px; height: 400px;
            background: radial-gradient(ellipse, rgba(217,123,26,0.13) 0%, transparent 70%);
            top: -160px; left: 50%; transform: translateX(-50%);
        }
        .z-bg-glow-bot {
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(34,211,238,0.06) 0%, transparent 70%);
            bottom: -80px; right: 8%;
        }
        .z-dot-grid {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(circle, rgba(175,166,154,0.07) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* Card */
        .z-card {
            position: relative; z-index: 1;
            display: flex; flex-direction: column;
            align-items: center; text-align: center;
            max-width: 460px; width: 100%;
        }

        /* Zampi */
        .z-mascot {
            width: 148px; height: 138px;
            margin-bottom: 1.75rem;
        }
        .z-mascot svg { width: 100%; height: 100%; }

        /* Animations */
        @keyframes z-float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }
        @keyframes z-sway {
            0%, 100% { transform: translateY(-3px) rotate(-7deg); }
            50%       { transform: translateY(-10px) rotate(7deg); }
        }
        @keyframes z-shake {
            0%, 100% { transform: translateX(0) rotate(0deg); }
            20%      { transform: translateX(-7px) rotate(-4deg); }
            40%      { transform: translateX(7px) rotate(4deg); }
            60%      { transform: translateX(-4px) rotate(-2deg); }
            80%      { transform: translateX(4px) rotate(2deg); }
        }
        @keyframes z-breathe {
            0%, 100% { transform: scale(1) translateY(0); }
            50%       { transform: scale(1.05) translateY(-5px); }
        }
        @keyframes z-pulse {
            0%, 100% { transform: translateY(0) scale(1); }
            30%       { transform: translateY(-9px) scale(1.04); }
            70%       { transform: translateY(-3px) scale(0.97); }
        }
        @keyframes z-tilt {
            0%, 100% { transform: rotate(-12deg) translateY(-2px); }
            50%       { transform: rotate(5deg) translateY(-8px); }
        }

        .z-mood-float   { animation: z-float   3s ease-in-out infinite; }
        .z-mood-sway    { animation: z-sway    2.2s ease-in-out infinite; }
        .z-mood-shake   { animation: z-shake   0.65s ease-in-out infinite; }
        .z-mood-breathe { animation: z-breathe 5s ease-in-out infinite; }
        .z-mood-pulse   { animation: z-pulse   1.3s ease-in-out infinite; }
        .z-mood-tilt    { animation: z-tilt    3s ease-in-out infinite; }

        @media (prefers-reduced-motion: reduce) {
            .z-mood-float, .z-mood-sway, .z-mood-shake,
            .z-mood-breathe, .z-mood-pulse, .z-mood-tilt { animation: none; }
        }

        /* Text */
        .z-code {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(5.5rem, 22vw, 9rem);
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #EFAD55 0%, #D97B1A 55%, #944E12 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }
        .z-tag {
            display: inline-block;
            padding: 0.2rem 0.75rem;
            background: rgba(217,123,26,0.12);
            border: 1px solid rgba(217,123,26,0.28);
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #E7922A;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .z-title {
            font-size: clamp(1.25rem, 4vw, 1.625rem);
            font-weight: 700;
            color: #F1EFEC;
            margin-bottom: 0.625rem;
            line-height: 1.2;
        }
        .z-msg {
            font-size: 0.9375rem;
            color: #8F847A;
            line-height: 1.65;
            max-width: 340px;
            margin-bottom: 2.25rem;
        }

        /* Buttons */
        .z-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; }

        .z-btn-primary {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.6rem 1.375rem;
            background: #D97B1A; color: #fff;
            font-family: inherit; font-weight: 600; font-size: 0.9rem;
            border-radius: 0.5rem; border: none; cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.15s ease;
        }
        .z-btn-primary:hover  { background: #B86315; transform: translateY(-1px); }
        .z-btn-primary:focus  { outline: 2px solid #D97B1A; outline-offset: 3px; }
        .z-btn-primary:active { transform: translateY(0); }

        .z-btn-ghost {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.6rem 1.375rem;
            background: transparent; color: #AFA69A;
            font-family: inherit; font-weight: 500; font-size: 0.9rem;
            border-radius: 0.5rem; border: 1px solid #4A4440; cursor: pointer;
            text-decoration: none;
            transition: border-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }
        .z-btn-ghost:hover  { border-color: #D97B1A; color: #EFAD55; transform: translateY(-1px); }
        .z-btn-ghost:focus  { outline: 2px solid #D97B1A; outline-offset: 3px; }
        .z-btn-ghost:active { transform: translateY(0); }

        /* Footer brand */
        .z-footer {
            position: fixed; bottom: 1.25rem; left: 50%; transform: translateX(-50%);
            z-index: 1; display: flex; align-items: center; gap: 0.4rem;
            color: #4A4440; font-size: 0.8rem; white-space: nowrap;
            text-decoration: none;
            transition: color 0.2s;
        }
        .z-footer:hover { color: #D97B1A; }
        .z-footer-logo { width: 22px; height: 22px; opacity: 0.55; }

        /* Divider dots */
        .z-dots {
            display: flex; gap: 0.4rem; align-items: center;
            margin-bottom: 1.5rem;
        }
        .z-dot {
            width: 4px; height: 4px; border-radius: 50%;
            background: rgba(217,123,26,0.35);
        }
        .z-dot.active { background: #D97B1A; width: 20px; border-radius: 2px; }
    </style>
</head>
<body>

    <div class="z-dot-grid" aria-hidden="true"></div>
    <div class="z-bg-glow z-bg-glow-top" aria-hidden="true"></div>
    <div class="z-bg-glow z-bg-glow-bot" aria-hidden="true"></div>

    <main class="z-card" role="main" aria-labelledby="z-error-heading">

        {{-- Zampi mascot (SVG inlined — no component dependency for error pages) --}}
        @php $zmid = 'zm'.uniqid(); @endphp
        <div class="z-mascot @yield('mood', 'z-mood-float')"
             style="filter: @yield('zampi-filter', 'drop-shadow(0 14px 28px rgba(217,123,26,0.28))');"
             aria-hidden="true">
            <svg viewBox="0 0 120 110" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
                <defs>
                    <radialGradient id="{{ $zmid }}-bT" cx="38%" cy="28%" r="62%">
                        <stop offset="0%"   stop-color="#FBDF6A"/>
                        <stop offset="45%"  stop-color="#E8980C"/>
                        <stop offset="100%" stop-color="#A05500"/>
                    </radialGradient>
                    <radialGradient id="{{ $zmid }}-bB" cx="38%" cy="22%" r="65%">
                        <stop offset="0%"   stop-color="#F5C830"/>
                        <stop offset="55%"  stop-color="#CC7008"/>
                        <stop offset="100%" stop-color="#8B4000"/>
                    </radialGradient>
                    <linearGradient id="{{ $zmid }}-ch" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%"   stop-color="#FFD740"/>
                        <stop offset="100%" stop-color="#F59000"/>
                    </linearGradient>
                    <linearGradient id="{{ $zmid }}-mt" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%"   stop-color="#7B3010"/>
                        <stop offset="100%" stop-color="#4A1800"/>
                    </linearGradient>
                    <linearGradient id="{{ $zmid }}-lt" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%"   stop-color="#5CC830"/>
                        <stop offset="100%" stop-color="#348010"/>
                    </linearGradient>
                    <radialGradient id="{{ $zmid }}-sc" cx="50%" cy="38%" r="55%">
                        <stop offset="0%"   stop-color="#0C0620"/>
                        <stop offset="100%" stop-color="#04000E"/>
                    </radialGradient>
                    <filter id="{{ $zmid }}-sh">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#3A1800" flood-opacity="0.45"/>
                    </filter>
                    <filter id="{{ $zmid }}-gP">
                        <feGaussianBlur stdDeviation="3.5" result="b"/>
                        <feMerge><feMergeNode in="b"/><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                    </filter>
                    <filter id="{{ $zmid }}-gC">
                        <feGaussianBlur stdDeviation="2" result="b"/>
                        <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                    </filter>
                </defs>
                <ellipse cx="60" cy="107" rx="40" ry="4" fill="#2A0800" opacity="0.25"/>
                <line x1="60" y1="2" x2="60" y2="22" stroke="#7A5010" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M60 3 L78 9 L60 16 Z" fill="#D80E1A"/>
                <ellipse cx="68" cy="8" rx="4" ry="1.8" fill="white" opacity="0.25" transform="rotate(-10,68,8)"/>
                <ellipse cx="60" cy="28" rx="48" ry="22" fill="url(#{{ $zmid }}-bT)" filter="url(#{{ $zmid }}-sh)"/>
                <ellipse cx="44" cy="20" rx="16" ry="8" fill="white" opacity="0.25" transform="rotate(-10,44,20)"/>
                <ellipse cx="42" cy="16" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(-18,42,16)"/>
                <ellipse cx="60" cy="12" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9"/>
                <ellipse cx="78" cy="17" rx="4.5" ry="1.8" fill="#FFF0A0" opacity="0.9" transform="rotate(18,78,17)"/>
                <ellipse cx="32" cy="26" rx="4"   ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(-22,32,26)"/>
                <ellipse cx="88" cy="27" rx="4"   ry="1.6" fill="#FFF0A0" opacity="0.85" transform="rotate(22,88,27)"/>
                <ellipse cx="60" cy="48" rx="48" ry="6" fill="#B06010"/>
                <path d="M12 51 Q28 46 42 53 Q54 59 60 52 Q66 45 78 53 Q92 60 108 51 L108 59 Q92 66 78 60 Q66 54 60 60 Q54 66 42 60 Q28 53 12 59 Z" fill="url(#{{ $zmid }}-ch)"/>
                <path d="M16 55 C11 60 11 68 16 71 L19 71 C15 66 16 55 16 55 Z" fill="#FBBF24"/>
                <ellipse cx="16.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                <path d="M104 55 C109 60 109 68 104 71 L101 71 C105 66 104 55 104 55 Z" fill="#FBBF24"/>
                <ellipse cx="103.5" cy="71.5" rx="3" ry="2" fill="#F59000"/>
                <path d="M12 59 Q22 55 34 62 Q46 68 60 61 Q74 54 86 62 Q98 68 108 59 L108 65 Q98 73 86 66 Q74 60 60 66 Q46 72 34 66 Q22 60 12 65 Z" fill="url(#{{ $zmid }}-lt)"/>
                <ellipse cx="60" cy="70"  rx="48" ry="9"  fill="url(#{{ $zmid }}-mt)" filter="url(#{{ $zmid }}-sh)"/>
                <ellipse cx="60" cy="84"  rx="48" ry="14" fill="url(#{{ $zmid }}-bB)" filter="url(#{{ $zmid }}-sh)"/>
                <ellipse cx="60" cy="95"  rx="43" ry="8"  fill="#8B4000"/>
                <ellipse cx="60" cy="101" rx="36" ry="5"  fill="#6A3000"/>
                <ellipse cx="46" cy="80"  rx="16" ry="5"  fill="white" opacity="0.15"/>
                <ellipse cx="60" cy="66" rx="34" ry="28" fill="#6010B0" opacity="0.45" filter="url(#{{ $zmid }}-gP)"/>
                <ellipse cx="60" cy="66" rx="32" ry="26" fill="#40087A" stroke="#CC60F8" stroke-width="3"/>
                <ellipse cx="60" cy="66" rx="29" ry="23" fill="#2C0660" stroke="#7828B8" stroke-width="1.2"/>
                <ellipse cx="60" cy="66" rx="27" ry="21" fill="url(#{{ $zmid }}-sc)"/>
                <rect x="34" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#{{ $zmid }}-gC)"/>
                <rect x="35" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                <rect x="36" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                <ellipse cx="39" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                <rect x="35" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                <rect x="68" y="54" width="18" height="20" rx="7" fill="#22D3EE" opacity="0.25" filter="url(#{{ $zmid }}-gC)"/>
                <rect x="69" y="55" width="16" height="18" rx="6" fill="#030E1A"/>
                <rect x="70" y="56" width="14" height="16" rx="5" fill="#22D3EE"/>
                <ellipse cx="73" cy="58.5" rx="3.5" ry="2" fill="white" opacity="0.65"/>
                <rect x="69" y="55" width="16" height="18" rx="6" fill="none" stroke="#A5F3FC" stroke-width="0.8" opacity="0.8"/>
                <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="4" stroke-linecap="round" opacity="0.2"/>
                <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- Decorative dots --}}
        <div class="z-dots" aria-hidden="true">
            <div class="z-dot"></div>
            <div class="z-dot active"></div>
            <div class="z-dot"></div>
        </div>

        {{-- Tag --}}
        <span class="z-tag">@yield('tag', 'Error')</span>

        {{-- Code --}}
        <p class="z-code" aria-label="Código de error @yield('code')">@yield('code')</p>

        {{-- Title --}}
        <h1 class="z-title" id="z-error-heading">@yield('title')</h1>

        {{-- Message --}}
        <p class="z-msg">@yield('message')</p>

        {{-- Actions --}}
        <nav class="z-actions" aria-label="Opciones de recuperación">
            @yield('actions')
        </nav>

    </main>

    {{-- Brand footer --}}
    <a href="/" class="z-footer" aria-label="Zampa — volver al inicio">
        <svg class="z-footer-logo" viewBox="0 0 120 110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            @php $fuid = 'ft'.uniqid(); @endphp
            <defs>
                <radialGradient id="{{ $fuid }}-bT" cx="38%" cy="28%" r="62%">
                    <stop offset="0%" stop-color="#FBDF6A"/>
                    <stop offset="45%" stop-color="#E8980C"/>
                    <stop offset="100%" stop-color="#A05500"/>
                </radialGradient>
                <radialGradient id="{{ $fuid }}-bB" cx="38%" cy="22%" r="65%">
                    <stop offset="0%" stop-color="#F5C830"/>
                    <stop offset="55%" stop-color="#CC7008"/>
                    <stop offset="100%" stop-color="#8B4000"/>
                </radialGradient>
                <linearGradient id="{{ $fuid }}-ch" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FFD740"/>
                    <stop offset="100%" stop-color="#F59000"/>
                </linearGradient>
                <linearGradient id="{{ $fuid }}-mt" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#7B3010"/>
                    <stop offset="100%" stop-color="#4A1800"/>
                </linearGradient>
                <linearGradient id="{{ $fuid }}-lt" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#5CC830"/>
                    <stop offset="100%" stop-color="#348010"/>
                </linearGradient>
                <radialGradient id="{{ $fuid }}-sc" cx="50%" cy="38%" r="55%">
                    <stop offset="0%" stop-color="#0C0620"/>
                    <stop offset="100%" stop-color="#04000E"/>
                </radialGradient>
            </defs>
            <ellipse cx="60" cy="107" rx="40" ry="4" fill="#2A0800" opacity="0.25"/>
            <line x1="60" y1="2" x2="60" y2="22" stroke="#7A5010" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M60 3 L78 9 L60 16 Z" fill="#D80E1A"/>
            <ellipse cx="60" cy="28" rx="48" ry="22" fill="url(#{{ $fuid }}-bT)"/>
            <ellipse cx="60" cy="48" rx="48" ry="6" fill="#B06010"/>
            <path d="M12 51 Q28 46 42 53 Q54 59 60 52 Q66 45 78 53 Q92 60 108 51 L108 59 Q92 66 78 60 Q66 54 60 60 Q54 66 42 60 Q28 53 12 59 Z" fill="url(#{{ $fuid }}-ch)"/>
            <path d="M12 59 Q22 55 34 62 Q46 68 60 61 Q74 54 86 62 Q98 68 108 59 L108 65 Q98 73 86 66 Q74 60 60 66 Q46 72 34 66 Q22 60 12 65 Z" fill="url(#{{ $fuid }}-lt)"/>
            <ellipse cx="60" cy="70" rx="48" ry="9" fill="url(#{{ $fuid }}-mt)"/>
            <ellipse cx="60" cy="84" rx="48" ry="14" fill="url(#{{ $fuid }}-bB)"/>
            <ellipse cx="60" cy="66" rx="32" ry="26" fill="#40087A" stroke="#CC60F8" stroke-width="3"/>
            <ellipse cx="60" cy="66" rx="27" ry="21" fill="url(#{{ $fuid }}-sc)"/>
            <rect x="35" y="55" width="16" height="18" rx="6" fill="#22D3EE"/>
            <rect x="69" y="55" width="16" height="18" rx="6" fill="#22D3EE"/>
            <path d="M51 79 Q60 86 69 79" fill="none" stroke="#22D3EE" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Zampa</span>
    </a>

</body>
</html>
