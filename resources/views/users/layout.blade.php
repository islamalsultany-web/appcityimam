<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'إدارة المستخدمين')</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        :root {
            --surface: rgba(245, 245, 245, 0.94);
            --surface-strong: rgba(238, 238, 238, 0.96);
            --ink: #0f172a;
            --soft: #5f6674;
            --accent: #f3c542;
            --accent-2: #e24a3b;
            --stroke: rgba(15, 23, 42, 0.13);
            --card-radius: 18px;
            --shadow: 0 14px 30px rgba(16, 24, 40, 0.22);
            --primary: #1692ff;
            --primary-dark: #0f56d4;
            --danger: #b91c1c;
            --success: #15803d;
        }

        body.dark {
            --surface: rgba(28, 32, 43, 0.92);
            --surface-strong: rgba(34, 40, 54, 0.94);
            --ink: #e8edf7;
            --soft: #b8c1d5;
            --stroke: rgba(255, 255, 255, 0.13);
            --shadow: 0 16px 28px rgba(0, 0, 0, 0.38);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Cairo', sans-serif;
            color: var(--ink);
            background:
                linear-gradient(180deg, rgba(0,0,0,0.56), rgba(0,0,0,0.58)),
                url('https://images.unsplash.com/photo-1603451731239-0ee2f0865dc2?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
        }

        /* ── Topbar ── */
        .topbar {
            min-height: 72px;
            background: rgba(246, 246, 246, 0.97);
            border-bottom: 1px solid rgba(0,0,0,0.09);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 22px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            flex-shrink: 0;
        }

        .logo-wrap {
            width: clamp(72px, 7vw, 112px);
            height: clamp(36px, 3.5vw, 52px);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .logo-image {
            width: 100%; height: 100%;
            object-fit: contain; display: block;
        }

        .title-wrap {
            height: clamp(28px, 3.5vw, 44px);
            width: clamp(150px, 24vw, 320px);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .title-image {
            width: 100%; height: 100%;
            object-fit: contain; display: block;
        }

        .topbar-nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        /* ── Shell ── */
        .shell {
            max-width: 1220px;
            margin: 0 auto;
            padding: 20px 16px;
            display: grid;
            gap: 16px;
        }

        .shell-grid {
            grid-template-columns: 240px minmax(0, 1fr);
            align-items: start;
        }

        /* ── Sidebar ── */
        .sidebar {
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            padding: 12px;
            position: sticky;
            top: 12px;
        }

        .sidebar-title {
            margin: 0 0 10px;
            font-size: 0.98rem;
            color: var(--ink);
        }

        .sidebar-section-title {
            margin: 10px 0 6px;
            font-size: 0.78rem;
            color: var(--soft);
            font-weight: 700;
        }

        .sidebar-menu {
            display: grid;
            gap: 8px;
        }

        .sidebar-link {
            border: 1px solid var(--stroke);
            border-radius: 10px;
            padding: 8px 10px;
            text-decoration: none;
            color: var(--ink);
            background: rgba(255, 255, 255, 0.75);
            font-size: 0.9rem;
            font-weight: 600;
            transition: background 150ms, transform 120ms;
        }

        .sidebar-link:hover {
            background: #fff;
            transform: translateY(-1px);
        }

        .sidebar-link.active {
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
        }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--stroke);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            background: rgba(243, 197, 66, 0.08);
        }

        .card-head h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--ink);
        }

        .card-body {
            padding: 16px;
        }

        /* ── Actions group ── */
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ── Buttons ── */
        .btn,
        .topbar-nav a,
        .topbar-nav button {
            border: 1px solid var(--stroke);
            background: rgba(255,255,255,0.88);
            color: var(--ink);
            border-radius: 10px;
            padding: 8px 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 600;
            transition: background 160ms, transform 140ms, box-shadow 160ms;
        }

        .btn:hover,
        .topbar-nav a:hover,
        .topbar-nav button:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(16,24,40,0.12);
            transform: translateY(-1px);
        }

        .btn.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: transparent;
            color: #fff;
        }

        .btn.primary:hover {
            background: linear-gradient(135deg, #1f9fff, #1462e0);
            transform: translateY(-1px);
        }

        .btn.warn {
            border-color: rgba(226, 74, 59, 0.3);
            color: var(--accent-2);
        }

        .btn.warn:hover {
            background: rgba(226, 74, 59, 0.08);
        }

        /* ── Alerts ── */
        .alert {
            border: 1px solid;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 12px;
            font-size: 0.92rem;
        }

        .alert.success {
            border-color: rgba(21, 128, 61, 0.35);
            background: rgba(236, 253, 245, 0.88);
            color: var(--success);
        }

        .alert.error {
            border-color: rgba(185, 28, 28, 0.35);
            background: rgba(254, 242, 242, 0.88);
            color: var(--danger);
        }

        /* ── Table ── */
        .table-wrap {
            width: 100%;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }

        th,
        td {
            border-bottom: 1px solid var(--stroke);
            padding: 10px 10px;
            text-align: right;
            white-space: nowrap;
        }

        th {
            background: rgba(243, 197, 66, 0.12);
            font-size: 0.9rem;
            font-weight: 700;
            color: #4a3800;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.45);
        }

        .muted {
            color: var(--soft);
            font-size: 0.9rem;
        }

        /* ── Role chip ── */
        .role-chip {
            border-radius: 999px;
            border: 1px solid rgba(243, 197, 66, 0.5);
            background: rgba(243, 197, 66, 0.16);
            color: #7a540a;
            font-size: 0.76rem;
            padding: 2px 10px;
            font-weight: 600;
        }

        /* ── Form grid ── */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            font-size: 0.88rem;
            color: #334155;
            font-weight: 700;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid var(--stroke);
            border-radius: 10px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.88);
            transition: border-color 160ms, box-shadow 160ms;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(22, 146, 255, 0.15);
            background: #fff;
        }

        .field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        /* ── List grid (show page) ── */
        .list-grid {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 12px;
        }

        .list-grid li {
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.65);
        }

        /* ── Pagination ── */
        .pager {
            margin-top: 14px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ── Responsive ── */
        @media (max-width: 820px) {
            .shell {
                padding: 12px 10px;
            }

            .shell-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }

            .form-grid,
            .list-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                flex-wrap: wrap;
                gap: 8px;
                padding: 10px 14px;
            }

            .topbar-nav {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    @php
        $currentRole = session('auth_app_role');
        $hideSidebar = $currentRole === 'asker';

        $authUserId = (int) session('auth_app_user_id');
        $authUser = $authUserId ? \App\Models\AppUser::query()->find($authUserId) : null;

        $hasSidebarAccess = function (array $permissions = [], array $roles = []) use ($authUser): bool {
            if (! $authUser) {
                return false;
            }

            if ($authUser->hasRole('admin')) {
                return true;
            }

            if (! empty($roles) && $authUser->hasAnyRole($roles)) {
                return true;
            }

            foreach ($permissions as $permission) {
                if ($authUser->can($permission)) {
                    return true;
                }
            }

            return false;
        };

        $showUsersLink = $hasSidebarAccess(['users.view', 'users.index'], ['admin']);
        $showTrackMemberLink = $hasSidebarAccess(['inquiries.asker.view'], ['asker']);
        $showPermissionsLink = $hasSidebarAccess(['permissions.members.view', 'permissions.members.edit'], ['admin']);

        $showAskerDashboardLink = $hasSidebarAccess(['inquiries.asker.view'], ['asker']);
        $showAskerCreateLink = $hasSidebarAccess(['inquiries.asker.create_page', 'inquiries.asker.create'], ['asker']);

        $showResponderDashboardLink = $hasSidebarAccess(['inquiries.responder.view'], ['responder']);
        $showResponderDeletedLink = $hasSidebarAccess(['inquiries.responder.deleted', 'inquiries.responder.manage'], ['responder']);
        $showResponderReportLink = $hasSidebarAccess(['inquiries.responder.report.print', 'inquiries.responder.manage'], ['responder']);
    @endphp

    <header class="topbar">
        <a class="brand" href="/">
            <div class="logo-wrap">
                <img class="logo-image" src="/brand-logo-clean.png" alt="شعار" onerror="this.style.display='none'">
            </div>
            <div class="title-wrap">
                <img class="title-image" src="/brand-title-clean.png" alt="مدينة الامام الحسين" onerror="this.style.display='none'">
            </div>
        </a>
        <div class="topbar-nav">
            @if (! request()->routeIs('dashboard.asker'))
                <a class="btn" href="/">الرئيسية</a>
            @endif
            @yield('topbar-actions')
        </div>
    </header>

    <div class="shell {{ $hideSidebar ? '' : 'shell-grid' }}">
        @if (! $hideSidebar)
            <aside class="sidebar">
                <h2 class="sidebar-title">القائمة الرئيسية</h2>
                <nav class="sidebar-menu" aria-label="روابط النظام">
                    <a class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}" href="/">الرئيسية</a>
                    <a class="sidebar-link {{ request()->routeIs('login.form') ? 'active' : '' }}" href="{{ route('login.form') }}">تسجيل الدخول</a>
                    @if ($showUsersLink)
                        <a class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">المستخدمين</a>
                    @endif
                    @if ($showTrackMemberLink)
                        <a class="sidebar-link {{ request()->routeIs('dashboard.asker') ? 'active' : '' }}" href="{{ route('dashboard.asker') }}">متابعة المنتسب</a>
                    @endif
                    @if ($showPermissionsLink)
                        <a class="sidebar-link {{ request()->routeIs('permissions.members.*') ? 'active' : '' }}" href="{{ route('permissions.members.index') }}">الصلاحيات</a>
                    @endif

                    @if ($showAskerDashboardLink || $showAskerCreateLink)
                        <div class="sidebar-section-title">صفحات المستفسر</div>
                    @endif
                    @if ($showAskerDashboardLink)
                        <a class="sidebar-link {{ request()->routeIs('dashboard.asker') ? 'active' : '' }}" href="{{ route('dashboard.asker') }}">لوحة المستفسر</a>
                    @endif
                    @if ($showAskerCreateLink)
                        <a class="sidebar-link {{ request()->routeIs('asker.inquiries.create') ? 'active' : '' }}" href="{{ route('asker.inquiries.create') }}">إرسال استفسار جديد</a>
                    @endif

                    @if ($showResponderDashboardLink || $showResponderDeletedLink || $showResponderReportLink)
                        <div class="sidebar-section-title">صفحات المجيب</div>
                    @endif
                    @if ($showResponderDashboardLink)
                        <a class="sidebar-link {{ request()->routeIs('dashboard.responder') ? 'active' : '' }}" href="{{ route('dashboard.responder') }}">لوحة المجيب</a>
                    @endif
                    @if ($showResponderDeletedLink)
                        <a class="sidebar-link {{ request()->routeIs('responder.inquiries.deleted') ? 'active' : '' }}" href="{{ route('responder.inquiries.deleted') }}">المحذوف مؤخرا</a>
                    @endif
                    @if ($showResponderReportLink)
                        <a class="sidebar-link {{ request()->routeIs('responder.inquiries.report.print') ? 'active' : '' }}" href="{{ route('responder.inquiries.report.print') }}" target="_blank">طباعة تقرير</a>
                    @endif

                    @if ($currentRole)
                        <div class="sidebar-section-title">الحساب</div>
                        <a class="sidebar-link" href="{{ route('logout.home') }}">تسجيل خروج</a>
                    @endif
                </nav>
            </aside>
        @endif

        <div class="card">
            <div class="card-head">
                <h1>@yield('page-title', 'إدارة المستخدمين')</h1>
                <div class="actions">
                    @yield('header-actions')
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert error">
                        <ul style="margin:0; padding-inline-start:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>