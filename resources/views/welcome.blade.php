@extends('users.layout')

@section('title', 'مدينة الامام الحسين للزائرين')
@section('page-title', 'بوابة إدارة المنتسبين')

@section('content')
<div style="display:grid; gap:20px; padding: 8px 0;">

    <p style="margin:0; font-size:1.05rem; color:var(--soft);">
        مرحباً بك في لوحة إدارة مدينة الامام الحسين (عليه السلام) للزائرين.
        اختر أحد الأقسام أدناه للبدء.
    </p>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:14px;">

        <a href="{{ route('users.index') }}" style="
            display:grid; place-items:center; gap:10px; padding:24px 16px;
            background: rgba(255,255,255,0.72); border:1px solid var(--stroke);
            border-radius: var(--card-radius); box-shadow: var(--shadow);
            text-decoration:none; color:var(--ink);
            transition: transform 160ms, box-shadow 160ms;
            font-family: inherit; text-align:center;
        " onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 18px 36px rgba(16,24,40,0.28)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="
                width:72px; height:72px; border-radius:20px;
                background: linear-gradient(145deg, #1692ff, #0f56d4);
                display:grid; place-items:center;
            ">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                     stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <strong style="font-size:1rem;">إدارة المستخدمين</strong>
        </a>

    </div>
</div>
@endsection
