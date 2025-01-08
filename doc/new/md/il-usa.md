# מדריך לשינויים בקוד עקב סוג השרת (Israel / USA)

מסמך זה מסביר כיצד המערכת מתנהגת באופן שונה בהתאם לשרת שבו היא רצה (ישראל או ארה"ב), וכן כיצד זה משפיע באופן ספציפי על הקובץ **treatments.php**.

---

# 1. מהות הרעיון

ברוב המערכות, ייתכן מצב שבו נרצה להריץ קטעי קוד שונים או לבצע פעולות שונות כאשר אנחנו בשרת ישראלי לעומת שרת אמריקאי. לדוגמה, לחבר ל‑API שונה, לשלוח מיילים לכתובת אחרת, או במקרה שלנו — לעדכן את SpaPlus רק אם אנחנו נמצאים בשרת ישראל.

לשם כך, אנו משתמשים בקובץ **config_system.php** ובתוכו משתנה/קבוע המגדיר את סוג השרת. לאחר מכן, בכל קובץ בקוד שבו נרצה לבצע לוגיקה שונה, נבצע `require_once` לקובץ הקונפיגורציה ונעשה בדיקת `if (SERVER_TYPE === 'israel') {...} else {...}`.

---

# 2. מבנה קובץ הקונפיגורציה (config_system.php)

הקובץ **config_system.php** אחראי לקבוע אם השרת הוא ישראלי או אמריקאי. לדוגמה:

```php
<?php
// config_system.php

// אפשרות 1: הגדרה ידנית
// define('SERVER_TYPE', 'israel');

// אפשרות 2: זיהוי לפי דומיין
if (strpos($_SERVER['HTTP_HOST'], 'bizonline.co.il') !== false) {
    $server_type = 'israel';
} else {
    $server_type = 'usa';
}

// קיבוע הערך כקבוע לשימוש בשאר חלקי המערכת
define('SERVER_TYPE', $server_type);
```

1. אם `$_SERVER['HTTP_HOST']` הוא `bizonline.co.il`, נגדיר את `SERVER_TYPE` כ־`'israel'`.  
2. בכל מקרה אחר, נגדיר `SERVER_TYPE` כ־`'usa'`.  
3. ניתן להחליף לוגיקה או כתובת דומיין בהתאם לצורך.

---

# 3. שימוש בקובץ הקונפיגורציה לצורך החלטות בקוד

ברגע שיש לנו את **config_system.php**, בכל קובץ שבו אנו צריכים לבצע פעולות שונות לפי סוג השרת, נשתמש כך:

```php
require_once __DIR__ . '/path/to/config_system.php';

// בדיקה אם זה שרת ישראל
if (SERVER_TYPE === 'israel') {
    // הרצת קוד / פונקציות שמיועדות לשרת בישראל
} else {
    // קוד שמיועד לשרת בארה"ב או ברירת מחדל
}
```

---

# 4. איך זה מתבטא בקובץ treatments.php

# הסבר כללי
בקובץ **treatments.php**, ברגע שהמשתמש שומר מחירי טיפולים (POST), המערכת שולחת את המחירים המעודכנים ל‑SpaPlus באמצעות האובייקט `SpaPlusRelay`. אבל **רק** אם אנחנו בשרת ישראל. אם זה שרת ארה"ב, נחליט להריץ פונקציה ריקה או מהלך שונה.

# דוגמת קוד רלוונטית (מקטע מ‑treatments.php)

```php
// 1) חיבור לקובץ הקונפיגורציה
require_once __DIR__ . '/../../config_system.php';

if ('POST' == $_SERVER['REQUEST_METHOD'] && $canSave) {

    // ... (עדכונים שונים במסד נתונים וכו') ...

    // 2) קטע התנאי – האם לעדכן את SpaPlus או לא
    if (SERVER_TYPE === 'israel') {
        // שרת ישראל: נמשיך לשלוח נתונים לספא פלוס
        try {
            $relay = new SpaPlusRelay($sid);
            $relay->sendPrices();
        } catch (Exception $e) {
            mail('alchemist.tech@gmail.com',
                 'Failed to send price update to SpaPlus',
                 'Failed to send price update to SpaPlus (site ' . $sid . '): ' . $e->getMessage());
        }
    } else {
        // שרת ארה"ב: אין צורך לעדכן את ספא פלוס, או נעשה פעולה אחרת/פונקציה ריקה
        function sendPricesPlaceholder() {
            // פונקציה ריקה או לוגיקה אלטרנטיבית
        }
        sendPricesPlaceholder();
    }

    echo '<script> window.location.href = "?page=treatments"; </script>';
    return;
}
```

# מה השתנה פה?
1. הוספנו את `require_once` של **config_system.php**.  
2. בדיוק **לפני** שליחת המחירים ל‑SpaPlus, הוספנו תנאי:  
   - `if (SERVER_TYPE === 'israel')` => קוראים לפונקציה `sendPrices()`.  
   - `else` => לא עושים דבר או מפעילים פונקציה אחרת.

כתוצאה מכך, המערכת תעדכן מחירים ב‑SpaPlus רק כשהיא מזהה שהשרת הוא ישראלי. בשרת ארה"ב (או כל שרת אחר), היא לא תנסה לדבר עם SpaPlus.

---

# 5. סיכום

- כדי להחליט על פעולות שונות לפי מיקום השרת, הגדרנו **config_system.php** שמספק קבוע (`SERVER_TYPE`).  
- בכל קובץ שרוצה לוגיקה שונה (כמו **treatments.php**), טוענים את הקובץ ועושים `if` פשוט.  
- כך נמנע קוד כפול ולא צריכים לשמור 2 גרסאות שונות של כל קובץ.

**טיפ**: אם יש עוד פעולות שרלוונטיות רק לשרת ישראל (כמו שליחת חשבונית, הודעת SMS, וכד'), אפשר לעטוף אותן באותו `if (SERVER_TYPE === 'israel')`.

---

> **עדכון לפי צרכים נוספים**  
> במידה ונרצה עוד שרתים, אפשר להרחיב את הלוגיקה בקובץ הקונפיגורציה (לדוגמה, `usa`, `eu`, `dev`, וכו'), ובהתאם לעדכן את התנאי ב‑treatments.php או בקבצים אחרים.
```