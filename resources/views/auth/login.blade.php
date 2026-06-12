<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="{{ asset('css/cairo-font.css') }}">
    <style nonce="{{ $cspNonce }}">

        :root {
            --surface: rgba(245, 245, 245, 0.94);
            --ink: #0f172a;
            --soft: #5f6674;
            --stroke: rgba(15, 23, 42, 0.13);
            --shadow: 0 14px 30px rgba(16, 24, 40, 0.22);
            --primary: #1692ff;
            --primary-dark: #0f56d4;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Cairo', sans-serif;
            color: var(--ink);
            background-color: #0f172a;
            background-image:
                radial-gradient(ellipse 85% 65% at 50% 0%, rgba(243, 197, 66, 0.14), transparent 58%),
                radial-gradient(ellipse 70% 50% at 100% 100%, rgba(22, 146, 255, 0.12), transparent 55%),
                radial-gradient(ellipse 60% 45% at 0% 100%, rgba(226, 74, 59, 0.08), transparent 50%),
                linear-gradient(165deg, #0b1220 0%, #1e293b 42%, #111827 100%);
            background-attachment: fixed;
            display: grid;
            place-items: center;
            padding: 16px;
        }

        .card {
            width: min(460px, 100%);
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--stroke);
            background: rgba(243, 197, 66, 0.1);
        }

        .head h1 {
            margin: 0;
            font-size: 1.2rem;
        }

        form {
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #334155;
        }

        input {
            width: 100%;
            border: 1px solid var(--stroke);
            border-radius: 10px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.88);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(22, 146, 255, 0.15);
            background: #fff;
        }

        .error {
            border: 1px solid rgba(185, 28, 28, 0.35);
            background: rgba(254, 242, 242, 0.88);
            color: var(--danger);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.9rem;
        }

        button {
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .hint {
            margin: 0;
            color: var(--soft);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-inline-end: 4.75rem;
        }

        .password-toggle {
            position: absolute;
            inset-inline-end: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary-dark);
            background: rgba(22, 146, 255, 0.1);
        }

        .password-toggle:hover {
            background: rgba(22, 146, 255, 0.18);
        }

        .password-toggle:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <h1>تسجيل الدخول</h1>
        </div>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <p class="hint">يمكنك الدخول بالرقم الوظيفي أو اسم المستخدم. أدخل كلمة المرور الخاصة بك — إذا نسيتها، تواصل مع مسؤول النظام.</p>

            <div class="field">
                <label for="username">اسم المستخدم أو الرقم الوظيفي</label>
                <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">كلمة المرور</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="إظهار كلمة المرور" aria-pressed="false">إظهار</button>
                </div>
            </div>

            <button type="submit">دخول</button>
        </form>
    </div>

    <script nonce="{{ $cspNonce }}">
        (function () {
            var input = document.getElementById('password');
            var toggle = document.getElementById('togglePassword');

            if (!input || !toggle) {
                return;
            }

            toggle.addEventListener('click', function () {
                var visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                toggle.textContent = visible ? 'إظهار' : 'إخفاء';
                toggle.setAttribute('aria-label', visible ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور');
                toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
            });
        })();
    </script>
</body>
</html>
