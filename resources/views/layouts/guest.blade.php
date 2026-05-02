<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') — StreamPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #0d0f14;
            --surface: #161a23;
            --border:  #252b38;
            --accent:  #e8a020;
            --text:    #e2e8f0;
            --muted:   #64748b;
            --danger:  #ef4444;
            --success: #22c55e;
            --radius:  12px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;

            /* Subtle grid pattern */
            background-image:
                linear-gradient(rgba(232,160,32,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232,160,32,.03) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* Brand */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .brand-logo {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }
        .brand-logo span { color: var(--accent); }
        .brand-tagline {
            font-size: .82rem;
            color: var(--muted);
            margin-top: 6px;
        }

        /* Card */
        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px 32px;
            box-shadow: 0 24px 64px rgba(0,0,0,.4);
        }
        .auth-card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .auth-card-subtitle {
            font-size: .85rem;
            color: var(--muted);
            margin-bottom: 28px;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            display: flex;
        }
        .form-control {
            width: 100%;
            padding: 10px 13px 10px 38px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(232,160,32,.12);
        }
        .form-control.is-error { border-color: var(--danger); }
        .form-control.no-icon { padding-left: 13px; }

        /* Remember / extras */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .85rem;
            color: var(--muted);
            cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            accent-color: var(--accent);
            width: 15px;
            height: 15px;
        }

        /* Submit */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .01em;
            transition: background .15s, transform .1s;
        }
        .btn-submit:hover { background: #f5ad30; }
        .btn-submit:active { transform: scale(.99); }

        /* Error message */
        .field-error {
            font-size: .78rem;
            color: var(--danger);
            margin-top: 5px;
        }

        /* Alert */
        .alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: .85rem;
            margin-bottom: 20px;
        }
        .alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: var(--danger); }
        .alert-success { background: rgba(34,197,94,.1);  border: 1px solid rgba(34,197,94,.3);  color: var(--success); }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: .78rem;
            color: var(--muted);
        }

        /* Decorative accent bar on card top */
        .auth-card::before {
            content: '';
            display: block;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), #f5ad30, transparent);
            margin: -36px -32px 32px;
            border-radius: var(--radius) var(--radius) 0 0;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="brand">
        <div class="brand-logo">Stream<span>Panel</span></div>
        <div class="brand-tagline">Admin Dashboard</div>
    </div>

    @yield('content')

    <div class="auth-footer">
        &copy; {{ date('Y') }} StreamPanel. All rights reserved.
    </div>
</div>
</body>
</html>