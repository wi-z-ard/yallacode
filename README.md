# نظام إدارة المصروفات - Expenses Management System

نظام شامل لإدارة المصروفات والميزانيات مع واجهة مستخدم عربية حديثة ومتجاوبة.

## المميزات الرئيسية

### 🎯 الميزات الأساسية
- **إدارة المعاملات**: إضافة، تعديل، حذف المعاملات المالية
- **إدارة الفئات**: فئات افتراضية وإمكانية إضافة فئات مخصصة
- **إدارة الميزانيات**: تحديد ميزانيات شهرية/سنوية لكل فئة
- **التقارير والتحليلات**: رسوم بيانية تفاعلية وتقارير مفصلة
- **نظام المستخدمين**: دعم متعدد المستخدمين مع أدوار مختلفة

### 🔐 نظام الأمان
- **تشفير كلمات المرور**: استخدام bcrypt للتشفير
- **جلسات آمنة**: إدارة جلسات المستخدمين بشكل آمن
- **حماية من CSRF**: حماية من هجمات Cross-Site Request Forgery
- **تنظيف البيانات**: تنظيف جميع المدخلات لمنع XSS

### 🎨 واجهة المستخدم
- **تصميم متجاوب**: يعمل على جميع الأجهزة
- **دعم اللغة العربية**: واجهة كاملة باللغة العربية مع RTL
- **نوافذ منبثقة**: جميع النماذج تستخدم Modal boxes
- **تحديث مباشر**: CRUD operations مع تحديث مباشر للجداول

### 📊 التحليلات والتقارير
- **الإنفاق الشهري**: تتبع الإنفاق شهرياً
- **تحليل الفئات**: توزيع الإنفاق حسب الفئات
- **مقارنة الميزانية**: مقارنة الإنفاق الفعلي بالميزانية المخططة
- **التدفق النقدي**: تتبع التدفق النقدي عبر الوقت

## متطلبات النظام

- **PHP**: 8.0 أو أحدث
- **MySQL**: 5.7 أو أحدث
- **Apache/Nginx**: خادم ويب
- **المتصفح**: متصفح حديث يدعم ES6

## التثبيت

### 1. تحضير البيئة
```bash
# تأكد من تشغيل XAMPP أو WAMP
# تأكد من تشغيل Apache و MySQL
```

### 2. نسخ الملفات
```bash
# انسخ جميع الملفات إلى مجلد htdocs/pro-exp
```

### 3. إعداد قاعدة البيانات
```bash
# افتح المتصفح واذهب إلى:
http://localhost/pro-exp/install.php

# أو قم بتشغيل schema.sql يدوياً في phpMyAdmin
```

### 4. تكوين قاعدة البيانات
قم بتعديل ملف `config/config.php` إذا لزم الأمر:
```php
private $host = 'localhost';
private $db_name = 'exp';
private $username = 'root';
private $password = '';
```

## بيانات الدخول الافتراضية

### المدير العام
- **البريد الإلكتروني**: admin@expenses.com
- **كلمة المرور**: admin123

## هيكل المشروع

```
pro-exp/
├── api/                    # API endpoints
│   ├── auth.php           # المصادقة
│   ├── transactions.php   # المعاملات
│   ├── categories.php     # الفئات
│   ├── budgets.php        # الميزانيات
│   ├── users.php          # المستخدمين
│   └── dashboard.php      # لوحة التحكم
├── config/
│   └── database.php       # إعدادات قاعدة البيانات
├── database/
│   └── schema.sql         # هيكل قاعدة البيانات
├── includes/
│   └── auth.php           # وظائف المصادقة
├── js/
│   └── app.js             # JavaScript الرئيسي
├── index.php              # الصفحة الرئيسية
├── login.php              # صفحة تسجيل الدخول
├── install.php            # سكريبت التثبيت
└── README.md              # هذا الملف
```

## الاستخدام

### 1. تسجيل الدخول
- اذهب إلى `http://localhost/pro-exp/`
- استخدم بيانات المدير العام للدخول

### 2. إضافة معاملة
- اضغط على "إضافة مصروف" في لوحة التحكم
- املأ البيانات المطلوبة
- اضغط "إضافة"

### 3. إدارة الميزانيات
- اذهب إلى قسم "الميزانيات"
- اضغط "إضافة ميزانية"
- حدد الفئة والمبلغ والفترة

### 4. عرض التقارير
- اذهب إلى قسم "التقارير"
- اختر الفترة الزمنية
- شاهد الرسوم البيانية والتحليلات

## API Documentation

### المصادقة
```
POST /api/auth.php?action=logout
GET  /api/auth.php?action=profile
POST /api/auth.php?action=update_profile
POST /api/auth.php?action=change_password
```

### المعاملات
```
GET    /api/transactions.php?action=recent&limit=10
GET    /api/transactions.php?action=all&page=1&limit=20
GET    /api/transactions.php?action=stats&period=month
POST   /api/transactions.php
PUT    /api/transactions.php
DELETE /api/transactions.php
```

### الفئات
```
GET    /api/categories.php
POST   /api/categories.php
PUT    /api/categories.php
DELETE /api/categories.php
```

### الميزانيات
```
GET    /api/budgets.php?action=current
GET    /api/budgets.php?action=all
GET    /api/budgets.php?action=summary
POST   /api/budgets.php
PUT    /api/budgets.php
DELETE /api/budgets.php
```

### لوحة التحكم
```
GET /api/dashboard.php?action=overview
GET /api/dashboard.php?action=monthly_spending
GET /api/dashboard.php?action=category_breakdown
GET /api/dashboard.php?action=cash_flow
```

### المستخدمين (المدير العام فقط)
```
GET    /api/users.php?action=all
GET    /api/users.php?action=stats
POST   /api/users.php
PUT    /api/users.php
DELETE /api/users.php
```

## الأدوار والصلاحيات

### المستخدم العادي (user)
- إدارة معاملاته الخاصة
- إنشاء فئات مخصصة
- إدارة ميزانياته
- عرض تقاريره

### المدير (admin)
- جميع صلاحيات المستخدم العادي
- عرض إحصائيات عامة
- إدارة الفئات العامة

### المدير العام (super_admin)
- جميع الصلاحيات
- إدارة المستخدمين
- الوصول لجميع البيانات

## الأمان والحماية

### حماية قاعدة البيانات
- استخدام Prepared Statements
- تشفير كلمات المرور
- تنظيف جميع المدخلات

### حماية الجلسات
- إدارة آمنة للجلسات
- انتهاء صلاحية الجلسات
- حماية من Session Hijacking

### حماية API
- التحقق من صحة المدخلات
- حماية من SQL Injection
- حماية من XSS

## التخصيص

### إضافة فئات جديدة
```sql
INSERT INTO categories (name, name_ar, icon, color, user_id) 
VALUES ('New Category', 'فئة جديدة', 'fas fa-icon', '#color', NULL);
```

### تخصيص الألوان
قم بتعديل ملف `js/app.js` لتغيير ألوان الرسوم البيانية.

### إضافة لغات جديدة
قم بإنشاء ملفات ترجمة في مجلد `lang/`.

## استكشاف الأخطاء

### مشاكل قاعدة البيانات
- تأكد من تشغيل MySQL
- تحقق من بيانات الاتصال في `config/database.php`
- تأكد من وجود قاعدة البيانات

### مشاكل الصلاحيات
- تأكد من صلاحيات الكتابة على المجلدات
- تحقق من إعدادات Apache/Nginx

### مشاكل JavaScript
- افتح Developer Tools في المتصفح
- تحقق من وجود أخطاء في Console

## المساهمة

نرحب بالمساهمات! يرجى:
1. عمل Fork للمشروع
2. إنشاء branch جديد للميزة
3. عمل commit للتغييرات
4. إرسال Pull Request

## الترخيص

هذا المشروع مرخص تحت رخصة MIT - انظر ملف LICENSE للتفاصيل.

## الدعم

للحصول على الدعم:
- افتح Issue في GitHub
- راسلنا على البريد الإلكتروني
- راجع الوثائق

## التحديثات المستقبلية

- [ ] تطبيق الهاتف المحمول
- [ ] تصدير البيانات (Excel, PDF)
- [ ] إشعارات الميزانية
- [ ] تحليلات متقدمة بالذكاء الاصطناعي
- [ ] دعم العملات المتعددة
- [ ] نظام النسخ الاحتياطي التلقائي

---

تم تطوير هذا النظام باستخدام:
- **Frontend**: HTML5, CSS3 (Tailwind), JavaScript (ES6), Chart.js
- **Backend**: PHP 8.0+, MySQL 5.7+
- **Security**: bcrypt, PDO, CSRF Protection
- **UI/UX**: Responsive Design, Arabic RTL Support

## 📄 الترخيص والاستخدام

### ⚠️ لأغراض تعليمية فقط
هذا النظام مخصص **للأغراض التعليمية فقط**. 

### 🚫 القيود
- **يُمنع البيع**: لا يجوز بيع أو تسويق أي جزء من الكود
- **يُمنع الاستخدام التجاري**: للتعلم والتدريس فقط
- **يُمنع إعادة التوزيع للربح**: لا يجوز توزيع النظام لأغراض تجارية

### ✅ الاستخدامات المسموحة
- التعلم والتدريس
- المشاريع الأكاديمية
- البحث العلمي
- المشاريع الشخصية التعليمية

### 👨‍💻 المطور
**محمد عوض**
- Facebook: [mohammed.3awad](https://facebook.com/mohammed.3awad)

للاستفسارات أو طلب إذن للاستخدام خارج النطاق التعليمي، يرجى التواصل عبر فيسبوك.

---

**© 2025 Mohammed Awad - Educational Use License**
