
# 1. הקדמה
המסמך מתאר את מנגנון ההתחברות למערכת (למשתמשים רגילים ולמטפלים), רכיבי Front-End להצגת טפסי התחברות, ורכיבי Back-End לאימות פרטי המשתמש, יצירת סשן והפניה לאזור מוגן. כמו כן, ישנו קובץ בסיסי להגדרת אובייקט משתמש (`class.TfusaBaseUser.php`), וקובץ Header שמציג תפריט עליון עם פופ-אפ תמיכה ב-WhatsApp ועוד.

בגדול, המערכת כוללת את הפעולות הבאות:
1. **תהליך התחברות משתמש רגיל**: מבוצע דרך `login.php` בשילוב עם `js_login.php`.
2. **תהליך התחברות מטפל**: מבוצע דרך `login_member.php` בשילוב עם `js_login_member.php`.
3. **קובץ מחלקת משתמש בסיסי** (`class.TfusaBaseUser.php`), המייצג את מאפייני המשתמש, ההרשאות, והאתרים השונים המותרים לו.
4. **קובץ Header** (`partials/header.php`), שבו מוצג תפריט עליון, לוגו, וקישור לתמיכה ב-WhatsApp.
5. **קובץ Manifest** (`manifest.json`) המגדיר פרטי Progressive Web App.

---

# 2. הקבצים שנבדקו
**הקבצים הבאים קיימים בפועל ונסרקו במלואם:**

1. **user/js_login.php**  
   - קובץ PHP המגיב לפעולת התחברות משתמש רגיל (POST).  
   - מבצע בדיקות תקינות שם משתמש וסיסמה, אימות מול DB (`biz_users`, `sites_users`), יצירת אובייקט סשן (`TfusaUser`) והפניה לנתיב המתאים.

2. **user/js_login_member.php**  
   - קובץ PHP המגיב לפעולת התחברות מטפל.  
   - דומה במבנהו ל-`js_login.php`, אך פועל על טבלת `therapists` ומייצר אובייקט `MemberUser` (הקובץ שבו `MemberUser` מוגדר לא נכלל ברשימת הקבצים).  
   - מחזיר תשובת JSON הכוללת `success` ו־`link` להמשך ניתוב.

3. **user/login.php**  
   - דף Front-End (HTML + JavaScript) המאפשר התחברות משתמש רגיל.  
   - כולל טופס התחברות, קריאת jQuery ל-`js_login.php`, ושמירת הפרטים ב-localStorage להקלת הכניסה הבאה.

4. **user/login_member.php**  
   - דף Front-End (HTML + JavaScript) המאפשר התחברות מטפלים (Therapists).  
   - מבחינה ויזואלית ותפקודית דומה ל-`user/login.php`, אך פונה אל `js_login_member.php`.

5. **cms/classes/class.TfusaBaseUser.php**  
   - מחלקת בסיס (BaseUser) המגדירה את מבנה המשתמש, הרשאות (Permissions), רשימת אתרים (sites) ותכונות נוספות.  
   - כוללת מתודות עזר כגון `access()`, `sites()`, `set_sites()`, `active_site()`, `passVerify()` ועוד.

6. **partials/header.php**  
   - מקטע HTML (תפריט עליון + כפתור ווטסאפ) שמוצג בראש העמודים.  
   - מכיל תפריט המבוסס על שליפה מתוך טבלת `menu` (DB), לוגו, קישור לתמיכה ב-WhatsApp (כולל Modal שנפתח בלחיצה).  
   - מטעין קובץ תמונת לוגו, אייקונים וכד’.

7. **manifest.json**  
   - קובץ הגדרות ליישום (Progressive Web App Manifest).  
   - כולל הגדרות כגון `short_name`, `name`, צבעים (theme_color), ונתיבי האייקונים.  
   - נקרא ע"י הדפדפן לצורך התקנה כאפליקציית ווב.

---

# 3. הקבצים שלא נמצאו
לא צוין שום קובץ ברשימה המקורית כ"לא נמצא". לפיכך, הרשימה ריקה.

---

# 4. הקבצים שלא נבדקו
במהלך הסריקה זוהו אזכורים לקבצים או רכיבי קוד נוספים, שאינם מופיעים ברשימת הקבצים שנבדקו או ברשימת "לא נמצאו" (כלומר, לא התקבל קובץ שלהם בפועל). ביניהם:

1. **functions.php** – מספק ככל הנראה את הפונקציה `typemap` ועוד פונקציות עזר.  
2. **class MemberUser** – אובייקט שכנראה ממומש בקובץ נפרד (לצורך כניסת מטפלים).  
3. **class udb** – מודל/מחלקה לשכבת DB (משמשת בפעולות `udb::single_row`, `udb::single_column` וכו’).  
4. **class JsonResult** – אובייקט לייצוג תוצאה בפורמט JSON, בשימוש ב-`js_login.php` ו-`js_login_member.php`.  
5. **assets/css/style.css**, **assets/css/sweetalert2.min.css**, **assets/js/jquery-2.2.4.min.js**, **assets/js/sweetalert2.min.js** – קבצים סטטיים שנטענים ב-HTML.  
6. **favicon.ico** – (יש גם בווריאציה `/favicon.ico?v=2`).  
7. **assets/img/login.jpg**, **assets/img/lock.png**, **assets/img/error.png** – קבצים גרפיים/אייקונים.  
8. **/index/phoneLogo1.png**, **/index/footer_logo.png**, **/index/logo.png** – קבצי תמונות נוספים שמופיעים ב-`partials/header.php`.

---

# 5. תרחישים כוללים בעמוד
### הוראות לניתוח קוד ובניית תרחישי שימוש (Use Flow Scenarios)

#### 1. זיהוי כל תרחישי השימוש
התרחישים שנמצאו בקבצים:
1. **תרחיש התחברות משתמש רגיל** – דרך `login.php` + `js_login.php`.
2. **תרחיש התחברות מטפל (Therapist)** – דרך `login_member.php` + `js_login_member.php`.

---

#### תרחיש 1: התחברות משתמש רגיל

##### 1. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:**  
  המשתמש נכנס לדף `user/login.php`, מזין שם משתמש וסיסמה, לוחץ "התחבר". אם הפרטים תקינים – נכנס למערכת, אחרת מוצגת הודעת שגיאה.

| מספר צעד | פעולה מצד המשתמש          | תוצאה מצופה                                                                   |
|----------|---------------------------|--------------------------------------------------------------------------------|
| 1        | פתיחת העמוד `login.php`  | מופיע טופס התחברות (שדות: שם משתמש, סיסמה, כפתור "התחבר").                     |
| 2        | הזנת שם משתמש + סיסמה    | הנתונים מוזנים בשדות בממשק.                                                   |
| 3        | לחיצה על כפתור "התחבר"   | מבוצעת קריאת AJAX ל-`js_login.php` לבדיקה ואימות מול DB.                       |
| 4        | המתנה לתשובה             | אם ההתחברות הצליחה – מעבר אוטומטי לכתובת שהוחזרה (`res.link`). אחרת – שגיאה.   |

##### 2. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  `login.php` מפעיל באמצעות JavaScript קריאה ל-`js_login.php`. קובץ `js_login.php` קורא טבלת `biz_users` (ואח"כ `sites_users`), בודק סיסמה מול `password_verify`, מייצר סשן `TfusaUser`, ומחזיר JSON.

| מספר צעד | לוגיקה טכנית                                                                                                                                  | מיקום בקוד               | מבנה ותוכן הקוד                                                                                                |
|----------|-----------------------------------------------------------------------------------------------------------------------------------------------|--------------------------|----------------------------------------------------------------------------------------------------------------|
| 1        | בדיקה אם `$_POST['login']` הוגדר.                                                                                                            | `user/js_login.php`      | `if(isset($_POST['login'])) { ... }`                                                                            |
| 2        | בדיקת תקינות שם משתמש וסיסמה (לא ריקים).                                                                                                     | `user/js_login.php`      | `if (!$username) $result['error'] = 'Empty or illegal e-mail'; ...`                                            |
| 3        | שליפת רשומת המשתמש (`SELECT * FROM biz_users ...`) ובדיקת סיסמה באמצעות `password_verify($password, $user['password'])`.                     | `user/js_login.php`      | `$user = udb::single_row($que); if($user && password_verify(...)) {...}`                                       |
| 4        | שליפת אתרים לבדיקת הרשאות (`sites_users`), יצירת אובייקט `TfusaUser`, ושמירתו ב-`$_SESSION['tfusa']['user'][$sess->access_token]`.          | `user/js_login.php`      | `$sess = new TfusaUser(...); $_SESSION['tfusa']['user'][$sess->access_token] = $sess;`                         |
| 5        | החזרת תשובת JSON עם `success` ו-`link` לניתוב מוצלח, או הודעת שגיאה אחרת.                                                                      | `user/js_login.php`      | `$result['success'] = true; $result['link'] = '/user/' . $sess->access_token . '/'; echo json_encode($result);` |

---

#### תרחיש 2: התחברות מטפל (Therapist)

##### 1. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:**  
  המטפל נכנס לדף `user/login_member.php`, מזין שם משתמש וסיסמה, לוחץ "התחבר". אם הפרטים תקינים – מועבר לאזור ייעודי למטפלים.

| מספר צעד | פעולה מצד המטפל               | תוצאה מצופה                                                 |
|----------|------------------------------|--------------------------------------------------------------|
| 1        | פתיחת העמוד `login_member.php` | טופס התחברות מיוחד למטפלים (כותרת "כניסת מטפלים").          |
| 2        | הזנת שם משתמש + סיסמה         | הנתונים מוזנים בשדות הטופס.                                 |
| 3        | לחיצה על כפתור "התחבר"        | AJAX אל `js_login_member.php`, אימות, ולאחר מכן תשובת JSON. |
| 4        | המתנה לתשובה                 | בהצלחה – מעבר לכתובת המתאימה; בכישלון – הודעת שגיאה.        |

##### 2. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  בדומה לתרחיש הקודם, `login_member.php` מפעיל את `js_login_member.php`. האחרון בודק את טבלת `therapists` (ולא `biz_users`) ומוודא שהמטפל לא מחוק (`deleted=0`), פעיל (`active=1`), ושמועדי העבודה שלו רלוונטיים (`workStart`, `workEnd`). אם הסיסמה תואמת (`MemberUser::passVerify`), נוצר סשן עם אובייקט `MemberUser`.

| מספר צעד | לוגיקה טכנית                                                                                                                       | מיקום בקוד                  | מבנה ותוכן הקוד                                                                                                         |
|----------|------------------------------------------------------------------------------------------------------------------------------------|-----------------------------|-------------------------------------------------------------------------------------------------------------------------|
| 1        | בדיקה אם `$_POST['login']` הוגדר.                                                                                                 | `user/js_login_member.php`  | `if(isset($_POST['login'])) { ... }`                                                                                     |
| 2        | בדיקת ריקוּת שם משתמש/סיסמה.                                                                                                      | `user/js_login_member.php`  | `if (!$username) $result['error'] = 'Empty or illegal e-mail'; else if (!$password) $result['error'] = 'Empty password';` |
| 3        | קריאת טבלת `therapists`, סינון `deleted=0`, `active=1`, בדיקת `workerType <> 'fictive'` ועוד.                                    | `user/js_login_member.php`  | `$que = "SELECT `therapistID`, ... FROM `therapists` WHERE ..."; $user = udb::single_row($que);`                          |
| 4        | אימות סיסמה עם `MemberUser::passVerify`, יצירת אובייקט `MemberUser` אם הצליח, ושמירתו ב-`$_SESSION['tfusa']['member']`.          | `user/js_login_member.php`  | `$sess = new MemberUser(...); $_SESSION['tfusa']['member'][$sess->access_token] = $sess;`                                 |
| 5        | החזרת תשובת JSON (`success`, `link`) או הודעת שגיאה.                                                                               | `user/js_login_member.php`  | `$result['success'] = true; $result['link'] = '/member/' . $sess->access_token . '/'; echo json_encode($result);`         |

---

# 6. מסמך שימוש (מדריך למשתמש)
להלן מדריך מקוצר עבור המשתמשים/מטפלים:

1. **כניסת משתמש רגיל**  
   - יש לגשת לכתובת `user/login.php`.  
   - הקלידו שם משתמש וסיסמה בשדות המתאימים.  
   - לחצו על "התחבר". במידה ונתוני ההתחברות תקינים, תופנו אוטומטית לאזור האישי.

2. **כניסת מטפלים**  
   - יש לגשת לכתובת `user/login_member.php`.  
   - הזינו שם משתמש וסיסמה שהוגדרו לכם כמטפלים.  
   - לחצו על "התחבר". אם ההתחברות הצליחה, תגיעו לאזור ניהול המטפלים. אם לא, תוצג הודעת שגיאה לבירור נוסף.

3. **התפריט העליון (Header)**  
   - התפריט מסופק ע"י `partials/header.php`. מכיל קישורים לעמוד הבית, לתמיכה ב-WhatsApp (כולל פופ-אפ), ולתפריטים נוספים מתוך טבלת `menu`.

4. **PWA (Progressive Web App)**  
   - הקובץ `manifest.json` משמש עבור התקנת האתר כאפליקציית ווב. נתוניו נטענים אוטומטית ע"י הדפדפן. אין צורך בהתערבות מיוחדת מצד המשתמש.

---

# 7. סיכום
המסמך מציג את המימוש המלא של תהליך ההתחברות למערכת, הן עבור משתמשים רגילים והן עבור מטפלים, תוך פירוט תרחישי שימוש (Front-End ו-Back-End). הוא כולל גם תיאור מחלקת הבסיס למשתמש, וקובץ ה-Header המציג את תפריט האתר. לבסוף, מצורף מדריך שימוש ידידותי המסביר כיצד להתחבר ולהשתמש בעמוד. יחדיו, קבצים אלו מרכיבים תשתית כניסה מאובטחת ודינמית, המסתמכת על סשן, הרשאות ואתרים שונים, ואף מציעה תמיכה ב-WhatsApp ומאפייני PWA.



---

## 2. רשימה להעתקה של הקבצים (שורה אחר שורה)

להלן שלוש הרשימות בפורמט המבוקש:

### 1. קבצים שנבדקו
```
user/js_login.php
user/js_login_member.php
user/login.php
user/login_member.php
cms/classes/class.TfusaBaseUser.php
partials/header.php
manifest.json
```

### 2. קבצים שלא נמצאו
*(אין קבצים בקטגוריה זו)*

*(השאר ריק)*

### 3. קבצים שלא נבדקו
```
functions.php
class MemberUser (לא ידוע שם קובץ מלא)
class udb (לא ידוע שם קובץ מלא)
class JsonResult (לא ידוע שם קובץ מלא)
assets/css/style.css
assets/css/sweetalert2.min.css
assets/js/jquery-2.2.4.min.js
assets/js/sweetalert2.min.js
favicon.ico
assets/img/login.jpg
assets/img/lock.png
assets/img/error.png
/index/phoneLogo1.png
/index/footer_logo.png
/index/logo.png
```
