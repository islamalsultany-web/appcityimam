<?php

return [
    'modules' => [
        'auth_and_home' => [
            'display_name' => 'الدخول والواجهة الرئيسية',
            'permissions' => [
                'home.view' => 'عرض الصفحة الرئيسية',
                'auth.login.view' => 'عرض صفحة تسجيل الدخول',
                'auth.login.submit' => 'تنفيذ تسجيل الدخول',
                'auth.logout' => 'تنفيذ تسجيل الخروج',
            ],
        ],
        'users' => [
            'display_name' => 'إدارة المستخدمين',
            'permissions' => [
                'users.view' => 'عرض المستخدمين',
                'users.index' => 'عرض قائمة المستخدمين',
                'users.create' => 'عرض صفحة إنشاء مستخدم',
                'users.store' => 'حفظ مستخدم جديد',
                'users.show' => 'عرض بيانات مستخدم',
                'users.edit' => 'عرض صفحة تعديل مستخدم',
                'users.update' => 'حفظ تعديل مستخدم',
                'users.delete' => 'حذف مستخدم',
                'users.bulk_delete' => 'حذف جميع المستخدمين',
                'users.excel.page' => 'عرض صفحة استيراد وتصدير المستخدمين',
                'users.excel.template' => 'تنزيل نموذج اكسل للمستخدمين',
                'users.excel.import' => 'استيراد مستخدمين من اكسل',
                'users.excel.export' => 'تصدير مستخدمين الى اكسل',
            ],
        ],
        'asker_pages' => [
            'display_name' => 'صفحات المستفسر',
            'permissions' => [
                'inquiries.asker.view' => 'عرض لوحة المستفسر',
                'inquiries.asker.create_page' => 'عرض صفحة إرسال استفسار جديد',
                'inquiries.asker.create' => 'إرسال استفسار',
                'inquiries.asker.view_details' => 'عرض تفاصيل الاستفسار للمستفسر',
                'inquiries.asker.print' => 'طباعة استفسار للمستفسر',
            ],
        ],
        'responder_pages' => [
            'display_name' => 'صفحات المجيب',
            'permissions' => [
                'inquiries.responder.view' => 'عرض لوحة المجيب',
                'inquiries.responder.deleted' => 'عرض المحذوف مؤخرا',
                'inquiries.responder.restore' => 'استرجاع الاستفسارات المحذوفة',
                'inquiries.responder.report.print' => 'طباعة تقرير المجيب',
                'inquiries.responder.view_details' => 'عرض تفاصيل الاستفسار للمجيب',
                'inquiries.responder.print' => 'طباعة الاستفسار للمجيب',
                'inquiries.responder.show_answer_page' => 'عرض صفحة الإجابة على الاستفسار',
                'inquiries.responder.answer' => 'الإجابة على الاستفسارات',
                'inquiries.responder.manage' => 'إدارة الاستفسارات',
                'inquiries.responder.delete' => 'حذف استفسار',
            ],
        ],
        'reviewer_pages' => [
            'display_name' => 'صفحات المدقق',
            'permissions' => [
                'inquiries.reviewer.view' => 'عرض لوحة المدقق',
                'inquiries.reviewer.review_page' => 'عرض صفحة تدقيق الإجابة',
                'inquiries.reviewer.review' => 'اعتماد أو إعادة إجابات المجيبين',
                'inquiries.reviewer.manage' => 'إدارة تدقيق الإجابات',
            ],
        ],
        'permissions' => [
            'display_name' => 'صلاحيات المنتسبين',
            'permissions' => [
                'permissions.members.view' => 'عرض صلاحيات المنتسبين',
                'permissions.members.create' => 'عرض صفحة إضافة صلاحيات المنتسبين',
                'permissions.members.store' => 'حفظ صلاحيات المنتسبين',
                'permissions.members.edit' => 'تعديل صلاحيات المنتسبين',
                'permissions.members.update' => 'حفظ تعديل صلاحيات المنتسبين',
            ],
        ],
    ],
    'role_templates' => [
        'admin' => [
            'display_name' => 'مدير النظام',
            'permissions' => '*',
        ],
        'asker' => [
            'display_name' => 'مستفسر',
            'permissions' => [
                'inquiries.asker.view',
                'inquiries.asker.create_page',
                'inquiries.asker.create',
            ],
        ],
        'responder' => [
            'display_name' => 'مجيب',
            'permissions' => [
                'inquiries.responder.view',
                'inquiries.responder.answer',
                'inquiries.responder.manage',
            ],
        ],
        'reviewer' => [
            'display_name' => 'مدقق',
            'permissions' => [
                'inquiries.reviewer.view',
                'inquiries.reviewer.review_page',
                'inquiries.reviewer.review',
                'inquiries.reviewer.manage',
            ],
        ],
    ],
];
