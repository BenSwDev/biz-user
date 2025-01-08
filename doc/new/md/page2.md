# 1. הקדמה
המערכת בנויה באופן מודולרי כך שכל עמוד מופעל בהתאם לסוג המשתמש, הרשאותיו והפעולות שנבחרו. התהליך מתחיל בכניסה ל-index.php, שמשמש כנקודת הכניסה הראשית ומנהל את ניתוב העמודים בעזרת פרמטרים שמועברים ב-URL. הקובץ auth.php מטפל בבדיקת ההרשאות והמשתמשים המורשים, ומגדיר את סביבת העבודה של המשתמש הנוכחי.

לאחר מכן, על פי הגדרות ותנאים ייחודיים, המערכת טוענת תפריטים ראשיים ותפריטי משנה (menu.php ו-submenu.php), כמו גם תפריטים מותאמים למשתמשים מסוגים מסוימים (menu_member.php).

כדי לספק פונקציונליות גלובלית ולנהל פעולות נפוצות, נעשה שימוש בקובץ functions.php. השילוב של רכיבים אלו מבטיח חוויית משתמש זורמת ומדויקת, המותאמת לצרכים שונים כמו יומן תפוסה, דוחות, או תהליכי עבודה ייעודיים.

הקבצים שעליהם מבוססת ההקדמה:
index.php
auth.php
functions.php
partial/menu.php
partial/submenu.php
partial/menu_member.php

בפרויקט קיימת מערכת לבדיקה וסליקה של כרטיסי אשראי. מנגנון זה בנוי ממספר קבצים:
- **ccard.php**: דף שמציג למשתמש בדיקת כרטיס דרך אייפריים, תוך שימוש בקלאסים חיצוניים לניהול טרמינל וסליקה (Terminal / YaadPay).
- **error_frame.php**: עמוד שגיאה המוצג במקרה שמשהו משתבש במהלך הבדיקה או שיש חריגה.
- **Terminal.php**: קובץ המנהל לוגיקה של "טרמינל" ומוודא סוג ספק התשלום (לדוגמה, YaadPay, MAX, CardCom).
- **YaadPay.php**: קובץ המכיל מחלקה לביצוע פעולות שונות מול ספק הסליקה YaadPay (כגון בדיקות, סליקה, ביטול עסקה, החזר כספי ועוד).

הרציונל הכולל הוא לאפשר למשתמש לבצע בדיקת תקינות של כרטיס אשראי (חיוב סמלי שיוחזר), או לבצע חיובים ופעולות נוספות, וכל זאת באופן מאובטח. במקרה של כשל טכני או בעיה בכרטיס – עוברים לעמוד שגיאה מתאים.


# 2. הקבצים שנבדקו
הקבצים שבהם קיים בפועל תוכן ונבחנו:

1. **user/pages/ccard.php**
   - מציג למשתמש דף בדיקת כרטיס אשראי באייפריים.
   - בודק האם למשתמש יש טרמינל פעיל דרך הקוד:
     ```php
     $termType = Terminal::hasTerminal($_CURRENT_USER->active_site());
     $client   = ($termType && strcasecmp($termType, 'max') && $_CURRENT_USER->single_site) 
                   ? Terminal::bySite($_CURRENT_USER->active_site()) 
                   : YaadPay::defaultTerminal();
     $link     = $client->initFrameCardTest();
     ```
   - במקרה של חריגה (Exception), נבנה קישור ל-**error_frame.php** ומועבר בפרמטר GET הודעת שגיאה.

2. **error_frame.php**
   - עמוד שגיאה בסיסי שמציג הודעת כישלון (מתקבלת מ-`$_GET['error']`) באופן בולט.
   - כולל קוד jQuery המגלגל את חלון-ההורה לראש הדף (`scrollTop(0)`).

3. **public_html/cms/classes/Terminal.php**
   - מוודא אם לאתר (siteID) מוגדר טרמינל פעיל ומאיזה סוג (max, yaad, cardcom).
   - מחזיר אובייקט טרמינל מתאים (`YaadPay::getTerminal($siteID)` וכו'), או זורק חריגה אם לא נמצא טרמינל תקין.
   - אחראי גם לבדיקות שונות (למשל `hasCardCheck`) ואחסון מידע בקאש סטטי (`self::$_term_cache`).

4. **public_html/api/classes/YaadPay.php**
   - מממש לוגיקת סליקה מקיפה מול ספק הסליקה YaadPay:
      - יצירת טרנזקציות (`trans_create`, `trans_update`, `trans_record_create` וכו').
      - חיוב, ביטול, זיכוי, בקשת טוקן, בדיקות באמצעות iframe וכדומה.
   - כולל פונקציית `initFrameCardTest()` המוקראת בקובץ `ccard.php`, ליצירת בדיקה של כרטיס אשראי בסכום נמוך (1 ש"ח), שמוחזר ללקוח.
   - בשגיאות, נזרקת `Exception`; הודעות השגיאה מנוהלות עם מפה פנימית של קודי בעיה (`YaadError::$list`).


# 3. הקבצים שלא נמצאו
אין ברשימה שום קובץ שסומן כ"לא נמצא", ולכן סעיף זה ריק.


# 4. הקבצים שלא נבדקו
לא זוהו קבצים נוספים שנדרש לבחון, מעבר לארבעת הקבצים שהוזכרו ומצויים בפועל.
> (*בהתאם להנחיות, יש להתעלם מאזכורים ל־index.php או לקבצים כמו `auth.php` וכד’, גם אם קיימים במערכת.*)


# 5. תרחישים כוללים בעמוד

### מטרה
לזהות תרחישי שימוש (Use Flow) כפי שעולים מהקבצים. בפועל ניתן לתאר מספר תרחישים עיקריים:

1. **תרחיש שימוש 1: בדיקה תקינה של כרטיס אשראי**
2. **תרחיש שימוש 2: שגיאה או כשל בבדיקה**
3. **תרחיש שימוש 3: פעולות סליקה נוספות** (כגון חיוב מלא, ביטול, זיכוי), המבוססות על מחלקת YaadPay (אם כי אין לכך דוגמה ממשית ב-ccard.php, אך מופיע ב־Terminal/YaadPay).

#### 5.1 מבנה הניתוח לכל תרחיש

1. **תיאור משתמש הקצה** (Front-End)
2. **תיאור המימוש בקוד** (Back-End)

---

### 5.1.1 תרחיש שימוש 1: בדיקה תקינה של כרטיס אשראי

#### (א) ניתוח משתמש הקצה (Front-End Perspective)

1. **תיאור כללי**
   - המשתמש מגיע לדף בדיקת כרטיס (ccard.php).
   - הוא רואה כותרת שמצהירה שמדובר בבדיקת תקינות כרטיס, לצד אייפריים המאפשר הזנת פרטים.

2. **טבלת צעדים**

| מספר צעד | פעולה מצד המשתמש                 | תוצאה מצופה                                                  |
|----------|----------------------------------|--------------------------------------------------------------|
| 1        | גישה לכתובת `/user/pages/ccard.php` | מוצגת כותרת והסבר קצר, יחד עם אייפריים לבדיקת הכרטיס.         |
| 2        | הכנסת פרטי כרטיס באייפריים       | המשתמש מצפה שאחרי אימות הנתונים, תתקבל הצלחה (כרטיס תקין).    |
| 3        | סיום הבדיקה                      | התהליך מסתיים בהודעת אישור (אישור מוצג באייפריים או מוסתר).    |

---

#### (ב) ניתוח המימוש בקוד (Back-End Perspective)

1. **תיאור כללי**
   - `ccard.php` בודק תחילה אם לאתר יש טרמינל מתאים (ע"י `Terminal::hasTerminal`), אם לא – או שנזרקת חריגה או שנלקח טרמינל ברירת מחדל (`YaadPay::defaultTerminal`).
   - בשלב הבא נקרא `initFrameCardTest()`, המחזיר URL בו מתבצעת הבדיקה בסכום של 1 ש"ח.

2. **טבלת צעדים**

| צעד | לוגיקה טכנית                                                                                                                     | מיקום בקוד               | מבנה ותוכן                                                                                                 |
|-----|----------------------------------------------------------------------------------------------------------------------------------|--------------------------|------------------------------------------------------------------------------------------------------------|
| 1   | קריאה ל-`Terminal::hasTerminal($siteID)` כדי לבדוק אם יש טרמינל פעיל ומאיזה סוג                                                  | `ccard.php`             | `$termType = Terminal::hasTerminal($_CURRENT_USER->active_site());`                                        |
| 2   | בחירת טרמינל: או `Terminal::bySite(...)` או `YaadPay::defaultTerminal()` בהתאם לתנאים (max וכו’)                                 | `ccard.php`             | `$client = ($termType && ...) ? Terminal::bySite(...) : YaadPay::defaultTerminal();`                       |
| 3   | הזנקת בדיקת כרטיס באייפריים: `initFrameCardTest()`, המחזיר `url`                                                                | `ccard.php`             | `$link = $client->initFrameCardTest();`                                                                    |
| 4   | הצגת האייפריים למשתמש עם ה-URL שנוצר                                                                                             | `ccard.php` (HTML)      | `<iframe src="<?=$link['url']?>" class="ccFrame"></iframe>`                                               |
| 5   | בצד YaadPay: נוצרת טרנזקציה מסוג `frame_card_check`, נשלחת בקשה לספק, נשמרת בבסיס הנתונים וכד’ (לפי הפונקציות ב-YaadPay)         | `YaadPay.php`            | `public function initFrameCardTest(...) { ... _send(...) ... }`                                           |

---

### 5.1.2 תרחיש שימוש 2: שגיאה או כשל בבדיקה

#### (א) ניתוח משתמש הקצה (Front-End Perspective)

| מספר צעד | פעולה מצד המשתמש                       | תוצאה מצופה                                                      |
|----------|----------------------------------------|------------------------------------------------------------------|
| 1        | כניסה לדף הבדיקה / ניסיון חיוב שנכשל  | מעבר אוטומטי לעמוד שגיאה (error_frame.php) עם הודעה מתאימה.      |
| 2        | צפייה בהודעת השגיאה                    | המשתמש רואה הודעה אדומה (לפי ה־HTML) המתארת את הבעיה.             |
| 3        | גלילה / חזרה לדף הקודם                | העמוד מוודא שגוללים לראש חלון ההורה.                             |

#### (ב) ניתוח המימוש בקוד (Back-End Perspective)

| צעד | לוגיקה טכנית                                                                                                    | מיקום בקוד    | מבנה ותוכן                                                                                                 |
|-----|-----------------------------------------------------------------------------------------------------------------|---------------|------------------------------------------------------------------------------------------------------------|
| 1   | אם `Terminal::bySite()` או `initFrameCardTest()` זורקים Exception (כשהטרמינל לא מוגדר או לא פעיל וכד’), נתפס חריג | `ccard.php`   | `catch (Exception $e) { $link = ['url'=>'error_frame.php?...']; }`                                         |
| 2   | הפניה לעמוד השגיאה עם פרמטר `error`                                                                             | `ccard.php`   | `$link = ['url' => 'error_frame.php?' . http_build_query(['error'=>$e->getMessage()])];`                   |
| 3   | קליטת הערך `$_GET['error']` והצגתו בעמוד `error_frame.php`                                                      | `error_frame.php` | `<?= (strip_tags($_GET['error']) ?: 'Oops... some error happened !') ?>`                              |
| 4   | jQuery מגלגל את חלון האב לראש הדף                                                                               | `error_frame.php` | `$("html , body",window.parent.document).scrollTop(0);`                                                 |

---

### 5.1.3 תרחיש שימוש 3: פעולות סליקה נוספות (חיוב, ביטול, החזר)
*אין דוגמה מפורשת לכך בקבצי ccard.php או error_frame.php*, אך בקובץ **YaadPay.php** מופיעים:
- **directPay()** – חיוב ישיר (כולל פרמטרים כגון סכום, תיאור, מספר תשלומים וכו’).
- **payCancel()** – ביטול עסקה קיימת.
- **payRefund()** – החזר כספי (זיכוי) ללקוח על סכום מסוים.
- **sendPrintout()** – הפקת חשבונית/קבלה מסוגים שונים (מזומן, צ’ק וכו’).

התרחיש הכללי דומה: המשתמש מזין או מפעיל פעולה, בצד השרת קוראים לאחת הפונקציות ב-YaadPay, נשמרת טרנזקציה בבסיס הנתונים, ואם יש שגיאה – נזרקת חריגה. התגובות עשויות להגיע כקוד שגיאה או הצלחה, לפי המפה `YaadError::$list`.

---

# 6. מסמך שימוש (מדריך למשתמש)
להלן מדריך קצר (בגוף שני):

1. **בדיקת כרטיס אשראי**
   - כנס/י לכתובת `/user/pages/ccard.php`.
   - תראה/י שם כותרת, הסבר קצר ואייפריים. יש למלא את פרטי האשראי בתוך האייפריים (אם מתבקש).
   - לאחר אישור, במידה והבדיקה תקינה, התהליך יסמוך שהכרטיס פעיל ותקבל/י הודעת הצלחה.

2. **במקרה של שגיאה**
   - אם מתרחשת חריגה, תועבר/י אוטומטית לעמוד `error_frame.php` שבו תוצג הודעת השגיאה באדום.
   - נסה/י לבדוק את ההודעה או לפנות לתמיכה הטכנית.

3. **ביטול/זיכוי/חיוב נוסף**
   - הפעולות הללו מנוהלות דרך ממשק מנהל (או פונקציות פנימיות) המשתמשות במחלקות `Terminal` ו־`YaadPay`.
   - במידה ותתבקש/י לבצע ביטול או החזר, הדבר יתבצע מאחורי הקלעים, ואין צורך בדף ייעודי מהצד שלך.

---

# 7. סיכום
במערכת זו נמצאים ארבעה קבצים עיקריים:
- **ccard.php**: דף בדיקת כרטיס באייפריים.
- **error_frame.php**: עמוד שגיאה המוצג במקרה של כשל.
- **Terminal.php**: קובץ הבודק את זמינות וסוג הטרמינל מול האתר.
- **YaadPay.php**: מכיל ממשקי סליקה וחיוב מול YaadPay.

התהליך השלם מאפשר למשתמש לבצע בדיקה מהירה של הכרטיס (חיוב סמלי שיוחזר תוך מספר ימים) או פעולות סליקה נוספות (חיוב, ביטול, זיכוי). במידה ומשהו אינו תקין, משתמשים ב־error_frame.php להצגת הודעת הכישלון.

