# 1. הקדמה
במסגרת הבדיקה בוצעה סריקה מעמיקה בארבעה קבצים עיקריים הקיימים בפועל:
- **pages/agreements.php**
- **partials/settings_menu.php**
- **pages/settings.php**
- **ajax_settings.php**

הקוד מציג מנגנון לניהול אתרים (Sites), כאשר למשתמש (לדוגמה, מנהל מערכת) יש אתר פעיל (active_site) שאליו הוא מפנה את הפעולות (עריכת הסכמים, הגדרת חוות דעת, הגדרת תכונות נוספות באתר). כמו כן, יש מנגנוני AJAX מתקדמים המאפשרים עדכון הגדרות שונות של האתר – החל מהגדרת ביטול הזמנות ועד הגדרות שכר.  
ניתן לראות שימוש במחלקות ופונקציות חיצוניות (כגון `udb`, `Translation`, `SalarySite`, `SalaryMaster`, `JsonResult`, ועוד) שאינן חלק מהקבצים שסופקו במפורש. המידע במערכת מתבסס על מסד נתונים (טבלאות `sites`, `sites_langs`, `therapists`, `salaryLog` וכו'), והפעולות כוללות עדכונים ושאילתות (SQL).

# 2. הקבצים שנבדקו

1. **pages/agreements.php**
   - אחראי על עריכת הסכמים שונים (1–4 + הסכם שכירות), תוך בחירת הסכם ברירת מחדל.
   - מתבסס על המשתמש הנוכחי ו"אתר פעיל" (Active Site), כך שההסכמים נשמרים למסד הנתונים (`sites_langs`) באמצעות `udb::update()` ו-`Translation::save_row()`.
   - ישנו מנגנון המרה טיפוסים באמצעות `typemap()`, והדף מרענן את עצמו אחרי השמירה (JavaScript).

2. **partials/settings_menu.php**
   - מציג תפריט עליון המאפשר מעבר ל"שני" עמודים עיקריים: "הסכמים" ו"חוות דעת".
   - מזהה את העמוד הפעיל (באמצעות השוואה ל-`$_SERVER['QUERY_STRING']`) ומסמן אותו כ-`active`.

3. **pages/settings.php**
   - עוסק בהגדרות חוות דעת (Reviews) עבור האתר הפעיל.
   - מציג אפשרות (דרך מתגים/checkbox) להפעיל שליחה אוטומטית של בקשה למילוי חוות דעת (`sendReviews`) ולפרסם חוות דעת (`publishReviews`).
   - בלחיצה על מתג, יש קריאות AJAX ל-`ajax_settings.php` (פונקציות JavaScript: `setReview` / `setPublish`).
   - טוען קובץ עיצוב CSS דינמי (`assets/css/style_ctrl.php`).

4. **ajax_settings.php**
   - מקבל בקשות AJAX (POST) מכמה סוגים, ומשנה הגדרות שונות באתר (בטבלת `sites` או `therapists` וכו').
   - עדיפות נמוכה (type=1 או type=2) משמשת להפעלה/כיבוי `sendReviews` / `publishReviews` (כפי שנראה ב-`pages/settings.php`).
   - מעבר לכך, מטפל בפרמטרים כגון `act` (למשל `calendarSettings`, `hideUnfilled`, `CancelCondSpa`, הגדרות שכר (`baseSalary`, `masterSalary`), ועוד) – כל מקרה מתעדכן במסד הנתונים.
   - משתמש במחלקות כמו `SalarySite` ו-`SalaryMaster` לשינוי הגדרות שכר, וכן ב-`JsonResult` כדי להשיב JSON מובנה.

# 3. הקבצים שלא נמצאו
לא דווח על קבצים מרשימתך שקיבלו סטטוס "הקובץ לא נמצא". לכן, הרשימה ריקה.

# 4. הקבצים שלא נבדקו
להלן קבצים/מחלקות שפועלם נרמז בקוד, אולם אינם חלק מרשימת הקבצים הסרוקים ולא סופקו בפועל:

- **udb.php** (או קובץ אחר המכיל את המחלקה `udb`)
- **Translation.php** (מכיל את המחלקה `Translation`)
- **SalarySite.php** (מכיל את המחלקה `SalarySite`)
- **SalaryMaster.php** (מכיל את המחלקה `SalaryMaster`)
- **JsonResult.php** (מכיל את המחלקה `JsonResult`)

> **הערה**: קבצים שהוגדרו בהנחיות ככאלה שיש להתעלם מאזכורם (כגון `auth.php`, `index.php`, `partial/menu.php`, `partial/submenu.php`, `partial/menu_member.php`, `functions.php`) אינם מופיעים ברשימה זו.

# 5. תרחישים כוללים בעמוד

להלן הניתוח המומלץ של כל תרחיש שימוש אפשרי, בהתבסס על ארבעת הקבצים הסרוקים.

---

## תרחיש 1: עריכת הסכמים (pages/agreements.php)

### א. תיאור משתמש הקצה (Front-End Perspective)
1. **תיאור כללי**
   - המשתמש (מנהל האתר או בעל הרשאות) נכנס לדף "הסכמים" ומבצע עדכונים להסכמים הרלוונטיים לאתר הפעיל.
   - באפשרותו לבחור איזה הסכם יהפוך להסכם "ברירת מחדל".

2. **טבלת צעדי התרחיש מצד המשתמש**

| **מספר צעד** | **פעולה מצד המשתמש**                      | **תוצאה מצופה**                                        |
|--------------|-------------------------------------------|--------------------------------------------------------|
| 1            | נכנס למערכת וניגש לכתובת `?page=agreements` (או דרך התפריט) | מוצג דף עריכת הסכמים (טקסטים ותיבות לעריכה).          |
| 2            | מעדכן את הטקסט בהסכם מסוים בתיבת הטקסט (לדוגמה "הסכם 2") | התוכן החדש מוצג בתיבה (textarea) לפני שליחה.         |
| 3            | בוחר סימון רדיו (radio) כדי להגדיר הסכם ספציפי כברירת מחדל | רדיו בוטן מסמן איזה הסכם הוא ה-Default.              |
| 4            | לוחץ "עדכן הסכם"                           | הדף מתעדכן / נטען מחדש, והמידע נשמר במערכת.           |

### ב. תיאור המימוש בקוד (Back-End Perspective)
1. **תיאור כללי**
   - בקובץ `pages/agreements.php` בודקים את האתר הפעיל (Active Site). אם הטופס נשלח (POST), המערכת קוראת את הערכים שהוזנו, ממירה אותם באמצעות `typemap()`, ולאחר מכן מבצעת עדכון בטבלת `sites_langs`.
   - בנוסף, נעשית קריאה ל-`Translation::save_row()` לשמירת התוכן.
   - בסיום, יש סקריפט JavaScript שמבצע רענון עמוד: `window.location.href = window.location.href;`.

2. **טבלת צעדי התרחיש מצד המתכנת**

| **מספר צעד** | **לוגיקה טכנית**                                                                                                    | **מיקום בקוד**                       | **מבנה ותוכן קוד**                                                                                                                              |
|--------------|---------------------------------------------------------------------------------------------------------------------|---------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| 1            | בדיקה/הגדרה של האתר הפעיל (`$_CURRENT_USER->active_site()`).                                                        | תחילת הקובץ `pages/agreements.php`    | ```php
if (!$_CURRENT_USER->select_site()){
$_CURRENT_USER->select_site($_CURRENT_USER->active_site());
// ...
}
```                                                           |
| 2            | אם `$_SERVER['REQUEST_METHOD'] == 'POST'`, קריאת פרמטרים מ-`$_POST` והמרה באמצעות `typemap()`.                       | בלוק ה-`if ('POST' == ...) { ... }`   | ```php
$data = typemap($_POST, [
  'agreement2'   => 'text',
  'agreement3'   => 'text',
  'agreement4'   => 'text',
  'agreement_rent'  => 'text',
  'defaultAgr'   => 'int'
]);
```                                                               |
| 3            | עדכון `sites_langs` וקריאה ל-`Translation::save_row(...)`.                                                          | באותו בלוק POST                      | ```php
udb::update('sites_langs', $data, "domainID=1 AND langID=1 AND siteID = " . $asid);
Translation::save_row('sites', $asid, $data, 1, 1);
```                                                               |
| 4            | לאחר העדכון, פקודת `<script>window.location.href = ...</script>` לביצוע רענון.                                      | ממש לפני `return;`                    | ```php
echo "<script>window.location.href = window.location.href;</script>";
```                                                               |

---

## תרחיש 2: ניווט בתפריט העליון (partials/settings_menu.php)

### א. תיאור משתמש הקצה (Front-End Perspective)
1. **תיאור כללי**  
   - בראש העמוד יש תפריט פנימי המציג קישורים ל"סכמים" ו"חוות דעת".  
   - המשתמש לוחץ על אחד הקישורים כדי להגיע לעמוד המבוקש.

2. **טבלת צעדי התרחיש מצד המשתמש**

| **מספר צעד** | **פעולה מצד המשתמש**            | **תוצאה מצופה**                            |
|--------------|---------------------------------|--------------------------------------------|
| 1            | לוחץ בתפריט על "הסכמים"        | מנווט אל `?page=agreements`.               |
| 2            | לוחץ בתפריט על "חוות דעת"       | מנווט אל `?page=settings`.                 |

### ב. תיאור המימוש בקוד (Back-End Perspective)
1. **תיאור כללי**  
   - הקובץ `partials/settings_menu.php` מגדיר מערך `$subMenu2` ומכניס לתוכו קישורים.  
   - עובר עליהם בלולאה, יוצר `<a>` דינמי, ובודק אם ה-query string הנוכחי (`$_SERVER['QUERY_STRING']`) תואם לאחד הערכים – אם כן, מוסיף CSS class בשם `active`.  

2. **טבלת צעדי התרחיש מצד המתכנת**

| **מספר צעד** | **לוגיקה טכנית**                                                                 | **מיקום בקוד**                         | **מבנה ותוכן קוד**                                                                                              |
|--------------|----------------------------------------------------------------------------------|-----------------------------------------|------------------------------------------------------------------------------------------------------------------|
| 1            | הגדרת מערך `$subMenu2` עם שני איברים: "הסכמים" ו"חוות דעת".                       | תחילת הקובץ `partials/settings_menu.php` | ```php
$subMenu2[] = ["url"=>"page=agreements", "name"=>"הסכמים"];
$subMenu2[] = ["url"=>"page=settings", "name"=>"חוות דעת"];
```                           |
| 2            | יצירת `<a>` דינמי ושימוש במשתנה `$active` לצורך סימון כפתור פעיל.                  | הלולאה `foreach($subMenu2 as $sub) {...}` | ```php
$active = ($_SERVER['QUERY_STRING'] == $sub["url"]) ? "active" : "";
echo "<a class=\"$active\" href=\"?$sub[url]\">$sub[name]</a>";
``` |

---

## תרחיש 3: הגדרת חוות דעת (pages/settings.php) + הפעלה/כיבוי באמצעות ajax_settings.php

### א. תיאור משתמש הקצה (Front-End Perspective)
1. **תיאור כללי**  
   - המשתמש עובר למסך "חוות דעת" ורואה שם מתגים (checkbox switches) עבור הגדרה של שליחת בקשות חוות דעת אוטומטיות (`sendReviews`) ופרסום חוות דעת (`publishReviews`).  
   - בעת לחיצה על מתג, הוא אמור לשנות את ההגדרה (לכאורה On/Off) ולשמור זאת במערכת.

2. **טבלת צעדי התרחיש מצד המשתמש**

| **מספר צעד** | **פעולה מצד המשתמש**                                                        | **תוצאה מצופה**                                                           |
|--------------|----------------------------------------------------------------------------|---------------------------------------------------------------------------|
| 1            | ניגש לכתובת `?page=settings` דרך התפריט העליון                             | מוצג דף "הגדרת חוות דעת" עם רשימת אתרים והגדרות (מתגים).                  |
| 2            | לוחץ על המתג (switch) של "שליחה אוטומטית" (sendReviews)                    | מתבצעת קריאת AJAX ל-`ajax_settings.php?type=1` ומאחורי הקלעים הערך משתנה. |
| 3            | בודק שהמצב המעודכן (checked/unchecked) מופיע בהתאם לחזרה מה-ajax.          | ממשיך בעבודה, כשהמערכת זוכרת את הערך החדש במסד הנתונים.                   |
| 4            | לוחץ על המתג (switch) של "פרסום חוות דעת" (publishReviews)                 | מתבצעת קריאת AJAX ל-`ajax_settings.php?type=2` וההגדרה נשמרת.            |

### ב. תיאור המימוש בקוד (Back-End Perspective)
1. **תיאור כללי**  
   - קובץ `pages/settings.php` מציג את המתגים בחלק ה-HTML, אך הם מוגדרים כ-`disabled` בדוגמה. כשלוחצים עליהם (באזור ה-`div`), הם קוראים לפונקציות JavaScript `setReview(siteid)` או `setPublish(siteid)`.  
   - כל פונקציה שולחת `$.post("ajax_settings.php", { id: siteid, type:1/2 })`.  
   - ב-`ajax_settings.php` יש בלוק שמזהה אם `intval($_POST['type']) == 1` או `2`, ואז קורא/מעדכן ערך (`sendReviews` או `publishReviews`) במסד הנתונים.

2. **טבלת צעדי התרחיש מצד המתכנת**

| **מספר צעד** | **לוגיקה טכנית**                                                                                                                                                   | **מיקום בקוד**                        | **מבנה ותוכן קוד**                                                                                                                                                                  |
|--------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1            | ב-`pages/settings.php` נטענת רשימת אתרים מה-DB (`$snames`), ומוצגים לכל אתר שני מתגים (שליחה/פרסום).                                                                | תחילת `pages/settings.php`             | ```php
$snames = udb::full_list("SELECT siteID, siteName, sendReviews, publishReviews FROM sites WHERE siteID IN (" . $asid . ")");
```                                                                                                                                                     |
| 2            | בעת קליק, מופעלות פונקציות JS: `setReview(siteid)` / `setPublish(siteid)`.                                                                                         | `<script>function setReview(){...}</script>` | ```js
function setReview(siteid){
  $.post("ajax_settings.php",{id:siteid, type:1}, function(res){
    // בודק אם res==0 או אחר ומעדכן את ה-checkbox
  });
}
```                                                                                                                                                                       |
| 3            | בקובץ `ajax_settings.php` (בהתאם ל-`type`=1 או 2) מתבצעת שאילתא כדי לזהות את הערך הנוכחי (sendReviews/publishReviews), ואז הופכים אותו (1 → 0 או 0 → 1).          | בתוך `if(intval($_POST['type']) ==1) {...}` או `==2` | ```php
$exists = udb::single_value("SELECT sendReviews FROM sites WHERE siteID =".intval($_POST['id']));
$change = $exists==1? 0 : 1;
udb::update('sites', ['sendReviews'=>$change], 'siteID='.intval($_POST['id']));
echo $change;
exit;
```                                                                                                  |
| 4            | מערך/ערך מוחזר ל-JavaScript (echo $change). JS מתאם אם לעשות `elem.prop("checked", true)` או `false`.                                                             | בסוף הבלוק `ajax_settings.php`         | ```php
echo $change; 
exit;
```                                                                                                                                                                               |

---

## תרחיש 4: הגדרות מתקדמות באמצעות ajax_settings.php

### א. תיאור משתמש הקצה (Front-End Perspective)
1. **תיאור כללי**  
   - מלבד "sendReviews" ו-"publishReviews", קיימות במערכת הגדרות נוספות (לפי הקוד ב-`ajax_settings.php`) כגון `calendarSettings`, `hideUnfilled`, `enableReminders`, הגדרות ביטול ספא (`CancelCondSpa`), הגדרות שכר (`baseSalary`, `masterSalary`), ועוד.  
   - ברמת UI לא סופק לנו קובץ תצוגה מפורט המציג את כל האפשרויות הללו, אך ניתן להניח שיש טפסים או כפתורים נוספים שמשתמשים ב-ajax לצורך עדכונים.

2. **טבלת צעדי התרחיש מצד המשתמש** (באופן כללי)

| **מספר צעד** | **פעולה מצד המשתמש**                                                    | **תוצאה מצופה**                                                             |
|--------------|-------------------------------------------------------------------------|-----------------------------------------------------------------------------|
| 1            | מבצע פעולה בממשק (למשל לוחץ על כפתור כלשהו במערכת ההגדרות הנרחבת).      | המערכת שולחת קריאת POST ל-`ajax_settings.php` עם `act=...'` או פרמטרים רלוונטיים. |
| 2            | ממתין לתגובה מהשרת (JSON/טקסט) ומצפה שהנתונים יתעדכנו במסד.            | הערכים החדשים נשמרים, והמשתמש רואה את השינוי ב-UI או בתצוגה.                 |

### ב. תיאור המימוש בקוד (Back-End Perspective)
1. **תיאור כללי**  
   - ב-`ajax_settings.php` יש בלוק גדול של `switch ($_POST['act']) { ... }`. עבור כל `case` ניתן שם לפעולה (למשל `calendarSettings`, `hideUnfilled`, `bookBefore`, ועוד).  
   - כל פעולה משנה ערכים בטבלה `sites` (או `therapists`) בהתאמה, לאחר בדיקות גישה (האם למשתמש יש הרשאה לאתר הספציפי וכו').

2. **טבלת צעדי התרחיש מצד המתכנת**

| **מספר צעד** | **לוגיקה טכנית**                                                                                                          | **מיקום בקוד**                     | **מבנה ותוכן קוד**                                                                                                                          |
|--------------|---------------------------------------------------------------------------------------------------------------------------|-------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| 1            | מזהה את `$_POST['act']` ומבצע `switch($_POST['act'])`.                                                                    | בגוף הקובץ `ajax_settings.php`      | ```php
switch($_POST['act']){
  case 'calendarSettings':
    // מטפל בהגדרות שונות
    break;
  case 'hideUnfilled':
    // ...
    break;
  // ...
}
```                                                                                                            |
| 2            | בכל מקרה `case`, בודק שיש `siteID` תקף ושיש למשתמש הנוכחי גישה לאתר (`$_CURRENT_USER->has($sid)`).                         | בתוך כל `case '...'`                | ```php
if (!$_CURRENT_USER->has($sid))
  throw new Exception("Access denied to site #".$sid);
```                                                                                                                              |
| 3            | מבצע עדכון בטבלת `sites` או `therapists` בהתאם לפעולה (`udb::update('sites', [...])`).                                    | בכל `case` ספציפי                   | ```php
udb::update('sites', ['hideUnfilled' => $hideUnfilled], 'siteID='.$sid);
```                                                                                                                                    |
| 4            | מחזיר תשובה במבנה JSON (באמצעות `JsonResult`) או `echo $change` / `exit`.                                                  | בסוף הביצוע בכל `case`.             | ```php
$result['status'] = 0;
echo json_encode($result);
exit;
```                                                                                                                                  |

---

# 6. מסמך שימוש (מדריך למשתמש)

להלן מדריך שימוש כללי (בגוף שני), המסכם את השימוש בשלושת המסכים המרכזיים ובפעולות ה-AJAX:

1. **גישה למסכים**  
   - לאחר התחברות למערכת, תראה בראש העמוד תפריט קטן (המתקבל מ-`partials/settings_menu.php`).  
   - לחץ על "הסכמים" כדי לנווט למסך עריכת הסכמים, או על "חוות דעת" כדי לנווט למסך ניהול חוות דעת.

2. **עריכת הסכמים**  
   - במסך "הסכמים" תוכל לראות הסכם 1 (קריא בלבד) והסכם 2–4 + הסכם שכירות (editable).  
   - ערוך את תוכן הטקסט (ב-`textarea`) לפי הצורך. אם ברצונך שמסמך מסוים יהיה ברירת המחדל (default) – סמן את ה-radio המתאים.  
   - לחיצה על "עדכן הסכם" תשמור את השינויים ותטען מחדש את העמוד כדי להציגם.

3. **ניהול חוות דעת**  
   - במסך "חוות דעת" תראה רשימה של אתרים (לרוב האתר הפעיל).  
   - יש מתג (switch) לשליחת בקשות למילוי חוות דעת אוטומטית, וכן מתג לפרסום חוות דעת באתר. בלחיצה עליו (אם הוא פעיל) תתבצע קריאת AJAX ותשנה את ההגדרה מאחורי הקלעים.  
   - ודא שהמתג משקף את המצב הרצוי (On/Off).

4. **פעולות מתקדמות (ajax_settings.php)**  
   - במערכת קיימות הגדרות נוספות (כגון הגדרות שכר, הגדרות ביטול, הגדרות תצוגה ועוד) המתבצעות בדרך דומה – שליחת בקשת AJAX ל-`ajax_settings.php`.  
   - לפי הפעולה הנדרשת, ייתכן ותהיה לך גישה לכפתור/טופס בממשק שגורם לשליחת `act=...` לשרת, אשר יעדכן את ההגדרות במסד הנתונים.  
   - לאחר השינוי, התוצאה נשמרת במסד והמערכת מגיבה בהתאם (למשל שינויים הנראים מיידית או לאחר רענון דף).

# 7. סיכום
שלושת הקבצים הראשונים (`pages/agreements.php`, `partials/settings_menu.php`, `pages/settings.php`) מתווים את מבנה הניהול של הסכמים וחוות דעת באתר, בעוד הקובץ הרביעי (`ajax_settings.php`) אחראי על ביצוע מגוון עדכונים דרך קריאות AJAX.  
המערכת משתמשת במחלקות חיצוניות (כגון `udb`, `Translation`, `SalarySite`, `SalaryMaster`) לצורכי תקשורת עם מסד הנתונים ולניהול לוגיקות מתקדמות (כגון שכר, ביטול הזמנות, הגדרות תצוגה ועוד). באופן זה, קל להרחיב ולהתאים את המערכת לצרכים מגוונים תוך שמירה על מבנה קוד מסודר.
