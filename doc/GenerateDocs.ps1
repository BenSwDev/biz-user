#requires -Version 3

<#
.SYNOPSIS
  Generate a "DocProject" folder containing:
    - FullDoc.md (entire doc in one file)
    - 01_IntroAndSubtopics\*.md
    - 02_Pages\ (split for pages except GiftCards)
      + 02_Pages\Giftcards\ (the single page "GiftCards" splitted)
    - 03_Various\ (3 placeholder files)
    - 04_Conclusion\Conclusion.md
  all content in UTF-8 with BOM.

.DESCRIPTION
  This script includes the entire documentation "as is" (word for word).
  Also splits it logically into subfolders and .md files.

.NOTES
  Make sure to store this .ps1 as UTF-8 with BOM, to preserve Hebrew properly.
#>

# ========== CONFIG ===========
$RootFolder = "DocProject"

# 1) נכין את הקובץ המלא, $FullDoc, הכולל *הכול* מילה במילה.
#    נשתמש ב-Here-String עם Single-Quotes.
$FullDoc = @'
מסמך משופר ומלא – “הקדמה כוללת לכל עמודי המערכת לפי התפריט”
________________________________________
הקדמה
במערכת קיימים מספר עמודים וקטגוריות מרכזיות, אליהם מנווטים דרך התפריט הראשי (כמופיע בקובץ ‎/partials/menu.php‎). כל עמוד ממלא תפקיד פונקציונלי אחר במערכת, ומאפשר לנצל יכולות שונות (ניהול הזמנות, גיפטקארד, דוחות ועוד). בנוסף, קיימים קבצי קוד בסיסיים האחראים על סביבת העבודה (אימות, הרשאות, פונקציות עזר כלליות וכו’).
דוגמאות לקבצים בסיסיים
•	auth.php – אחראי על אימות גולשים, תחזוקת סשן והרשאות משתמש.
•	functions.php – הגדרות כלליות ו”פונקציות עזר” משותפות.
•	index.php – נקודת כניסה ראשית; טוען הגדרות, שפה, תפריטים, ומפנה לעמודים שונים.
•	partials/menu.php – מגדיר את רשימת העמודים בתפריט הראשי, כולל קריטריונים כמו userType=0/2, calendarOnly ועוד.
במערכת זו, פריטי תפריט מסוימים מוצגים או מוסתרים בתנאים שונים (למשל userType=0 או userType=2) וכן תלוי אם calendar_only=true/false, וכדומה. הסלקטור page=? מפנה בפועל לקובץ PHP מסוים (כפי שהוסבר בהמשך).
________________________________________
עמודי המערכת כפי שהם מופיעים בתפריט (בהתאם ל־partials/menu.php)
להלן רשימת העמודים העיקריים, לצד ההפניה ל־PHP (מתוך הקובץ ?page=XXX). לעיתים onclick או תנאים אחרים גורמים לכך ש־page=? הוא בעצם שם אחד (למשל "orders-to-sign"), אך השורה נפתחת בפועל ב־?page=ccard (בקובץ ccard.php), וכדומה.
1.	מסך ראשי (home)
o	נקרא בתפריט page="home" (למשתמשים userType=0 או userType=2).
o	בפועל מפנה ל–home.php.
o	עמוד “דשבורד” או בית, מציג מידע כללי ונתוני פתיחה.
2.	בדיקת כרטיס אשראי (ccard)
o	מופיע בתפריט כ־page="orders-to-sign" אך עם onclick שמוביל ל–?page=ccard.
o	ממומש ב–ccard.php.
o	מאפשר תהליך בדיקה / חיוב / אימות ידני של כרטיסי אשראי (באתרים מסוימים).
3.	הצטרפות לסליקה דרך המערכת (cc)
o	page="join-cc-service", בפועל מפנה ל–?page=cc.
o	קובץ cc.php.
o	מסך הדרכה / הרשמה לחיבור מסוף סליקה לעסקים שאין להם סליקה פעילה.
4.	פירוט עסקאות (yaadTrans)
o	page="orders-to-sign" (וריאציה אחרת), onclick ?page=yaadTrans.
o	ממומש בקובץ yaadTrans.php.
o	מציג תנועות אשראי / סליקה והיסטוריית עסקאות.
5.	ניהול גיפטקארד (giftcards)
o	בתפריט page="giftcards", onclick ?page=giftcards&gc=1.
o	קובץ giftcards.php.
o	מאפשר להקים ולנהל גיפטקארדים; כולל תהליך רכישה, מימוש, זיכוי וכד’.
6.	ניהול מנויים (subscriptions)
o	page="subscriptions", מפנה ל–?page=subscriptions.
o	קובץ subscriptions.php.
o	עמוד לניהול “מנויים” או תשלומים חוזרים (אם הפונקציונליות זמינה באתר).
7.	יומן תפוסה ספא (absolute_calendar)
o	page="yoman" (userType=0), onclick ?page=absolute_calendar&type=1&viewtype=2.
o	ממומש ב–absolute_calendar.php. (קיימות עוד גרסאות – old/dev/2, אך זו העיקרית בתפריט).
o	עמוד יומן ייעודי לצפייה/עריכה בהזמנות SPA; תצוגה לפי יום/שבוע/חדרים/מטפלים וכו’.
8.	יצירת הזמנה ספא (create_order)
o	page="create_order" (userType=0), onclick מפעיל openNewSpa(this), שבסופו מפנה ל–?page=orders עם פרמטרים.
o	הקובץ שמטפל בפועל: orders.php.
o	עמוד ליצירת הזמנה חדשה עבור SPA (או סביבה דומה).
9.	הגדרות ספא (treatments / treaters)
o	בתפריט מסומן כ–page="treatments", אך onclick ?page=treaters.
o	קובץ treaters.php.
o	עמוד הגדרות מתקדם לניהול מטפלים, סוגי טיפולים, זמני חדרים ועוד — מיועד לאתרי ספא.
10.	דוחות (report_manage)
o	page="treatments" (userType=0) → onclick ?page=report_manage,
ובגרסה אחרת (calendarOnly=false) page="report_manage" → onclick ?page=report_manageZ.
o	לרוב מפנה ל–report_manageZ.php (וגם report_manage.php קיים ונטען בתנאים מסוימים).
o	מרכז דוחות (הכנסות, מכירות, תפוסה ועוד). ייתכן מפוצל לעמודי משנה (stats_treatments, report_extras וכד’).
11.	הצהרות בריאות (healthStatements)
o	page="orders-to-sign", עוד וריאציה, onclick ?page=healthStatements.
o	קובץ healthStatements.php.
o	מוצג לעיתים כאשר יש צורך שהלקוחות יחתמו על הצהרת בריאות.
12.	יומן זמינות והזמנות (calendar_ver2)
o	page="yoman" (userType=0, גרסה אחרת) → onclick ?page=calendar_ver2.
o	קובץ calendar_ver2.php.
o	שונה מעט מ־absolute_calendar; ייתכן שמיועד למסלול הזמנות ללא טיפול / מבנה חדרים.
13.	יצירת הזמנה (create_order) – שוב וריאציה שנייה
o	page="create_order", onclick openNewOrder(this).
o	מפנה ל–?page=orders (קובץ orders.php).
o	יכול לשמש כיצירת הזמנה “רגילה” (לא ספא), תלוי בפרמטר pageType=preorder וכו’.
14.	דוחות (userType=2) – שוב אזכור
o	page="report_manage", onclick ?page=report_manageZ.
o	אותו רעיון: report_manageZ.php.
o	מציג תפריט או דוחות שונים.
15.	לקוחות (clients)
o	page="orders-to-sign", וריאציה, onclick ?page=clients.
o	קובץ clients.php.
o	עמוד למעקב וניהול נתוני הלקוחות, פרטים אישיים, היסטוריית הזמנות וכו’.
16.	חיפוש הזמנות (orders-list)
o	page="orders-list" → onclick ?page=orders&otype=order&orderStatus=active.
o	בפועל orders.php.
o	עמוד להצגת הזמנות, סינון לפי סוג הזמנה, שינוי סטטוסים והיסטוריה.
17.	מחירים (orders-list בגישה אחרת)
o	page="orders-list", onclick ?page=prices.
o	קובץ prices.php.
o	ניהול מחירונים, מוצרים ותוספים.
18.	הגדרות (agreements)
o	page="orders-to-sign", וריאציה, onclick ?page=agreements.
o	קובץ agreements.php.
o	לרוב מרכז מספר הסכמים / הגדרות כלליות.
19.	חוות דעת (reviews)
o	page="orders-to-sign", onclick ?page=reviews.
o	קובץ reviews.php.
o	מאפשר צפייה / ניהול במשובי לקוחות וחוות דעת.
20.	סטטיסטיקות (stats)
o	page="stats", onclick ?page=stats.
o	קובץ stats.php.
o	מציג ניתוחים וגרפים (סך מכירות, פילוחים וכו’).
21.	עדכון פנויים (vilasavail)
o	page="vilasavail", onclick ?page=vilasavail.
o	קובץ vilasavail.php.
o	משמש באתרים המשכירים חדרים/צימרים, לניהול זמינות.
22.	מסך ראשי – שוב, למשתמש userType=2
o	page="home", redirect ל–home.php.
o	אותו עמוד דשבורד, פשוט מוצג גם למשתמשים מסוג 2.
23.	יומן תפוסה ספא (userType=2)
o	page="yoman", onclick ?page=absolute_calendar&type=1&viewtype=2.
o	אותו קובץ absolute_calendar.php.
o	רק הבדל בהרשאות.
24.	יצירת הזמנה ספא (userType=2)
o	page="create_order", onclick openNewSpa(this).
o	בפועל orders.php.
25.	ניהול גיפטקארד (userType=2)
o	page="giftcards", onclick ?page=giftcards-log2&gc=1.
o	הפעם מפנה ל–giftcards-log2.php. (גרסת “לוג” מורחבת)
o	מיועד למשתמש-על, שרואה דיווחים וסיכומים רחבים של רכישות.
26.	הצהרות בריאות (userType=2)
o	page="orders-to-sign", onclick ?page=healthStatements.
o	healthStatements.php.
27.	חיפוש הזמנות (userType=2)
o	page="orders-list", onclick ?page=orders&otype=order&orderStatus=active.
o	orders.php.
28.	חוות דעת (userType=2)
o	page="orders-to-sign", onclick ?page=reviews.
o	reviews.php.
29.	דוחות (userType=2)
o	שוב page="report_manage", onclick ?page=report_manageZ.
o	report_manageZ.php.
________________________________________
סיכום קצר של העמודים בתפריט
כל עמוד בתפריט ממלא תפקיד אחר:
•	חלקם עוסקים במכירות וכספים (כמו ccard, yaadTrans, giftcards)
•	חלקם לניהול לקוחות או הגדרות (למשל clients, agreements)
•	חלקם ספציפיים יותר לסוג אתר (למשל תפוסה ספא absolute_calendar, הצטרפות לסליקה cc),
•	וחלקם דוחות או סטטיסטיקות (report_manageZ, stats).
הערה על הרשאות (userType, calendarOnly וכד’)
ב־partials/menu.php יש לוגיקה נוספת שקובעת האם פריט תפריט יוצג או לא, כגון if($_CURRENT_USER->userType == 0) או if($_CURRENT_USER->calendar_only != true). לכן ייתכן שחלק מהעמודים לא יופיעו כלל למשתמש מסוים.
בנוסף: קיימים עוד קבצי PHP ברשימה הכוללת (שיפורטו בהמשך) שאינם מופיעים בתפריט הראשי, אך קיימים לצרכי פונקציות פנימיות, ניהול, או עמודים משניים.
________________________________________
קבצי קוד בסיסיים
•	auth.php – אחראי על אימות והרשאות.
•	functions.php – פונקציות עזר כלליות.
•	index.php – נקודת כניסה, טוען את התפריט וכו’.
•	partials/menu.php – המקום שבו מגדירים את ה־items בתפריט (כולל page=? והרשאות).
________________________________________
העמודים ברשימה והקשר לתפריט
(דומה למידע לעיל, אך בטבלה קצרה “page => קובץ PHP”):
שם קצר בתפריט	page=?	קובץ PHP
מסך ראשי	home	home.php
בדיקת כרטיס אשראי	(orders-to-sign) => ccard	ccard.php
הצטרפות לסליקה	join-cc-service => cc	cc.php
פירוט עסקאות	(orders-to-sign) => yaadTrans	yaadTrans.php
ניהול גיפטקארד	giftcards => giftcards&gc=1	giftcards.php
ניהול גיפטקארד (userType=2)	giftcards => giftcards-log2 & gc=1	giftcards-log2.php
ניהול מנויים	subscriptions	subscriptions.php
יומן תפוסה ספא	yoman => absolute_calendar	absolute_calendar.php
יצירת הזמנה ספא	create_order => orders	orders.php
הגדרות ספא (מטפלים)	treatments => treaters	treaters.php
דוחות (מספר גרסאות)	report_manage => report_manageZ	report_manageZ.php
הצהרות בריאות	(orders-to-sign) => healthStatements	healthStatements.php
יומן זמינות והזמנות	yoman => calendar_ver2	calendar_ver2.php
יצירת הזמנה (כללי)	create_order => orders	orders.php
לקוחות	(orders-to-sign) => clients	clients.php
חיפוש הזמנות	orders-list => orders	orders.php
מחירים	orders-list => prices	prices.php
הגדרות	(orders-to-sign) => agreements	agreements.php
חוות דעת	(orders-to-sign) => reviews	reviews.php
סטטיסטיקות	stats	stats.php
עדכון פנויים (צימרים)	vilasavail	vilasavail.php
________________________________________
רשימת קבצים שאינם מופיעים בתפריט אך קיימים במערכת
1.	absolute_calendar.old.php
2.	absolute_calendar2.php
3.	absolute_calendar_dev.php
4.	ajax_paytypes.php
5.	alerts.php
6.	bizpop_settings.php
7.	calendar.php
8.	calendar2.php
9.	calendar2_ver2.php
10.	calendar_old.php
11.	calendarnew.php
12.	clients_pages.php
13.	galleryGlobal.php
14.	giftcards-log.php (לעומת giftcards-log2 שמופיע בתפריט למשתמש-על)
15.	healthdec.php
16.	hours.php
17.	monthmaster.php
18.	monthmaster_dev.php
19.	monthtotals.php
20.	monthtotals_dev.php
21.	monthtotals_test.php
22.	O_clients.php
23.	OLD_treaters.php
24.	orderTexts.php
25.	paymentsettings.php
26.	paytypes.php
27.	products.php
28.	report_budget.php
29.	report_extras.php
30.	report_manage_dev.php
31.	report_manage_test.php
32.	report_operative.php
33.	report_period.php
34.	report_treatments.php
35.	sales-banners.php
36.	settings.php
37.	shiftscalendar.php
38.	sites_cupons.php
39.	sources.php
40.	statistics.php
41.	stats_treatments.php
42.	treat_desc.php
43.	treatmentsrooms.php
44.	workers.php
מדובר בעמודים וקבצים שעשויים לשמש למטרות שונות (פיתוח, ניהול פנימי, גרסאות ישנות וכד’). הם אינם מופיעים בפריטי התפריט הראשי הרגיל, אך ייתכן שניגשים אליהם באופנים אחרים או כחלק מתהליכים פנימיים.
________________________________________
המשך: התמקדות בעמוד GiftCards
לאחר ההקדמה הכללית לעמודי המערכת ולמבנה התפריט, נרצה להתחיל בהסבר מפורט על אחד העמודים – עמוד ה־GiftCard. עמוד זה ממוקם בתפריט הראשי תחת הכיתוב “ניהול גיפטקארד” (או מופיע רק אם userType=0 / gc=1), וממומש ב־giftcards.php (או giftcards-log2.php למשתמש-על).
כעת נעבור למסמך ייעודי המסביר את פעילותו של עמוד ה־GiftCard, לרבות התרחישים והתהליכים השונים (להלן “מסמך GiftCards”).
________________________________________
תרחישים כוללים בעמוד GiftCards
1.	כניסה לעמוד וצפייה ברשימה
2.	בחירת אתר (Site) והצגת/הסתרת גיפטקארדים
3.	יצירת גיפטקארד חדש (הוספה)
4.	עריכת גיפטקארד קיים
5.	מחיקת גיפטקארד
6.	הפעלה/השבתה (Activate/Deactivate) של גיפטקארד
7.	ניהול הגדרות גלובליות (Global Gift Cards Settings)
8.	צפייה בעמוד החיצוני (Preview) של הגיפטקארד
9.	שליחת קישור ללקוח (“שליחה ללקוח”)
10.	צפייה בשובר/פרטי רכישה ומימושים (Pop-up) + מימוש / ביטול מימוש / זיכוי
(הרחבות סעיפים 10.1, 10.2, 10.3, 10.4 מפרטות את שלבי המימוש / ביטול / זיכוי)
________________________________________
הטבלאות המפורטות לכל תרחיש
להלן פירוט מקיף של כל תרחיש בפורמט טבלאי, שלב אחר שלב, בצד המשתמש (UI/Front-End) ובצד הקוד (Back-End / JS / AJAX), בהתבסס על הקבצים giftcards.php, giftcards.js, ajax_load_giftcard.php, וכו’:
________________________________________
תרחיש 1: כניסה לעמוד וצפייה ראשונית ברשימת הגיפטקארדים
שלב	צד המשתמש	צד הקוד (מה קורה)
1.1	המשתמש, לאחר שהתחבר, מנווט ל־ /user/pages/giftcards.php.	1. בקבצים הבסיסיים (auth.php, partials/menu.php) כבר בוצע אימות והרשאות. 2. giftcards.php נטען, קורא ל־JS giftcards.js.
1.2	רואה כותרת “ניהול גיפטקארד”.	1. giftcards.php מדפיס <section class="giftcards"> ... </section> ו־<div class="title">ניהול גיפטקארד</div>. 2. מה־PHP מתבצעת שאילתת SELECT (אם siteID קיים) לטבלת giftCards.
1.3	רואה רשימת גיפטקארדים (אם קיימים).	1. לכל <div class="giftcard" ...> מוצגים הפרטים (title, sum וכו’). 2. מוצג אייקון עריכה, מחיקה, כפתור Activate/Deactivate.
________________________________________
תרחיש 2: בחירת אתר (Site) והצגת/הסתרת גיפטקארדים
שלב	צד המשתמש	צד הקוד (מה קורה)
2.1	אם יש אתרים מרובים, המשתמש בוחר אתר מרשימה (Select).	1. giftcards.js (פונקציה bindListElements()) מאזינה ל־ $("#sid").change(...). 2. בעת בחירה, JS מסנן את הגיפטקארדים ב־DOM ומחביא/מציג לפי ה־SiteID.
2.2	רואה שגיפטקארדים של אתרים אחרים “נעלמים”.	1. בהחלפת ה־SiteID, מעדכן $("#guid").val(...). 2. כפתורים כמו “הוסף חדש”, “הגדרות תצוגת עמוד” מופעלים רק אם siteID != 0.
2.3	יכול לעבור בין אתרים שונים ולראות לכל אתר את הגיפטקארדים שלו.	1. לא נעשית קריאת AJAX חדשה בשלב זה, לרוב זה רק סינון בצד לקוח.
________________________________________
תרחיש 3: יצירת גיפטקארד חדש (הוספה)
שלב	צד המשתמש	צד הקוד (מה קורה)
3.1	לוחץ “הוסף חדש” (כפתור <div class="add-new" onclick="loadGiftCardData(0)">).	1. loadGiftCardData(0) (ב־giftcards.js) מופעלת; 0 = אין ID ⇒ יצירת חדש.
3.2	נפתח פופאפ עם טופס ריק לעריכת גיפטקארד.	1. אם gid=0, הפונקציה מאפסת את הטופס, לא שולחת GET. 2. מציגת <div class="giftpop order">....
3.3	ממלא שדות (כותרת, סכום, ימים וכו’), לוחץ “שמור”.	1. $("#giftCardForm").submit() → submitGiftCardForm(e,this). 2. JS בונה FormData עם כל השדות.
3.4	הטופס נשלח ב־AJAX ל־ ajax_load_giftcard.php?act=1.	1. case=1: אם giftCardID==0 ⇒ INSERT לטבלת giftCards. 2. מעלה תמונה אם יש (picpic).
3.5	רואה alert שההוספה בוצעה, העמוד נטען מחדש.	1. if response.success==true ⇒ window.location.reload().
3.6	הגיפטקארד החדש מופיע ברשימה.	1. giftcards.php בטעינה הבאה מציג את הרשומה החדשה.
________________________________________
תרחיש 4: עריכת גיפטקארד קיים
שלב	צד המשתמש	צד הקוד (מה קורה)
4.1	לוחץ על עיפרון (class="edit" data-id="...").	1. $(".giftcard .edit").on("click",...) → loadGiftCardData(id).
4.2	קופץ פופאפ עם טופס והשדות מלאים.	1. loadGiftCardData(id) שולח GET ל־ ajax_load_giftcard.php?act=0&id=<gid>. 2. case=0 ב־PHP: מאתר giftCardID ומחזיר JSON (title, desc...).
4.3	עורך (מחליף שם, סכום...).	1. בשלב זה הנתונים רק ב־DOM, נשמרים ב־DB רק אחרי “שמור”.
4.4	לוחץ “שמור”.	1. submitGiftCardForm() שולח ל־ ajax_load_giftcard.php?act=1 עם giftCardID!=0 → UPDATE giftCards.
4.5	רואה התראה שהעריכה נשמרה.	1. חזרה מהקריאה – response.success==true → window.location.reload().
4.6	הרשימה מתעדכנת בנתונים החדשים.	1. בטעינה מחדש, giftcards.php מציג את הערכים המעודכנים.
________________________________________
תרחיש 5: מחיקת גיפטקארד
שלב	צד המשתמש	צד הקוד (מה קורה)
5.1	לוחץ על אייקון “פח” (Remove).	1. delete_gift(giftCardID).
5.2	Pop-up אישור (Swal “האם אתה בטוח?”).	1. אם מאשרים, שולחים GET ל־ ajax_load_giftcard.php?act=3&id=<giftCardID>.
5.3	ב־Back-End – מפעיל deleted=1.	1. udb::query("update giftCards set deleted=1 where giftCardID=...").
5.4	הגיפטקארד נעלם מהעמוד.	1. בצד הקליינט: $("#giftcard" + giftID).remove().
________________________________________
תרחיש 6: הפעלה/השבתה (Activate/Deactivate)
שלב	צד המשתמש	צד הקוד (מה קורה)
6.1	יש checkbox “פעיל”.	1. בכל <div class="active"> onchange=activeDeActive(giftCardID).
6.2	לוחץ, מחליף בין On ↔ Off.	1. activeDeActive(id) שולח GET ajax_load_giftcard.php?act=2&id=....
6.3	ב־Back-End מעדכן active = not active.	1. case=2: update giftCards set active = not active where giftCardID=....
6.4	רואה ב־UI שהסטטוס השתנה.	1. לא מתרחש רענון עמוד, רק שינוי ב־checkbox.
________________________________________
תרחיש 7: ניהול הגדרות גלובליות (Global Gift Cards Settings)
שלב	צד המשתמש	צד הקוד (מה קורה)
7.1	לוחץ “הגדרות תצוגת עמוד” (<div class="page-options"...).	1. loadGeneralForm() שולח GET ajax_giftcards_setting.php?act=1&siteID2=....
7.2	נפתח Pop-up עם טופס הגדרות (לוגו, רקע...).	1. אם יש נתונים ב־DB, נטענים. אם לא, הטופס ריק.
7.3	מעדכן ערכים (טקסט, Meta, תמונות), לוחץ “שמור”.	1. $("#globaloptionsForm").submit() → submitGeneralForm(e, this) → שולח ?act=0 + קבצים להעלאה.
7.4	הקוד מעדכן/מוסיף רשומה giftCardsSetting.	1. אם settingID קיים → UPDATE, אחרת INSERT.
7.5	החלון נסגר והמסך נטען מחדש.	1. response.success==true → window.location.reload().
________________________________________
תרחיש 8: צפייה בעמוד החיצוני (Preview) של הגיפטקארד
שלב	צד המשתמש	צד הקוד (מה קורה)
8.1	לוחץ על <a class="link send_btn" href="http://www.vouchers.co.il/g.php?guid=..." ...>.	1. לינק חיצוני לפורטל vouchers.co.il, ע"פ sites.guid.
8.2	נפתח חלון חדש עם הכתובת http://www.vouchers.co.il/g.php?guid=XXXX.	1. אין קוד נוסף בפרויקט שלנו (הכל ב־vouchers.co.il).
8.3	רואה את כל הגיפטקארדים כפי שנראים ללקוח.	1. טעינה מבוססת guid בצד vouchers.co.il.
________________________________________
תרחיש 9: שליחת קישור ללקוח (“שליחה ללקוח”)
שלב	צד המשתמש	צד הקוד (מה קורה)
9.1	לוחץ על <div class="plusSend" data-title="שליחת גיפט קארד" ...>.	1. שומר data-msg="http://www.vouchers.co.il/g.php?guid=... ל־share, או למייל.
9.2	לעיתים נפתח חלון להזנת נמען/הודעה, או שליחה בווטסאפ/מייל.	1. בפועל, תלוי מימוש. חלק מהקוד מצביע על whatsappBuild() או ajax_sendEmail וכו’.
9.3	הלקוח מקבל את הקישור ...	1. לא מפורט בקובץ, רק ידוע שזה מפעיל sendEmail/sendSMS.
________________________________________
תרחיש 10: צפייה בשובר/פרטי רכישה ומימושים (Pop-up) + מימוש / ביטול מימוש / זיכוי
(תרחיש ארוך המחולק לתת־תרחישים)
________________________________________
10.1 צפייה בחלונית Pop-up של שובר
שלב	צד המשתמש	צד הקוד
10.1.1	לוחץ על פעולה להצגת השובר, למשל showPOP(pID, 0).	1. קריאה $.get("ajax_pop_gift.php?gID="+pID, ...). 2. מחזירה HTML עם פרטי השובר.
10.1.2	נפתח Pop-up עם פרטי שובר (שם המזמין, סכום, תוקף...).	1. ajax_pop_gift.php עושה JOIN בין gifts_purchases ל־giftCards, בודק שימושים giftCardsUsage. 2. מחזיר HTML מוכנה.
10.1.3	רואה בטבלה היסטוריית מימושים והסכום שנותר.	1. SELECT על giftCardsUsage.
10.1.4	רואה כפתורי “מימוש חלקי” / “מלא” (אם נשאר יתרה).	1. <div class="part" onclick="mimushPop(1)"> ... או <div class="full" onclick="mimushPop(2)"> ...
10.1.5	אם הושלם רכישה direct ללא מימוש קודם, מוצג כפתור “זיכוי” (refund).	1. תנאי isDirect + useageSum=0 ⇒ מציג <div class="full refund">....
________________________________________
10.2 מימוש חלקי או מלא
שלב	צד המשתמש	צד הקוד (מה קורה)
10.2.1	לוחץ על “למימוש חלקי” או “מימוש מלא”.	1. mimushPop(1) / mimushPop(2) פותח <div class="giftcard gift-pop mimush">#mimushShovar.
10.2.2	אם חלקי, מזין <input id="sumToUse">. אם מלא, ייתכן שמילאו את כל היתרה אוטומטית.	1. אין שינוי ב־DB עד ל“אישור מימוש”.
10.2.3	לוחץ “מימוש”.	1. POST ל־ ajax_giftcards.php?act=use&pID=<>&sumToUse=....
10.2.4	ב־PHP: case 'use' → מוסיף רשומה ל־giftCardsUsage, בודק שאין חריגה מהסכום.	1. update/insert giftCardsUsage.
10.2.5	המשתמש רואה הודעה “המימוש הצליח”.	1. JS מציג sweetalert success, ואז סוגר pop-up / טוען מחדש.
________________________________________
10.3 ביטול מימוש
שלב	צד המשתמש	צד הקוד (מה קורה)
10.3.1	רואה בשורת המימוש כפתור “ביטול מימוש” אם cancellable=1.	1. <div class="del-btn" onclick="deleteUsage(useID, ...)">.
10.3.2	לוחץ, מופיע אישור → קורא deleteUsage(useID, pID, code).	1. שולח POST ajax_giftcards.php?act=deleteUsage&pid=...&uid=....
10.3.3	ב־PHP, case 'deleteUsage': מוחק את הרשומה מ־giftCardsUsage	1. udb::query("delete from giftCardsUsage where useID=... and cancellable=1..."). או update (תלוי מימוש לוגי).
10.3.4	השורה מוסרת ברשימת המימושים.	1. JS עידכון הטבלה/רענון ה־Pop-up.
________________________________________
10.4 זיכוי (Refund)
שלב	צד המשתמש	צד הקוד (מה קורה)
10.4.1	לוחץ “זיכוי” (refund) בתוך ה־Pop-up.	1. askGCRefund(pID, this), שולח POST ל־ ajax_giftcards.php?act=refundDirect&pID=....
10.4.2	ב־PHP, case='refundDirect': בודק אם pID=עסקה direct ללא שימוש	1. אם אין שימוש, עושה CardComGeneral->payRefund(...). 2. אם הצליח, מעדכן purchases.refunded=....
10.4.3	אם הצליח, מופיעה הודעת “בוצע זיכוי בהצלחה”.	1. אחרת, מציג שגיאה, אולי מוחק רשומה זמנית.
10.4.4	הגיפטקארד הופך למעשה ללא שווי, כי הלקוח קיבל החזר מלא.	1. המשתמש רואה status='refunded'.
________________________________________
קבצים שנבדקו לצורך GiftCards
1.	/user/pages/giftcards.php – עמוד ראשי להצגה/עריכה של גיפטקארדים.
2.	/user/assets/js/giftcards.js – רוב הפונקציות ל־CRUD, העלאת תמונות, פופ-אפים וכו’.
3.	/user/ajax_load_giftcard.php – CRUD בסיסי (act=0/1/2/3/4).
4.	/user/ajax_giftcards_setting.php – ניהול הגדרות גלובליות (טבלה giftCardsSetting).
5.	/user/ajax_pop_gift.php – מחזיר HTML של שובר (Pop-up) עם נתוני רכישה ומימושים.
6.	/user/ajax_giftcards.php – פעולות מתקדמות: מימוש, ביטול, זיכוי.
7.	ajax_sendEmail.php, ajax_sendSMS.php (כלליים, לא מופיעים בהרחבה כאן).
קבצים חסרים להשלמת חלק מהתרחישים
•	Terminal.php, Transaction.php, CardComGeneral.php (לסליקה/זיכויים).
•	download_invoice_gc.php (חשבוניות).
•	picUpload.php (להעלאת תמונות).
________________________________________
סיכום
•	עברנו על מבנה המערכת והתפריט (partials/menu.php), כיצד page=? מפנה בפועל לקבצי PHP, ובאילו תנאים מוצג כל פריט.
•	יישמנו דוגמה מפורטת בעמוד GiftCards (giftcards.php) עם תרחישים מגוונים – יצירה, עריכה, מימוש ושוברים.
•	קיימים עמודים נוספים שאינם מוצגים בתפריט, אך נמצאים בקוד (ראו רשימה נפרדת).
•	קבצי קוד בסיסיים (auth.php, functions.php ועוד) מספקים את תשתית ההרשאות, הניווט והפונקציות העזר.
________________________________________
'@


# 2) מבנה התיקיות + הקבצים המפוצלים:
#    נשמור כאן מפתחות: "תיקייה" => "Hashtable של קבצים"
#    בכל "קובץ" => "תוכן"
$structure = @{

    '01_IntroAndSubtopics' = @{
        'intro_main.md' = @'
(כאן נשים את ההקדמה ותתי-הנושאים הראשיים)
[לדוגמה אפשר לקחת חלק מהטקסט על "הקדמה", "דוגמאות לקבצים בסיסיים", וכו']
'@
    },

    '02_Pages' = @{
    # כאן נשים את כל העמודים למעט GiftCards
        'pages_all.md' = @'
(כאן אפשר לתמצת את עמודים 1..29, חוץ מ-Giftcards)
[למשל: 1) home, 2) ccard, וכו']
'@,

        # תיקייה לעמוד Giftcards בלבד
        'Giftcards' = @{
            'giftcards_intro.md' = @'
(כאן "התמקדות בעמוד GiftCards", הפתיח)
'@,
            'giftcards_scenarios.md' = @'
(כאן התרחישים 1..10 - כניסה לעמוד וצפייה ברשימה, וכו’)
'@,
            'giftcards_tables.md' = @'
(כאן הטבלאות המפורטות לכל תרחיש - או פיצול נוסף)
'@
        }
    },

    '03_Various' = @{
    # שלושה placeholder files
        'various1.md' = "(תוכן Placeholder ראשון)",
        'various2.md' = "(תוכן Placeholder שני)",
        'various3.md' = "(תוכן Placeholder שלישי)"
    },

    '04_Conclusion' = @{
        'final_summary.md' = @'
(כאן הקובץ סיכום כללי
אפשר לקחת את החלק: "סיכום - עברנו על מבנה המערכת..." וכו')
'@
    }
}


# =================== פונקציות עזר ======================
function Ensure-Folder($path) {
    if (!(Test-Path $path)) {
        New-Item -ItemType Directory -Path $path | Out-Null
    }
}

function Create-TreeRecursively($base, $tree) {
    Ensure-Folder $base
    foreach($key in $tree.Keys) {
        $val = $tree[$key]
        $subPath = Join-Path $base $key
        if($val -is [string]) {
            # val is file content
            $val | Out-File -FilePath $subPath -Encoding UTF8BOM
            Write-Host "Created file: $subPath"
        }
        elseif($val -is [hashtable]) {
            # val is subfolder
            Ensure-Folder $subPath
            Create-TreeRecursively $subPath $val
        }
    }
}

# ================= MAIN ====================
Write-Host "=== Creating doc project in: $RootFolder ==="
Ensure-Folder $RootFolder

# 1) צור קובץ מלא בשם FullDoc.md
$fullDocPath = Join-Path $RootFolder "FullDoc.md"
$FullDoc | Out-File -FilePath $fullDocPath -Encoding UTF8BOM
Write-Host "Created file: $fullDocPath (contains entire doc)"

# 2) צור את המבנה המפוצל
Create-TreeRecursively $RootFolder $structure

Write-Host "Done! Check the '$RootFolder' folder."
