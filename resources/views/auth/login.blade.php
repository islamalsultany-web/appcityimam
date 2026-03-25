<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

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
            background:
                linear-gradient(180deg, rgba(0,0,0,0.56), rgba(0,0,0,0.58)),
                url('https://images.unsplash.com/photo-1603451731239-0ee2f0865dc2?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
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

            <p class="hint">ادخل اسم المستخدم وكلمة السر للوصول إلى النظام.</p>

            <div class="field">
                <label for="username">اسم المستخدم</label>
                <input id="username" name="username" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">كلمة السر</label>
                <input id="password" name="password" type="password" required>
            </div>

            <button type="submit">دخول</button>
        </form>
    </div>
</body>
</html>
