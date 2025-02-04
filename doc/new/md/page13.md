# 1. הקדמה
בדוח זה מוצגים ממצאים מקיפים מניתוח ארבעה קבצים אשר נמצאו ונסרקו במלואם. הקבצים מכילים קטעי קוד PHP הקשורים בעיקר לניהול הזמנות (כולל חיפוש, סינון, מחיקה והוספה) ותצוגה של ממשק משתמש (למשל תפריט משנה להזמנות). כל המידע המוצג מבוסס אך ורק על תוכן הקבצים שהוצגו בפועל, ללא שום תוספות או הנחות חיצוניות.

---

# 2. הקבצים שנבדקו
להלן ארבעת הקבצים שנסרקו ואשר בפועל קיימים:

1. **pages/orders.php**
    - מציג דף (page) המרכז הזמנות ומאפשר לבצע עליהן חיפושים, סינונים ותצוגה.
    - מבצע שימוש בפרמטרים שונים מ- `$_GET` (כגון: `sid`, `orderStatus`, `extras`, `free` וכו') כדי לבנות שאילתת SQL מורכבת, בשילוב בדיקות הרשאות, ומסנן תוצאות לפי סטטוס, מקור הגעה, תאריכים ועוד.
    - משתמש ב־ `UserPager` (מחלקה לפאגינציה – אינה מופיעה ברשימה ולכן לא נבדקה) כדי לחלק את התוצאות לעמודים.
    - קורא לפונקציה `orderComp($order)` (שאינה מופיעה בקובץ) לצורך הצגת כל הזמנה ברשימה.

2. **ajax_client.php**
    - קובץ AJAX שנועד לטפל בפעולות הקשורות בלקוח (client).
    - מטפל בשני תרחישים עיקריים דרך הפרמטר `act`:
        1. `clientInfo`: חיפוש לקוח לפי ערך חופשי (לפחות 3 תווים) ו־ siteID מורשה. מחזיר רשימת לקוחות בפורמט JSON.
        2. `delete`: מחיקת לקוח מתוך הטבלה `crm_clients` (אם המשתמש מורשה ל־ siteID המבוקש).
    - מבצע בדיקות הרשאה בסיסיות (האם המשתמש מורשה לאותו `siteID`) וכן בדיקה על אורך מחרוזת החיפוש.

3. **ajax_order.php**
    - קובץ AJAX המטפל בעיקר ביצירת הזמנת "יום שלם" (`allDay`) ביחידה (unitID) מסוימת, ובדיקת התנגשות עם הזמנות קיימות.
    - כולל פונקציות עזר פנימיות:
        - `addTfusa(...)`: מוסיפה רשומות בטבלת `tfusa` עבור כל רבע שעה בטווח הנדרש.
        - `checkAvil(...)`: בודקת התנגשות מול הזמנות קיימות (`orders`), תוך התחשבות בתאריכים ושעות.
    - אם אין התנגשות, נוצרת הזמנה חדשה, אחרת מתבצעת מחיקה של הזמנה קיימת (אם היא גם `allDay`) או החזרת קוד סטטוס המעיד על התנגשות.
    - מבצע קריאה ל- `DatesManager::update_wubook(...)` (קובץ זה לא נכלל ברשימת הסריקה ולכן לא נבדק).

4. **partials/orders_menu.php**
    - קובץ PHP קצר היוצר תפריט/סאב-תפריט (topMenu) של קישורים עבור הזמנות.
    - מגדיר מערך של תתי-תפריט (לינקים) המסננים הזמנות לפי פרמטרים (כגון `page=orders&otype=order&orderStatus=active`, `orderSign=incomplete` וכדומה).
    - מאפשר למשתמש לנווט בקלות בין סוגי ההזמנות ("פעילות", "שיריונים", "לחתימה", "אחרונות" ועוד).

---

# 3. הקבצים שלא נמצאו
לפי הרשימה שסופקה, לא צוין מפורשות על שום קובץ שהוא "לא נמצא". על כן:
- **אין קבצים שלא נמצאו**.

---

# 4. הקבצים שלא נבדקו
במהלך הסריקה זוהו אזכורים והפניות לקבצים או מחלקות שלא היו ברשימת הקבצים שנסרקו בפועל. ולכן, הם לא נכללו בבדיקה. להלן הקבצים/מחלקות הרלוונטיים:

1. **DatesManager.php**
    - מוזכר בקובץ `ajax_order.php` באמצעות קריאה לפונקציה `DatesManager::update_wubook()`.
    - לא מופיע ברשימת הקבצים שסופקו.

2. **UserUtilsNew.php**
    - מופיע בקובץ `pages/orders.php` (למשל `UserUtilsNew::init(...)`, `UserUtilsNew::$CouponsfullList` וכדומה).
    - קובץ זה לא נכלל ברשימה שסופקה.

3. **UserPager.php**
    - נעשה בו שימוש בקובץ `pages/orders.php` (אובייקט `new UserPager()`), אך אין קובץ תואם ברשימה.

(בהתאם להנחיות, התעלמנו מאזכורים ל- `auth.php`, `index.php`, `partial/menu.php`, `partial/submenu.php`, `partial/menu_member.php`, `functions.php`, גם אם הוזכרו בקוד).

---

# 5. תרחישים כוללים בעמוד (Use Flow Scenarios)

להלן תרחישי השימוש שניתן לגזור מהקבצים שנבדקו, תוך פירוט מבנה הניתוח (Front-End / Back-End).

---

## תרחיש 1: חיפוש והצגת הזמנות (pages/orders.php)
### 1. תיאור משתמש הקצה (Front-End Perspective)
המשתמש נכנס לדף ההזמנות ורוצה לבצע חיפוש/סינון:
- הוא בוחר מתחם (site) אם יש לו מספר מתחמים.
- ממלא תאריך התחלה (from) ותאריך סיום (to).
- יכול לבחור סטטוס (פעילות/מבוטלות) או חתימה (חתומות/לחתימה).
- יכול לסנן לפי מקור הגעה (sourceID), תוספים בתשלום (extras), וכן להזין טקסט חופשי (free).  
  לאחר מילוי הנתונים ולחיצה על "חפש", המערכת מציגה רשימת הזמנות העונות לפרמטרים.

#### טבלת צעדים – משתמש הקצה
| מספר צעד | פעולה מצד המשתמש                                                         | תוצאה מצופה                                                          |
|----------|------------------------------------------------------------------------|----------------------------------------------------------------------|
| 1        | פותח את הדף `orders.php`.                                              | רואה כותרת "רשימת הזמנות" ותפריט חיפוש מוסתר/מקופל.                 |
| 2        | לוחץ על "חפש הזמנות" או לחצן דומה כדי להציג את טופס החיפוש             | טופס החיפוש נפרש, מציג שדות למילוי וסינון.                           |
| 3        | ממלא פרמטרים (תאריכים, סטטוס, מקור, חיפוש חופשי וכו')                 | אין תגובה מידית, עד שיבוצע שלב שליחת הטופס.                          |
| 4        | לוחץ על כפתור "חפש"                                                   | הדף נטען מחדש ומציג רשימת הזמנות מסוננת בהתאם לפרמטרים שהוזנו.       |
| 5        | גולל או עובר בין עמודי התוצאות (Pagination)                           | דפדוף בין עמודי התוצאות לפי הגדרת `UserPager`.                      |

### 2. תיאור המימוש בקוד (Back-End Perspective)
בקובץ `pages/orders.php`:
- בפרמטרי GET נבדק `sid` ומשווים מול הרשאות המשתמש (`$_CURRENT_USER->has($sid)`).
- נבנית שאילתת SQL מורכבת לפי התנאים שנמסרו (orderStatus, extras, from, to, ועוד).
- אם המשתמש הוא `is_spa()`, השאילתה כוללת חיבורים נוספים לטבלת `treatments` וכו'.
- בשאילתה יש `GROUP BY`, `HAVING`, ו־ `ORDER BY` דינמיים, בהתאם לפרמטרים.
- נעשה שימוש במחלקת `UserPager` כדי לבצע הגבלה וניווט בין עמודים (`$pager->sqlLimit()`).
- התוצאות מוצגות ברשימה, וככל הנראה כל הזמנה עוברת לפונקציה `orderComp($order)` (אינה מופיעה בקובץ).

#### טבלת צעדים – מתכנת
| מספר צעד | לוגיקה טכנית                                                                                                                                                                  | מיקום בקוד                      | מבנה ותוכן הקוד                                                                                                                                                                          |
|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1        | קריאת פרמטרים מ־ `$_GET` (כגון `sid`, `orderStatus` וכו') ובדיקת גישה לאתר.                                                                                                   | תחילת הקובץ (~ שורות 7–14)     | ```php
$sid = intval($_GET['sid']) ?: $_CURRENT_USER->select_site();
if($sid && !$_CURRENT_USER->has($sid)){
echo 'Access denied';
return;
}
``` |
| 2        | בניית השאילתה `$que` לפי תנאים וסינונים שונים (תאריך התחלה, תאריך סיום, מקור הגעה וכו').                                                                                     | לאורכו של הקובץ                 | ```php
$que = "SELECT SQL_CALC_FOUND_ROWS `sites`.`siteName`, `orders`.* ...
        LEFT JOIN ...
        WHERE allDay=0 ";
...
``` |
| 3        | שימוש ב־ `GROUP BY orders.orderID` (או `parentOrder` אם `is_spa()`) והוספת `HAVING` עבור תנאים מיוחדים כמו `noroom` או `hasfictive`.                                          | לקראת אמצע הקובץ                | ```php
$que .= " GROUP BY orders." . $group;
...
if ($_GET['noroom'] == '1' || $_GET['isfictive'] == '1'){
    $que .= " HAVING ...";
}
``` |
| 4        | הגדרת סדר התוצאות (ORDER BY) ויצירת אובייקט `UserPager`.                                                                                                                     | אזור לפני השורה `ORDER BY`      | ```php
$que .= " ORDER BY " . (...);
$pager = new UserPager();
$que .= $pager->sqlLimit();
``` |
| 5        | שליפת התוצאות ושמירתן במערך `$orders`. הצגתן בלולאת `foreach(...)` תוך קריאה לפונקציית `orderComp($order)`.                                                                 | סוף הקובץ (~שורות אחרונות)     | ```php
$orders = udb::key_row($que, 'orderID');
foreach($orders as $order){
    orderComp($order);
}
``` |

---

## תרחיש 2: ניווט בתפריט ההזמנות (partials/orders_menu.php)
### 1. תיאור משתמש הקצה (Front-End Perspective)
המשתמש רואה תפריט עליון (או משנה) המאפשר לבחור במהירות פילטר/סוג אחר של הזמנות:
- "פעילות", "שיריונים", "לחתימה" ועוד.
- כל לחיצה מבצעת מעבר לכתובת (URL) הכוללת פרמטרים שונים (למשל `page=orders&otype=preorder&orderStatus=active`).

#### טבלת צעדים – משתמש הקצה
| מספר צעד | פעולה מצד המשתמש                                       | תוצאה מצופה                                                                                                 |
|----------|--------------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| 1        | רואה בראש הדף או בצידו תפריט המכיל קישורים שונים.      | מוצגות קטגוריות המוגדרות במערך `$subMenu2`.                                                                 |
| 2        | לוחץ על אחת הקטגוריות (למשל "פעילות").                | הדפדפן מנווט ל־ `?page=orders&otype=order&orderStatus=active`.                                             |
| 3        | כעת עמוד `orders.php` נטען עם הפרמטרים המתאימים, מציג רק הזמנות פעילות לפי הלוגיקה שבקובץ `orders.php`. | המשתמש רואה רשימה מצומצמת בהתאם לקטגוריה/פילטר הנבחר.                                                      |

### 2. תיאור המימוש בקוד (Back-End Perspective)
- בקובץ `partials/orders_menu.php` מוגדר מערך `$subMenu2` של קישורים. כל אלמנט במערך מכיל `url` ו־ `name`.
- יוצרים לולאה `foreach($subMenu2 as $sub) {...}` המדפיסה `<a href="?<?=$sub["url"]?>"><?=$sub["name"]?></a>`.
- כאשר המשתמש לוחץ על אחד הלינקים, נטענת הכתובת החדשה (`orders.php`) עם הפרמטרים הנדרשים.

#### טבלת צעדים – מתכנת
| מספר צעד | לוגיקה טכנית                                                                    | מיקום בקוד                  | מבנה ותוכן הקוד                                                                                                      |
|----------|--------------------------------------------------------------------------------|-----------------------------|-----------------------------------------------------------------------------------------------------------------------|
| 1        | הגדרת מערך `$subMenu2` המכיל פרטי קישור וסינון (לדוגמה `otype=preorder`).       | תחילת הקובץ                | ```php
$subMenu2[] = array("url"=>"page=orders&otype=order&orderStatus=active", "name"=>"פעילות");
$subMenu2[] = array("url"=>"page=orders&otype=preorder&orderStatus=active", "name"=>"שיריונים");
...
``` |
| 2        | לולאת `foreach(...)` היוצרת `<a>` בהתבסס על `url` ו־ `name`.                   | סוף הקובץ                  | ```php
<?foreach($subMenu2 as $sub){
   ...
   <a class="<?=$active?>" href="?<?=$sub["url"]?>"><?=$sub["name"]?></a>
}?>
``` |

---

## תרחיש 3: חיפוש מידע על לקוח (ajax_client.php, act=clientInfo)
### 1. תיאור משתמש הקצה (Front-End Perspective)
משתמש מנסה להקליד שם/טלפון/מייל לקוח בשדה מסוים וזקוק להשלמה או להצגת תוצאות. 
- לאחר הקלדת 3 תווים לפחות, נשלחת בקשת AJAX ל־ `ajax_client.php?act=clientInfo` (או POST).
- אם נמצאו תוצאות, הן מוצגות למשתמש כבצעדי השלמה אוטומטית או רשימה.

#### טבלת צעדים – משתמש הקצה
| מספר צעד | פעולה מצד המשתמש                                    | תוצאה מצופה                                                   |
|----------|-----------------------------------------------------|---------------------------------------------------------------|
| 1        | מזין בשדה חיפוש הלקוח מחרוזת (לפחות 3 תווים).       | המערכת שולחת בקשת AJAX ל־ `ajax_client.php?act=clientInfo`.  |
| 2        | המערכת בודקת האם יש לקוחות מתאימים באתר (siteID) הנבחר | מוצגת רשימה של לקוחות תואמים (שם, טלפון, אימייל) או הודעת "אין תוצאות". |
| 3        | המשתמש בוחר אחד מהלקוחות שהוצעו.                   | מחליט להמשיך תהליך עם פרטי הלקוח, למשל יצירת הזמנה.           |

### 2. תיאור המימוש בקוד (Back-End Perspective)
- הפרמטר `act` מוגדר כ־ `'clientInfo'`.
- מתבצעת בדיקה שהמשתמש מורשה לאותו `siteID` וכן שהערך `val` מכיל לפחות 3 תווים.
- נבנה תנאי חיפוש (WHERE) על בסיס העמודות `clientMobile`, `clientName`, `clientPassport`, `clientEmail`.
- התוצאות נשלפות מטבלת `crm_clients` ומוחזרות כ־ JSON.

#### טבלת צעדים – מתכנת
| מספר צעד | לוגיקה טכנית                                                                                     | מיקום בקוד                            | מבנה ותוכן הקוד                                                                                                      |
|----------|-------------------------------------------------------------------------------------------------|---------------------------------------|-----------------------------------------------------------------------------------------------------------------------|
| 1        | בדיקה שהמשתמש מורשה ומחרוזת חיפוש תקינה (`mb_strlen($input['val']) >= 3`).                       | סביב שורות 13–18 (בקירוב)             | ```php
if (!$input['sid'] || !$_CURRENT_USER->has($input['sid']))
    throw new Exception("Access denied to site #" . $input['sid']);
if (mb_strlen($input['val'], 'UTF-8') < 3)
    throw new Exception("Search string too short");
``` |
| 2        | הרכבת תנאי WHERE דינמי על סמך שדות רלוונטיים (phone, name, email וכו').                          | סביב שורה 24 ואילך                    | ```php
$flist = ['phone' => 'clientMobile', 'name' => 'clientName', ...];
$where = implode("` LIKE '%" . udb::escape_string($input['val']) . "%' OR `", $flist);
...
``` |
| 3        | שליפת התוצאות מטבלת `crm_clients` והחזרתן לתוך `$result['clients']`.                             | לקראת השורה 29 (בערך)                | ```php
$que = "SELECT `clientID`, `clientName` AS `name`, ...
        FROM `crm_clients`
        WHERE `siteID` = " . $input['sid'] . " AND (`" . $where . "` LIKE ...)";
$result['clients'] = udb::single_list($que);
``` |
| 4        | המערכת מחזירה JSON ללקוח (Front-End) עם התוצאות.                                                 | סוף המקטע לפני `break;`               | ```php
$result['clients'] = ...
$result['status'] = 0;
``` |

---

## תרחיש 4: מחיקת לקוח (ajax_client.php, act=delete)
### 1. תיאור משתמש הקצה (Front-End Perspective)
המשתמש לוחץ על כפתור "מחק לקוח" בממשק ניהול; פעולה זו שולחת בקשת AJAX.

#### טבלת צעדים – משתמש הקצה
| מספר צעד | פעולה מצד המשתמש        | תוצאה מצופה                                             |
|----------|-------------------------|---------------------------------------------------------|
| 1        | לוחץ על "מחק" ליד לקוח כלשהו | בקשת AJAX נשלחת ל־ `ajax_client.php?act=delete` עם `cid` ו־ `sid`. |
| 2        | מערכת בודקת הרשאה ומבצעת מחיקה | הלקוח נמחק, המשתמש רואה אישור/הודעת הצלחה.              |

### 2. תיאור המימוש בקוד (Back-End Perspective)
- `act=delete` גורם לקוד לקרוא פרמטרים `cid` (clientID) ו־ `sid` (siteID).
- בודק אם המשתמש מורשה לאתר זה.
- מריץ פקודת `DELETE` על הטבלה `crm_clients` לפי siteID ו־ clientID.

#### טבלת צעדים – מתכנת
| מספר צעד | לוגיקה טכנית                                                                                | מיקום בקוד                  | מבנה ותוכן הקוד                                                         |
|----------|--------------------------------------------------------------------------------------------|-----------------------------|--------------------------------------------------------------------------|
| 1        | קריאת `$_POST['cid']` ו־ `$_POST['sid']`.                                                 | סביב שורות 31–35 (בקירוב)  | ```php
$clientID = intval($_POST['cid']);
$siteID   = intval($_POST['sid']);
if (!$siteID || !$_CURRENT_USER->has($siteID))
    throw new Exception("Access denied to site #" . $siteID);
``` |
| 2        | מחיקה מהטבלה `crm_clients` בהתאם ל־ `siteID` ו־ `clientID`.                               | סביב שורה 37               | ```php
udb::query("DELETE FROM `crm_clients` WHERE `siteID` = " . $siteID . " AND `clientID` = " . $clientID);
``` |

---

## תרחיש 5: יצירת הזמנת יום שלם (allDay) ובדיקת התנגשות (ajax_order.php)
### 1. תיאור משתמש הקצה (Front-End Perspective)
המשתמש מבקש לפתוח הזמנה מסוג "יום שלם" ביחידה מסוימת ובתאריך נתון. 
- תרחיש זה מתרחש לרוב מאחורי הקלעים (AJAX). 
- אם היחידה פנויה (ללא התנגשות), נוצרת הזמנה. אם יש התנגשות עם הזמנה אחרת, הקוד מטפל בהתאם.

#### טבלת צעדים – משתמש הקצה
| מספר צעד | פעולה מצד המשתמש                                             | תוצאה מצופה                                                       |
|----------|--------------------------------------------------------------|-------------------------------------------------------------------|
| 1        | בוחר תאריך ויחידה ויוזם הזמנת יום שלם (למשל ע"י כפתור "הוסף הזמנה ליום שלם"). | המערכת שולחת בקשת AJAX ל־ `ajax_order.php` עם `allDay=1` ושאר פרמטרים. |
| 2        | אם היחידה פנויה, נוצרת הזמנה חדשה לכל היום.                  | המשתמש מקבל חיווי "בוצע בהצלחה" או מקביל.                          |
| 3        | אם קיימת הזמנה allDay מתנגשת, ייתכן שההזמנה הישנה תימחק או שהמשתמש יקבל סטטוס המעיד על התנגשות. | מתקבלת תשובה בקוד סטטוס (2 או 3) לפי ההיגיון המתואר בקוד.         |

### 2. תיאור המימוש בקוד (Back-End Perspective)
- בקובץ `ajax_order.php`, קוראים לפונקציות `addTfusa(...)` ו־ `checkAvil(...)`.
- קודם נאספים פרמטרים `orderID`, `allDay`, `unitID`, `from`.
- אם `allDay==1`, נשלפים שעת צ'ק-אין וצ'ק-אאוט מהטבלה `sites`. 
- קוראים ל־ `checkAvil($data)` כדי לבדוק אם קיימת התנגשות. 
- אם אין – יוצרים הזמנה חדשה בטבלת `orders` ובטבלת `orderUnits`, וגם רושמים רבעי שעות לטבלת `tfusa`.
- אם יש התנגשות עם הזמנה `allDay`, מוחקים את ההזמנה הקודמת (orders, orderUnits, tfusa) ומחזירים סטטוס `2`. אחרת מחזירים סטטוס `3`.

#### טבלת צעדים – מתכנת
| מספר צעד | לוגיקה טכנית                                                                                                                                                                                        | מיקום בקוד          | מבנה ותוכן הקוד                                                                                                                                                                                            |
|----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1        | קליטת פרמטרים POST (למשל `$orderID`, `$allDay`, `$unitID`, `$dateFrom`).                                                                                                                          | אחרי `try{` הראשי  | ```php
$orderID = intval($_POST['orderID']);
$allDay = intval($_POST['allDay']);
$unitID = intval($_POST['unitID']);
$dateFrom = typemap(implode('-',array_reverse(explode('/',trim($_POST['from'])))),"date");
``` |
| 2        | בדיקה אם `$allDay` == 1; שאז נשלפים `checkInHour`, `checkOutHour` מהטבלה `sites`.                                                                                                                 | ~ שורה 32 ואילך     | ```php
$que = "SELECT `sites`.`checkInHour`, `sites`.`checkOutHour`, `sites`.`siteID`, rooms.roomID
        FROM `sites`
        INNER JOIN `rooms` USING (siteID)
        ...
``` |
| 3        | קריאה לפונקציה `checkAvil($data)` כדי לבדוק האם כבר קיימת הזמנה פעילה.                                                                                                                            | סביב שורות 46–57    | ```php
if(!checkAvil($data)){
   // אין התנגשות: יוצרים רשומה ב־ orders + orderUnits + tfusa
} else {
   // יש התנגשות: מוחקים או מחזירים סטטוס
}
``` |
| 4        | יצירת הזמנה חדשה בטבלת `orders`, החדרת היחידה ל־ `orderUnits`, וקריאה ל־ `addTfusa()` ליצירת רשומות רבע שעה.                                                                                      | באותו אזור          | ```php
$orderID = udb::insert('orders', [
   'siteID' => $siteID,
   'timeFrom' => $timeFrom,
   'timeUntil' => $timeUntil,
   'allDay' => 1
]);
udb::insert("orderUnits", ["orderID" => $orderID, "unitID" => $unitID]);
addTfusa($orderID, $unitID, $timeFrom, $timeUntil);
$result['status'] = 1;
``` |
| 5        | טיפול בהזמנה מתנגשת: במקרה של הזמנת יום שלם קודמת, מחיקתה מטבלאות `orders`, `orderUnits`, `tfusa`; אחרת הגדרת `$result['status']=3`.                                                              | סביב שורות 65–73    | ```php
if($checkOcc['allDay']){
    udb::query("DELETE FROM `orders` WHERE `orderID`=".$checkOcc['orderID']);
    udb::query("DELETE FROM `orderUnits` WHERE `orderID`=".$checkOcc['orderID']);
    udb::query("DELETE FROM `tfusa` WHERE `orderID`=".$checkOcc['orderID']);
    $result['status'] = 2;
}else{
    $result['status'] = 3;
}
``` |

---

# 6. מסמך שימוש (מדריך למשתמש)
להלן מדריך מקוצר לשימוש בפונקציונליות הקיימת, מנקודת מבט משתמש קצה:

1. **ניווט בהזמנות**  
   - בחלק העליון של הדף, יופיע תפריט (orders_menu) המציג קישורים לסוגים שונים של הזמנות (למשל "פעילות", "שיריונים", "לחתימה").  
   - לחיצה על אחד הקישורים תנתב אותך לעמוד ההזמנות (`orders.php`) עם הפילטר המתאים.

2. **חיפוש וסינון הזמנות**  
   1. בדף ההזמנות, לחצו על "חפש הזמנות" או על האזור המציג את טופס החיפוש.  
   2. מלאו את השדות הרצויים: תאריך, סטטוס, מקור הגעה, וכו'.  
   3. הקלידו בשדה "חיפוש חופשי" אם ברצונכם לסנן לפי שם/טלפון/מייל הלקוח.  
   4. לחצו על "חפש" כדי לרענן את הרשימה.  
   5. ניתן לעבור בין עמודי תוצאות באמצעות ה־Pagination בתחתית.

3. **חיפוש מידע על לקוח**  
   1. בעת הזנת שם/טלפון/מייל לחיפוש, הזינו לפחות 3 תווים.  
   2. המערכת תחפש אוטומטית (או ע"י שליחת טופס) ותציג רשימת התאמות.  
   3. בחרו לקוח מתאים כדי להמשיך את התהליך (למשל ליצירת הזמנה).

4. **מחיקת לקוח**  
   1. לחצו על כפתור "מחק" ליד הלקוח הרלוונטי, במידה והוא מופיע ברשימת הלקוחות.  
   2. אם יש לכם הרשאות, הלקוח יוסר מהמערכת בהצלחה.

5. **יצירת הזמנת יום שלם ובדיקת התנגשות**  
   1. לצורך הקצאת יחידה (room/unit) ליום שלם, בחרו תאריך ויחידה מתאימים.  
   2. שמרו/שלחו את הבקשה. אם היחידה פנויה, תתקבל הודעת אישור והמערכת תיצור את ההזמנה.  
   3. אם קיימת הזמנה יום שלם קודמת, היא תוסר או תופיע התראה על התנגשות.  

---

# 7. סיכום
מסמך זה מציג ניתוח מלא של ארבעה קבצים במערכת: `pages/orders.php`, `ajax_client.php`, `ajax_order.php`, ו- `partials/orders_menu.php`. בכל אחד מהם מטופלים היבטים שונים של מערכת ההזמנות והלקוחות, החל מחיפוש וסינון מורכבים ועד יצירת הזמנות יום שלם תוך בדיקת התנגשויות. בנוסף, מאפיינים כמו תפריט עליון (SubMenu) מסייעים לניווט מהיר בין סוגי הזמנות.  
נמצאו הפניות נוספות לרכיבים כגון `DatesManager.php`, `UserUtilsNew.php` ו- `UserPager.php` אשר אינם נכללים בקבצים המסופקים, ולכן לא נבדקו בפועל.

