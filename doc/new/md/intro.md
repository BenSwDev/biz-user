# מסמך הסבר מורחב על הקבצים והתרחישים הראשיים לאחר התחברות וניווט בתפריטים

במסמך זה תוצג סקירה מלאה ומעמיקה על כל אחד מהקבצים המרכזיים במערכת, כולל אופן השילוב ביניהם, פונקציונליות עיקרית ותרחישי שימוש. הוא נועד למתכנתים מנוסים שרוצים להכיר לעומק את מבנה המערכת, לצורך תחזוקה והרחבה עתידית.  
המסמך כולל:

1. [הצגה כללית של המערכת](#system-overview)
2. [פירוט הקבצים העיקריים](#main-files)
    - [index.php](#indexphp)
    - [auth.php](#authphp)
    - [functions.php](#functionsphp)
    - [partial/menu.php](#partialmenuphp)
    - [partial/submenu.php](#partialsubmenuphp)
    - [partial/menu_member.php](#partialmenumemberphp)
3. [תהליך זרימת הנתונים וההרשאות](#auth-flow)
4. [תרחישי עבודה עיקריים](#main-scenarios)

---

<a name="system-overview"></a>
## 1. הצגה כללית של המערכת
המערכת מיועדת לניהול הזמנות, טיפולים, ספא, כרטיסי מתנה, מנויים, תוספות (Extras), ודוחות פיננסיים ותפעוליים. היא בנויה באופן מודולרי, כך שניתן להוסיף או להחליף רכיבים בקלות.  
בגדול, המערכת כוללת:
- **כניסת משתמש** עם הרשאות מסוגים שונים (אדמין, ספא, ״Member״ למטפלים, וכו’).
- **יומן תפוסה** להצגת טיפול/שעות עבודה/משמרות.
- **דוחות** עבור אירועים, מכירות, סטטיסטיקות שונות.
- **ניהול כרטיסי מתנה (Giftcards)**, תוספות, ומגוון הגדרות עבור אתר ספא או משלים.

---

<a name="main-files"></a>
## 2. פירוט הקבצים העיקריים

המערכת נשענת על מספר קבצים מרכזיים המוגדרים כ״ליבה״, בתוספת קבצים משלימים (partials). להלן עיקרם:

<a name="indexphp"></a>
### 2.1 index.php
- **תפקיד:** נקודת הכניסה הראשית (Entry Point) ליישום.
- **עיקרי הפעולות:**
    1. בודק/קובע את השפה (Locale) בהתאם לפרמטר `lang` ב-URL, שומר/מעדכן אותה ב-Session.
    2. מאתחל סשן ואת סביבת העבודה.
    3. טוען את הקובץ `auth.php` כדי לוודא שהמשתמש מחובר (או מפנה למסך כניסה).
    4. לפי פרמטר `?page=...`, טוען את קובץ העמוד המתאים מתוך `pages/` (למשל `home.php`).
    5. מגדיר Template בסיסי (HTML/CSS/JS כלליים) ומרנדר את התפריטים המתאימים (menu, submenu).
    6. מכין משתנים גלובליים (כגון $_CURRENT_USER, $_CURRENT_BASE, ועוד) לשימוש במערכת.

#### נקודות לשיפור / הרחבה אפשריות ב-index.php
1. **ניהול שפה מתקדם** – למשל, תמיכה בכתיבות-כיוון שונים מעבר ל-RTL/LTR, והעברת הודעות שגיאה/אזהרה מקובצי שפה (i18n מלא).
2. **ראוטר חכם** – במקום `?page=...`, אפשר לממש Router ידידותי ל-URL נקי (Rewrite).
3. **אבטחה** – מומלץ לוודא שכל קריאה ב-GET, כגון `?page=`, נבדקת והקובץ אכן קיים ולא מוביל לפריצות Path Traversal.

---

<a name="authphp"></a>
### 2.2 auth.php
- **תפקיד:** טיפול בתהליך ההזדהות והרשאות במערכת.
- **עיקרי הפעולות:**
    1. בודק Session פעילה (אם המשתמש כבר התחבר). אם אין, מפנה ל-`login.php`.
    2. מזהה את סוג המשתמש (ספא, אדמין, מטפל וכו’) ויוצר את אובייקט `TfusaUser` (או `MemberUser`).
    3. מגדיר `$_CURRENT_USER` עם נתוני ההרשאות והאתרים (sites) בהם מותר לו לפעול.
    4. מאפשר מעבר בין אתרים (sites) במקרה שמשתמש אחד מנהל מספר אתרים.
    5. קובע הגבלות (למשל `calendar_only`, `showstats`) ומשתמש בהן להצגת התפריטים/הדוחות.

#### נקודות לשיפור / הרחבה אפשריות ב-auth.php
1. **ספריית OAuth או JWT** – ניתן לשקול פרוטוקולי הזדהות מודרניים במקום Session רגיל.
2. **Multi-factor Authentication (2FA)** – להוסיף אימות כפול (בטלפון / באימייל) למשתמשים רגישים.
3. **ניהול הרשאות דינמי** – שמירת ההרשאות בטבלאות DB והטמעה של Role-based Access Control (RBAC).

---

<a name="functionsphp"></a>
### 2.3 functions.php
- **תפקיד:** פונקציות עזר (Utility) גלובליות לשימוש ברחבי המערכת.
- **עיקרי הפעולות:**
    1. עיבוד תאריכים (למשל `returnMarkedDates(...)`).
    2. בניית לינקים, עזרי WhatsApp, טיפול ב-DB שכיח (שליפות/עדכונים).
    3. פונקציות שעושות שימוש באובייקט המשתמש (למשל, בדיקת הרשאות או הפקת ערכים חוזרים).

#### נקודות לשיפור / הרחבה אפשריות ב-functions.php
1. **הפרדת שכבות** – לעיתים מוטב לפצל פונקציות DB לקובץ/מחלקה נפרד(ת).
2. **Prefix לשמות פונקציות** – כדי למנוע התנגשויות שמות, כדאי לשקול מרחבי שמות (Namespace) או Class Helper.
3. **Unit Tests** – כתיבת בדיקות אוטומטיות לפונקציות קריטיות.

---

<a name="partialmenuphp"></a>
### 2.4 partial/menu.php
- **תפקיד:** התפריט הראשי (Side Menu) של המערכת, שבו ניתנים קישורים לאזורים השונים (עמוד בית, דוחות, יצירת הזמנה, הגדרות…).
- **עיקרי הפעולות:**
    1. מציג אוסף של `<li>` עם אייקון וכיתוב לכל פריט תפריט.
    2. בודק הגבלות משתמש (למשל `calendar_only`, `showstats`, `is_spa`) כדי להחליט אילו פריטים להציג.
    3. מאגד פריטי תפריט מותנים (למשל סליקה תופיע רק אם יש `hasTerminal`).

#### נקודות לשיפור / הרחבה אפשריות ב-menu.php
1. **מנגנון הרשאות גמיש** – להגדיר טבלת הרשאות בבסיס הנתונים, במקום תנאים ״קשיחים״ בקוד.
2. **עיצוב מתקדם** – ניתן להשתמש בספריית UI (Bootstrap/Vue/React) לתפריט דינמי יותר.
3. **קוד מקוצר** – במקום מערך גדול של פריטים, לשמור אותם ב-DB, לטעון ולרנדר באמצעות לולאה.

---

<a name="partialsubmenuphp"></a>
### 2.5 partial/submenu.php
- **תפקיד:** הצגת תפריט-משנה (Submenu) רלוונטי לפי ההקשר הנוכחי (למשל, אם אנחנו בעמוד ״הגדרות״, יופיעו תתי-קישורים לשינוי הגדרות נוספות).
- **עיקרי הפעולות:**
    1. מגדיר מערך `$subMenu[...]` שבו כל מקבץ מייצג קטגוריה (למשל [1] הגדרות כלליות, [2] ניהול הזמנות).
    2. מזהה לפי `$_GET['page']` באיזה קבוצת תפריט אנחנו נמצאים, ומציג את הקישורים המתאימים.
    3. בודק אם המשתמש הנוכחי הוא ספציפי (is_spa, userType) כדי להחליט מה להציג.

#### נקודות לשיפור / הרחבה אפשריות ב-submenu.php
1. **דינמיות מלאה** – במקום הגדרה ידנית, ניתן לנהל הסאב-מניו בטבלת DB עם מזהי קטגוריות ותת-דפים.
2. **עיצוב והתאמה אישית** – יצירת מנגנון עריכת submenu ממשקית (למשל, עבור כל אתר).

---

<a name="partialmenumemberphp"></a>
### 2.6 partial/menu_member.php
- **תפקיד:** תפריט ייעודי למשתמשי “Member” (למשל, מטפלים).
- **עיקרי הפעולות:**
    1. מציג רשימת כפתורים מצומצמת (לרוב: יומן, רשימת הזמנות למטפל, הצהרות בריאות).
    2. מאפשר עדיין לקשר לעמוד הבית ולפעולות בסיסיות, אך מסתיר פונקציות ניהוליות שאינן רלוונטיות למטפל.
    3. מוודא שהמשתמש הנוכחי מוגדר כ-`userType=2`.

#### נקודות לשיפור / הרחבה אפשריות ב-menu_member.php
1. **איחוד קוד עם menu.php** – במקום להחזיק שני קבצים כמעט זהים, אפשר להחזיק מערך פריטי תפריט אחד ולסנן לפי userType.
2. **התאמה אישית** – ייתכן צורך להציג למטפל דוחות אישיים/היסטוריית טיפולים/יומן פרטי מורחב.

---

<a name="auth-flow"></a>
## 3. תהליך זרימת הנתונים וההרשאות
1. **המשתמש מגיע ל-index.php** – המערכת מנסה לטעון Session ולבדוק את זהותו.
2. אם אין Session / המשתמש לא מחובר, **`auth.php`** מפנה ל-login.php.
3. אם ההתחברות תקינה, נטען אובייקט משתמש (TfusaUser או אחר) לתוך `$_CURRENT_USER`.
4. **הקובץ index.php** מגדיר את התפריטים (menu.php / menu_member.php) בהתאם לסוג המשתמש והרשאותיו.
5. נטען העמוד שביקש המשתמש באמצעות `?page=...` (למשל pages/home.php).

---

<a name="main-scenarios"></a>
## 4. תרחישי עבודה עיקריים

### 4.1 כניסת משתמש למערכת
1. המשתמש פונה ל-`index.php`.
2. `auth.php` בודק אם יש Session. אין? מפנה ל-`login.php`.
3. לאחר הזדהות, מוקם אובייקט `$_CURRENT_USER`.
4. `index.php` טוען את התפריטים ואת העמוד הנכון (`?page=home`, לדוגמה).

### 4.2 מעבר בין עמודים וטעינת תפריטים
1. המשתמש לוחץ על פריט תפריט (menu.php).
2. המערכת קוראת `window.location.href='?page=something'`.
3. `index.php` מזהה `$_GET['page']="something"` וטוען את `pages/something.php`.
4. אם יש תפריט-משנה רלוונטי (submenu.php), הוא מוצג בראש העמוד.

### 4.3 בדיקת הרשאות והצגת פונקציונליות מותאמת
- אם `$_CURRENT_USER->calendar_only` = true, הסתרת כפתורים מסוימים (למשל, יצירת הזמנה).
- אם `$_CURRENT_USER->is_spa()`, הצגת הגדרות מתאימות לספא (ניהול מטפלים, שעות פעילות…).
- אם `$_CURRENT_USER->showstats`, הצגת דוחות / נתוני סטטיסטיקה.
- אם `$_CURRENT_USER->userType == 2` (מטפל), טעינת `menu_member.php` במקום `menu.php`.

### 4.4 יומן תפוסה ותצוגת הזמנות
- מעבר ל-`?page=absolute_calendar` / `?page=calendar_ver2`.
- טעינת אירועים מה-DB (משמרות, טיפולים, חדרים) והצגתן ביומן.
- לעיתים מאפשר Drag & Drop לעדכון משמרת או מיקום.

### 4.5 ניהול כרטיסי מתנה (Giftcards)
- אם האתר מאפשר `vvouchers=1`, נוסף בתפריט פריט הקשור ל-Giftcards.
- קיימים קבצים נפרדים (`giftcards-log.php` וכו') לניהול רכישות / מימושים.

### 4.6 ניהול תוספות (Extras)
- קובץ `extras.php` לניהול פריטים בתשלום נוסף (ארוחות, שירותים משלימים).
- עשוי להופיע כתוספת בהזמנה או בדוח.

### 4.7 דוחות ומסכי סטטיסטיקה
- עמודים כמו `?page=report_manage`, `?page=report_extras`, `?page=stats_treatments` ועוד.
- מאפשרים להפיק דוחות שונים (הכנסות, סטטיסטיקות טיפולים, ניהול).
- `submenu.php` מסייע לניווט בין תתי-הדוחות.

---

