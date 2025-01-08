# 1. הקדמה
מערכת הגיפטקארדים המתוארת בקבצים שנבדקו מרכזת לוגיקה לניהול ושימוש בגיפטקארדים (Gift Cards).  
הקוד עוסק בהצגת גיפטקארדים, הוספה, עריכה, מחיקה, הגדרות כלליות (תצוגה/רקעים/טקסטים) וכן מימוש הגיפטקארד (חלקי או מלא).  
בנוסף, קיימת יכולת הנפקת/הורדת חשבוניות (בעיקר במקרים של חיוב "ישיר"), ביטולים וזיכויים, והצגת מידע למשתמש על הגיפטקארד עצמו.

הפרויקט מכיל ממשק Front-End (בעיקר דרך `pages/giftcards.php` ו-JavaScript בקובץ `assets/js/giftcards.js`), לצד קריאות AJAX (Back-End) לטיפול בפעולות מורכבות יותר דרך קבצים ייעודיים.

---

# 2. הקבצים שנבדקו

**1. `pages/giftcards.php`**
- מציג ממשק משתמש ראשי לניהול גיפטקארדים (רשימה, כפתור "הוסף חדש", כפתור "הגדרות תצוגת עמוד" וכו’).
- כולל בתוכו תגית `<script>` לטעינת הסקריפט `giftcards.js` מהתיקייה `assets/js/`.
- מציג רשימה של הגיפטקארדים (כולל כפתורי עריכה, מחיקה, הפעלה/השבתה) ומאפשר בחירת מתחם (site) אם ישנם כמה.
- מציג חלונית פופ־אפ (modal) לעריכת/הוספת גיפטקארד (עם טפסים למילוי פרטים).
- מציג חלונית פופ־אפ נוספת המאפשרת עריכת הגדרות כלליות של העמוד (כותרת, לוגו, תמונת רקע, טקסט עליון, תיאור עסק, הערות וכו’).

**2. `assets/js/giftcards.js`**
- קובץ JavaScript האחראי ללוגיקת Front-End של עמוד הגיפטקארדים.
- כולל פונקציות AJAX לטעינת נתוני גיפטקארד ספציפי (`loadGiftCardData`), שליחה ושמירה של ערכים חדשים (`submitGiftCardForm`), מחיקה והפעלה/ביטול הפעלה.
- מטפל גם בעדכון הגדרות הגלובליות (`submitGeneralForm`) ובטעינת הטופס המתאים (לדוגמה: `loadGeneralForm`).
- כולל מנגנון סידור גיפטקארדים (מיקום/סדר רשימה) וקריאה ל-AJAX לצורך עדכון ה-DB בסדר החדש.
- כולל גם לוגיקת "מימוש" (Redeem) חלקי או מלא (`mimushPop`) וקריאות לחיוב/זיכוי.

**3. `ajax_load_giftcard.php`**
- קובץ Back-End המטפל בפעולות CRUD (Create / Read / Update / Delete) עבור טבלת `giftCards`.
- `act=0` – שליפת נתוני גיפטקארד בודד.
- `act=1` – הוספה או עדכון של גיפטקארד לפי POST (כותרת, סכום, תוקף בחודשים, ועוד).
- `act=2` – הפעלה/ביטול (היפוך ערך `active`).
- `act=3` – מחיקה לוגית (set `deleted=1`).
- `act=4` – עדכון סדר ההצגה (`showOrder`) של מספר גיפטקארדים.

**4. `ajax_giftcards_setting.php`**
- מטפל בשמירת הגדרות גלובליות של עמוד הגיפטקארד (בטבלה `giftCardsSetting`), כגון כותרת, לוגו, תמונת רקע, טקסטים וכו’.
- תומך בהעלאת קבצי תמונה (רקע/לוגו) ומתאים את תאריך העדכון ומנהל העדכון.

**5. `ajax_pop_gift.php`**
- מחזיר תוכן HTML (פופ־אפ) של מידע על רכישה ספציפית של גיפטקארד (`gifts_purchases`), כולל שימושים שכבר בוצעו (log מימושים) ויתרה שנותרה לשימוש.
- מתייחס למידע אודות סכום השובר, מקור הרכישה, ברכה אישית, תוקף, ועוד.
- מציג גם טבלה של מימושים קודמים (חלקיים או מלאים), ומאפשר פעולת "ביטול מימוש" באם זה אפשרי.
- מתייחס לחיוב "ישיר" (terminal=direct) שעשוי לכלול הפקת חשבונית או זיכוי.

**6. `ajax_giftcards.php`**
- קובץ Back-End המטפל בפעולות שונות הקשורות לרכישה/מימוש, לרבות:
    - Refund (זיכוי) במידה והגיפטקארד נרכש במסלול ישיר (direct).
    - ביטול מימוש (deleteUsage).
    - מימוש (use) חלקי או מלא לפי סכום שהמשתמש הגדיר.
- מקפיד לבצע בדיקות (כמו לוודא שלא בוצעו מימושים קודמים שמונעים זיכוי, ולוודא שסכום המימוש אינו חורג מיתרת הגיפטקארד).

**7. `download_invoice_gc.php`**
- קובץ האחראי להורדת/הצגת חשבונית (Invoice) של רכישת גיפטקארד במסלול חיוב ישיר.
- בודק אם הלקוח הנוכחי מורשה גישה (אותו siteID) ושהרכישה אכן רלוונטית לגיפטקארד המבוקש.
- במידה והחשבונית הופקה ע"י ספק חיצוני (למשל CardCom / YaadPay), נעשית קריאה להורדת ה-PDF או הפנייה לכתובת הרלוונטית.

---

# 3. הקבצים שלא נמצאו
**אין קבצים שלא נמצאו.**

---

# 4. הקבצים שלא נבדקו
להלן הקבצים שאליהם מתייחס הקוד, אך אינם מופיעים כלל ברשימת הקבצים שנבדקו או ברשימת "לא נמצאו":
1. `picUpload.php`
2. `style_ctrl.php`

---

# 5. תרחישים כוללים בעמוד

> להלן הדגמה כללית של תרחישי שימוש (Use Flow Scenarios) הקיימים במערכת, על סמך הקוד שנסרק. הניתוח מחולק למבט Front-End (מנקודת מבט המשתמש) ומבט Back-End (הפעולות שמתרחשות מאחורי הקלעים).

---

## תרחיש 1: צפייה ברשימת הגיפטקארדים

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש מגיע לעמוד הניהול ורואה רשימת גיפטקארדים (אם יש לו מספר מתחמים, ייתכן שעליו לבחור מתחם מהתפריט הנפתח).

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש             | תוצאה מצופה                                                            |
|----------|-----------------------------|-------------------------------------------------------------------------|
| 1        | כניסה לכתובת `giftcards.php` | מוצגת רשימת הגיפטקארדים הקיימים (בהתאם ל-siteID שנבחר כברירת מחדל).    |
| 2        | (אופציונלי) בחירת מתחם מסוים מהתפריט | הרשימה מתעדכנת בהתאם ל-giftcards השייכים למתחם הנבחר.               |
| 3        | צפייה בכפתורים הקיימים ("הוסף חדש", "הגדרות תצוגת עמוד" וכד’) | המשתמש מזהה אפשרויות עריכה ו/או הוספת גיפטקארד חדש.                   |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  העמוד `pages/giftcards.php` טוען (ב-PHP) את הרשימה מתוך DB (`giftCards`), ומציגה למשתמש. במידה שיש אפשרות לבחור "siteID", נעשית שאילתת DB סלקטיבית בהתאם ל-siteID שנבחר.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                                        | מיקום בקוד                 | מבנה ותוכן הקוד                                                                 |
|----------|-------------------------------------------------------------------------------------------------------------------------------------|----------------------------|----------------------------------------------------------------------------------|
| 1        | קריאה ל-DB עבור `giftCards` לפי `siteID`.                                                                                           | `pages/giftcards.php`      | `$sql = "SELECT * FROM giftCards WHERE siteID=…"…`                               |
| 2        | הצגה דינמית של הרשימה בלולאה (יצירת div עבור כל giftCard).                                                                          | `pages/giftcards.php`      | `foreach($giftCards as $giftCard) { … }`                                         |
| 3        | טיפול בבחירת מתחם (site): אם נבחר siteID שונה, העמוד מבצע רענון או טוען רשימה חדשה ב-JS.                                            | `assets/js/giftcards.js`   | `$("#sid").on("change",function(){ … AJAX / … })`                                |

---

## תרחיש 2: הוספת גיפטקארד חדש

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש לוחץ על "הוסף חדש", ממלא טופס (כותרת, סכום, תיאור, תוקף וכו’), ולוחץ על "שמור".

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                       | תוצאה מצופה                                                         |
|----------|---------------------------------------|----------------------------------------------------------------------|
| 1        | לחיצה על כפתור "הוסף חדש"             | מוצג חלון פופ־אפ עם טופס ריק לעריכת נתוני גיפטקארד.                  |
| 2        | מילוי שדות (כותרת, סכום, תיאור וכו’) | המשתמש מזין את כל הפרטים המבוקשים.                                  |
| 3        | לחיצה על כפתור "שמור"                | הטופס נסגר, מופיעה הודעת הצלחה, והגיפטקארד החדש נוסף לרשימה.        |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  בעת לחיצה על "הוסף חדש" נקרא `loadGiftCardData(0)` (JavaScript) שמאפיין מצב "גיפטקארד חדש". לאחר מילוי הפרטים ושמירה, מתבצע AJAX לקובץ `ajax_load_giftcard.php` עם `act=1`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                               | מיקום בקוד                   | מבנה ותוכן הקוד                                                                                                                                          |
|----------|----------------------------------------------------------------------------------------------------------------------------|------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| 1        | אירוע `onclick` על הכפתור "הוסף חדש" קורא לפונקציה `loadGiftCardData(0)`.                                                 | `pages/giftcards.php`        | `<div class="add-new" onclick="loadGiftCardData(0)">הוסף חדש</div>`                                                                                      |
| 2        | ב-JS, אם `gid == 0`, מנקים את הטופס (`$("#giftCardForm")[0].reset()` וכו’).                                               | `assets/js/giftcards.js`     | `if(gid != 0) { … } else { … reset form … $('.giftpop').fadeIn('fast'); }`                                                                                |
| 3        | בעת לחיצה על "שמור" בטופס, פונקציית `submitGiftCardForm` אוספת את הנתונים, ושולחת `act=1` ל-`ajax_load_giftcard.php`.     | `assets/js/giftcards.js`     | `url: baseUrl + "ajax_load_giftcard.php?act=1" … method: "POST" … formData …`                                                                            |
| 4        | ב-PHP (שרת), `case 1` יוצרת רשומה חדשה ב-DB בטבלת `giftCards` עם המידע שנשלח.                                             | `ajax_load_giftcard.php`     | `udb::insert("giftCards",$cp);`                                                                                                                           |
| 5        | מחזיר תגובת JSON `success=true`. JavaScript מציג הודעה ומרענן את העמוד / הרשימה.                                          | `ajax_load_giftcard.php`     | `echo json_encode($results, … )`  +  `swal.fire({type:"success",…}).then(()=> window.location.reload());`                                                |

---

## תרחיש 3: עריכת גיפטקארד קיים

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש בוחר גיפטקארד מהרשימה, לוחץ על אייקון עריכה, מופיע חלון פופ־אפ עם פרטי הגיפטקארד, ולאחר עריכה ושמירה – השינויים נשמרים.

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                           | תוצאה מצופה                                                           |
|----------|-------------------------------------------|----------------------------------------------------------------------|
| 1        | לחיצה על אייקון "עריכה" באחד הפריטים       | נפתח פופ־אפ עם הטופס הממולא בערכי הגיפטקארד הנוכחי.                   |
| 2        | שינוי אחד או יותר מהשדות (כותרת, סכום וכו’) | המשתמש מעדכן את הערכים לפי הצורך.                                    |
| 3        | לחיצה על כפתור "שמור"                    | חלון נסגר, מופיעה הודעת הצלחה, והרשימה מוצגת עם הנתונים המעודכנים.    |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  בלחיצה על אייקון העריכה, נקרא `loadGiftCardData(gid)`. בפונקציה זו מתבצע AJAX ל-`ajax_load_giftcard.php?act=0` לשליפת נתוני הגיפטקארד. לאחר שינויים בטופס, שליחה מתבצעת שוב ל-`ajax_load_giftcard.php` עם `act=1`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                                         | מיקום בקוד                | מבנה ותוכן הקוד                                                                                                                 |
|----------|------------------------------------------------------------------------------------------------------------------------------------|---------------------------|----------------------------------------------------------------------------------------------------------------------------------|
| 1        | אירוע `onclick` על כפתור העריכה מעביר את ה-id ל-`loadGiftCardData(id)`.                                                              | `pages/giftcards.php`     | `<div class="edit" data-id="<?=$giftCard['giftCardID']?>">…</div>`                                                              |
| 2        | `loadGiftCardData` שולח AJAX ל-`ajax_load_giftcard.php?id=GID&act=0`, מקבל JSON עם הנתונים הקיימים.                                 | `assets/js/giftcards.js`  | `$.get("ajax_load_giftcard.php?id=" + gid, … ) { … fill form fields … }`                                                        |
| 3        | המשתמש לוחץ "שמור" => `submitGiftCardForm` => AJAX POST עם `act=1`.                                                                  | `assets/js/giftcards.js`  | `url: baseUrl + "ajax_load_giftcard.php?act=1", data: formData …`                                                              |
| 4        | `case 1` ב-PHP מעדכן `giftCards` עם היכן `giftCardID=$giftCardID`.                                                                  | `ajax_load_giftcard.php`  | `udb::update("giftCards",$cp," giftCardID=".$giftCardID);`                                                                       |
| 5        | התגובה JSON כוללת `success=true`, נעשה `swal.fire()` והעמוד נטען/מתעדכן.                                                            | `assets/js/giftcards.js`  | `swal.fire({type:"success",…}).then(()=> window.location.reload());`                                                            |

---

## תרחיש 4: מחיקה (Delete) של גיפטקארד

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש לוחץ על אייקון מחיקה ליד פריט קיים, ומאשר את המחיקה. הפריט נעלם מהרשימה (מחיקה לוגית).

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                                   | תוצאה מצופה                                     |
|----------|---------------------------------------------------|-------------------------------------------------|
| 1        | לחיצה על כפתור "מחיקה" (אייקון סל)                | מופיע חלון אישור (Confirm) "האם אתם בטוחים?".   |
| 2        | לחיצה על "כן, מחק!"                               | הגיפטקארד מוסר מהרשימה (מופיעה הודעת "נמחק").   |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  ב-JavaScript קוראים ל-`delete_gift(giftid)`, אשר מבצע AJAX עם `act=3` ל-`ajax_load_giftcard.php`. שם נקבע `deleted=1`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                       | מיקום בקוד                | מבנה ותוכן הקוד                                                                                                                  |
|----------|--------------------------------------------------------------------------------------------------------------------|---------------------------|-------------------------------------------------------------------------------------------------------------------------------|
| 1        | קריאה לפונקציה `delete_gift(giftid)` בשילוב הודעת `Swal.fire({...})` לאישור.                                      | `assets/js/giftcards.js`  | `$.get("ajax_load_giftcard.php?act=3&id=" + giftid, function(resp){ … remove item … })`                                       |
| 2        | `case 3` ב-`ajax_load_giftcard.php` מעדכן את השדה `deleted=1`.                                                    | `ajax_load_giftcard.php`  | `udb::query("update `giftCards` set deleted=1 where giftCardID=".$id);`                                                      |
| 3        | במענה מצליח, הקוד ב-JS מסיר את האלמנט מה-DOM (`$("#giftcard" + giftid).remove();`).                                | `assets/js/giftcards.js`  | `$("#giftcard" + giftid).remove(); Swal.fire({title:"נמחק", type:"info"});`                                                 |

---

## תרחיש 5: הפעלה/ביטול הפעלה (Active/Deactivate)

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש רואה כפתור Toggle (switch) של "פעיל/לא פעיל" ברשימת הגיפטקארדים, ומסמן/מבטל סימון.

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                      | תוצאה מצופה                                             |
|----------|--------------------------------------|--------------------------------------------------------|
| 1        | לחיצה על הסוויצ’ "פעיל/לא פעיל"       | מצב הגיפטקארד מתהפך (unchecked/checked).               |
| 2        | התצוגה ברשימה מראה שהמוצר כעת פעיל/לא פעיל | הגיפטקארד מציג טקסט/סטטוס "פעיל" או מוסר אותו.        |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  העדכון מתבצע דרך `activeDeActive(id)` שקורא ל-`ajax_load_giftcard.php?act=2&id=...`. הצד שרת מבצע `active = NOT active`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                    | מיקום בקוד                | מבנה ותוכן הקוד                                            |
|----------|-------------------------------------------------------------------------------------------------|---------------------------|-----------------------------------------------------------|
| 1        | `onchange="activeDeActive(…)";` מפעיל AJAX GET עם `act=2`.                                     | `pages/giftcards.php`     | `<input type="checkbox" data-id="<?=$giftCard['giftCardID']?>" onchange="activeDeActive(…)" …>` |
| 2        | `case 2` קובע `active = NOT active where giftCardID=…`.                                       | `ajax_load_giftcard.php`  | `udb::query("update `giftCards` set active= NOT active…")`|
| 3        | JSON חוזר עם `success=true`, אין טעינת עמוד מחדש אלא רק שינוי גרפי (סוויצ’).                    | `assets/js/giftcards.js`  |  *הקוד לא מציג toast מיוחד, רק מפעיל פעולה "Done"*         |

---

## תרחיש 6: הגדרות תצוגת עמוד (Global Settings)

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** המשתמש לוחץ על "הגדרות תצוגת עמוד", ממלא טופס הגדרות (כותרת, לוגו, תמונת רקע, תיאור וכו’), ולוחץ "שמור".

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                              | תוצאה מצופה                                      |
|----------|----------------------------------------------|-------------------------------------------------|
| 1        | לחיצה על "הגדרות תצוגת עמוד"                 | מופיע חלון פופ־אפ "global_editPop".             |
| 2        | הזנת / עדכון השדות (כותרת, תמונות, תיאור...) | ערכים חדשים מוצגים בתצוגה המקדימה (אם יש).      |
| 3        | לחיצה על "שמור"                              | הפופ־אפ נסגר, מופיעה הודעת הצלחה, העמוד נטען מחדש עם ההגדרות החדשות. |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  בלחיצה על "הגדרות תצוגת עמוד" נקרא `loadGeneralForm()`, שולח AJAX ל-`ajax_giftcards_setting.php?act=1` לקבלת נתוני ההגדרות. לאחר מילוי טופס ושמירה, נשלח POST אל אותו קובץ עם `act=0`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                          | מיקום בקוד                      | מבנה ותוכן הקוד                                                                                                      |
|----------|---------------------------------------------------------------------------------------|---------------------------------|-----------------------------------------------------------------------------------------------------------------------|
| 1        | כפתור "הגדרות תצוגת עמוד" מפעיל `loadGeneralForm()`                                  | `pages/giftcards.php`           | `<div class="page-options" onclick="loadGeneralForm()">הגדרות תצוגת עמוד</div>`                                       |
| 2        | `loadGeneralForm()` שולח GET `?act=1&siteID2=…` ל-`ajax_giftcards_setting.php`.       | `assets/js/giftcards.js`        | `$.get(baseUrl + "ajax_giftcards_setting.php?act=1&siteID2="+ siteID, … )`                                           |
| 3        | הטופס מוצג, המשתמש ממלא ומשגר => `submitGeneralForm(e,this);` שעושה POST `act=0`.      | `assets/js/giftcards.js`        | `url: baseUrl + "ajax_giftcards_setting.php?act=0", data: formData, … method: POST …`                                |
| 4        | `case 0` מעדכן או מוסיף שורה ב-`giftCardsSetting` עם הקלט (כותרת, לוגו, תמונת רקע וכו’). | `ajax_giftcards_setting.php`    | `udb::update("giftCardsSetting",$cp," giftCardsSettingID=…")` או `udb::insert(…)` בהתאם לקיום מזהה קודם.            |
| 5        | מחזיר JSON. JS מציג הודעת "נשמר" ומבצע `window.location.reload()`.                    | `ajax_giftcards_setting.php` + `assets/js/giftcards.js` | `swal.fire({type:"success",…}).then(()=> {window.location.reload();})`                            |

---

## תרחיש 7: צפייה בפרטי גיפטקארד ומימוש (Use / Redeem)

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** משתמש רוצה לבדוק גיפטקארד מסוים (לדוגמה, בעמדת קופה או ניהול) ולהחיל מימוש חלקי/מלא.  
  דרך הלוגים ב"פופ־אפ" ניתן גם לראות את ההיסטוריה של המימושים.

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                                             | תוצאה מצופה                                                                                |
|----------|-------------------------------------------------------------|-------------------------------------------------------------------------------------------|
| 1        | המשתמש מקיש מספר שובר/גיפטקארד (12 תווים) או בוחר מהרשימה   | נפתח פופ־אפ פרטי הגיפטקארד (`ajax_pop_gift.php`).                                        |
| 2        | נחשף למידע על השובר (תוקף, יתרה, ברכה, מימושים קודמים וכו’). | המשתמש מחליט לממש סכום מסוים או את כל היתרה, ע"י כפתור "מימוש חלקי" או "מימוש מלא".       |
| 3        | המשתמש מזין את הסכום (במימוש חלקי) ולוחץ "אישור"            | אם הסכום חוקי (לא חורג מיתרה) – מופיעה הודעת הצלחה ומעודכן המימוש.                        |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  הפופ־אפ נטען באמצעות `ajax_pop_gift.php`, שם נשלפים נתוני הרכישה (`gifts_purchases`) והטבלה של `giftCardsUsage`.  
  כשלוחצים על "מימוש", נשלח `act=use` דרך `ajax_giftcards.php`. אם סכום המימוש תקין, מתבצעת שמירה בטבלת `giftCardsUsage`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                                         | מיקום בקוד                | מבנה ותוכן הקוד                                                                                         |
|----------|------------------------------------------------------------------------------------------------------------------------------------|---------------------------|----------------------------------------------------------------------------------------------------------|
| 1        | קריאה לאתר `ajax_pop_gift.php?gID=…` או `oid=…` מחזירה HTML מוכן של הפרטים.                                                        | `assets/js/giftcards.js`  | `showPOP(gid,oid){ $.get("ajax_pop_gift.php?gID="+gid+"&oid="+oid, function(res){ … }) }`              |
| 2        | הפונקציה `mimushPop(mtype)` מציגה Modal עבור מימוש, מציבה ערך ברירת מחדל (סכום מלא/חלקי).                                          | `assets/js/giftcards.js`  | `$("#sumToUse").val(leftOver); … $(".giftcard.gift-pop.mimush").fadeIn('fast');`                        |
| 3        | בעת אישור, `$("#mimushShovar").submit()` שולח POST עם `act=use` ל-`ajax_giftcards.php`.                                            | `assets/js/giftcards.js`  | `var formData={act:"use",…}; url:"ajax_giftcards.php", method:"POST"…`                                   |
| 4        | `case 'use'` ב-`ajax_giftcards.php` בודק יתרה שכבר נוצלה, משווה לסכום החדש, ומוסיף רשומה ל-`giftCardsUsage`.                         | `ajax_giftcards.php`      | `udb::insert('giftCardsUsage', … )`                                                                     |
| 5        | מחזיר JSON `success=true`, JS מציג סיום מוצלח, והיתרה מתעדכנת.                                                                      | `ajax_giftcards.php` + JS | `swal.fire({type:"success",title:"הפעולה בוצעה בהצלחה"}).then(()=> window.location.reload());`          |

---

## תרחיש 8: זיכוי (Refund)

### א. תיאור משתמש הקצה (Front-End Perspective)
- **תיאור כללי:** אם גיפטקארד נקנה במסלול "direct" (חיוב ישיר), ובתנאי שטרם מומש, ניתן לזכות את העסקה (Refund).

**טבלת צעדי התרחיש מצד המשתמש:**

| מספר צעד | פעולה מצד המשתמש                                     | תוצאה מצופה                                                               |
|----------|-----------------------------------------------------|----------------------------------------------------------------------------|
| 1        | פתיחת פרטי גיפטקארד (פופ־אפ), לחיצה על כפתור "זיכוי" | עולה הודעה לאישור זיכוי, ולאחר אישור מופיעה הודעת הצלחה "בוצע זיכוי".       |

### ב. תיאור המימוש בקוד (Back-End Perspective)
- **תיאור כללי:**  
  בקובץ `ajax_giftcards.php`, מתקבל `act=refundDirect`. מתבצעות בדיקות שאין כבר מימושים, מפעילים קריאת API לספק הסליקה (CardCom וכד’), ואם הצליח – מסמנים `refunded=1`.

**טבלת צעדי התרחיש מצד המתכנת:**

| מספר צעד | לוגיקה טכנית                                                                                                | מיקום בקוד             | מבנה ותוכן הקוד                                                                            |
|----------|------------------------------------------------------------------------------------------------------------|------------------------|---------------------------------------------------------------------------------------------|
| 1        | קריאה ל-`ajax_giftcards.php` עם `act=refundDirect` + `pid=…`.                                              | `assets/js/giftcards.js` (או אחר) | `$.post("ajax_giftcards.php", {act:"refundDirect", pid:…}, … )`                            |
| 2        | בדיקה אם כבר מומש סכום בגיפטקארד; אם כן – שגיאה.                                                            | `ajax_giftcards.php`   | `if($used>0) throw new Exception("Cannot refund …");`                                       |
| 3        | קריאה ל-API זיכוי (CardComGeneral->payRefund).                                                             | `ajax_giftcards.php`   | `$res = $terminal->payRefund(…)`                                                            |
| 4        | עדכון `gifts_purchases` ושמירת סימון `refunded=…`.                                                          | `ajax_giftcards.php`   | `udb::update('gifts_purchases',['refunded'=>$res['_transID']]," transID=…")`               |
| 5        | מחזיר JSON `success=true`, וה-JavaScript מציג הודעת "בוצע בהצלחה".                                         | `ajax_giftcards.php`   | `echo json_encode($result);`                                                                |

---

# 6. מסמך שימוש (מדריך למשתמש)

> להלן מדריך קצר המיועד למשתמשי המערכת (כגון מנהלי האתר) בשפה ידידותית:

1. **כניסה לרשימת גיפטקארדים**
    - היכנס/י לכתובת הניהול המתאימה (`giftcards.php`).
    - בעמוד הראשי, תוצג בפניך רשימת הגיפטקארדים הקיימים. אם יש מתחמים שונים, בחרי מהרשימה בראש העמוד.

2. **הוספת גיפטקארד חדש**
    - לחצ/י על הכפתור "הוסף חדש".
    - מלא/י את הפרטים (כותרת, סכום, תיאור, תוקף בחודשים וכו’).
    - לחצ/י "שמור" – הגיפטקארד יופיע ברשימה מיד לאחר מכן.

3. **עריכת גיפטקארד קיים**
    - לאיתור גיפטקארד מסוים, מצא/י אותו ברשימה.
    - לחצ/י על אייקון "עריכה" (עיפרון).
    - עדכנ/י את השדות הרצויים ולחצ/י "שמור".

4. **מחיקת גיפטקארד**
    - לחצ/י על אייקון המחיקה (סל אשפה) בצמוד לגיפטקארד.
    - אשר/י את הפעולה בחלון הקופץ. הגיפטקארד יוסר מהרשימה (אך קיים ב-DB כ"deleted").

5. **הפעלת/ביטול הפעלת גיפטקארד**
    - במתג (Switch) "פעיל/לא פעיל" ניתן להדליק/לכבות את הגיפטקארד.
    - אם כבוי, המשתמשים לא יראו אותו בצד החיצוני (בהתאם ללוגיקה באתר).

6. **הגדרות תצוגת עמוד**
    - לחצ/י על הכפתור "הגדרות תצוגת עמוד".
    - ייפתח חלון שבו תוכלי לשנות כותרת, להעלות לוגו או רקע, להזין תיאור עסק והערות.
    - לחצ/י "שמור" לרענון והצגת השינויים.

7. **צפייה בפרטי שובר ומימוש**
    - היכנס/י לפופ־אפ המידע (לפי הצורך) או הקישי מספר שובר מלא (12 תווים).
    - תוצג יתרת הגיפטקארד, היסטוריית המימושים, תאריך רכישה ותוקף.
    - למימוש סכום מסוים, לחצ/י "למימוש חלקי" והקלד/י את הסכום המבוקש; למימוש מלא – לחצ/י "למימוש מלא".
    - אשר/י, ותקבלי הודעה על הצלחה. היתרה תעודכן.

8. **זיכוי (Refund)**
    - במידה והגיפטקארד נקנה במסלול "direct" ולא בוצעו מימושים, לחצ/י על "זיכוי" בפופ־אפ הפרטים.
    - אשר/י את הפעולה. אם הכל תקין, תופיע הודעת הצלחה "בוצע זיכוי" והסטטוס יתעדכן.

---

# 7. סיכום
הקוד מספק פתרון מלא לניהול גיפטקארדים: החל מרשימת כרטיסים, הוספה ועריכה, דרך הגדרות תצוגה כוללות ועד למימוש, החזרים וזיכויים.  
במערכת קיימת הפרדה ברורה בין החלק הוויזואלי (Front-End) לבין פעולות ה-Back-End המטפלות בבסיס הנתונים ובאינטראקציות עם ספקי סליקה / הפקת חשבוניות.

ניתן להרחיב ולשפר היבטים שונים לפי הצורך, אך על בסיס המידע שמופיע בקבצים שנסרקו – זהו המבנה המרכזי והתרחישים הנתמכים.
