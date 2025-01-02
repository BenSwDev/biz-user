document.addEventListener("DOMContentLoaded", () => {
  // מאזינים לכל הקישורים בתפריט הראשי
  const mainLinks = document.querySelectorAll('nav.sidebar a[data-page]');
  mainLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const pageName = link.getAttribute("data-page");
      loadPageContent(pageName);
    });
  });
});

/**
 * טוען את קובץ ה-JSON (ע"פ pageName), ומציג אותו ב-div#page-content
 * וגם מייצר תפריט משנה בתפריט הצד עבור סעיפי ה־sections שב־JSON.
 */
function loadPageContent(pageName) {
  const url = `json/${pageName}.json`;
  fetch(url)
    .then((res) => {
      if (!res.ok) {
        throw new Error("Cannot load JSON");
      }
      return res.json();
    })
    .then((data) => {
      // קודם מרנדרים את ה-HTML של העמוד
      renderPageContent(data);

      // ואח"כ יוצרים את תפריט המשנה (sections)
      createSubmenuForSections(pageName, data);
    })
    .catch((err) => {
      console.error(err);
      const container = document.getElementById("page-content");
      container.innerHTML = `<h2>שגיאה בטעינת העמוד</h2>
        <p>${err.message}</p>`;
      // במקרה של שגיאה, אפשר גם לנקות תפריט משנה
      clearSubmenu();
    });
}

/**
 * מרנדר את תוכן ה-JSON בעמוד (כותרת + sections + subSections).
 * מציג כברירת מחדל את *כל* הסעיפים, אך מייד לאחר מכן
 * נרצה להסתיר את כולם ולהשאיר רק את הראשון (באמצעות createSubmenuForSections).
 */
function renderPageContent(data) {
  const container = document.getElementById("page-content");
  container.innerHTML = ""; // מנקה תוכן קודם

  // כותרת ראשית
  const pageTitle = document.createElement("h1");
  pageTitle.textContent = data.pageTitle || "עמוד ללא כותרת";
  container.appendChild(pageTitle);

  // ואם יש sections...
  if (Array.isArray(data.sections)) {
    data.sections.forEach((sec, secIndex) => {
      // div עוטף לסקשן
      const sectionDiv = document.createElement("div");
      sectionDiv.className = "section-block";
      // נזהה את ה-section הזה בעזרת data-section-index
      sectionDiv.setAttribute("data-section-index", secIndex);

      // כותרת הסקשן (h2)
      const h2 = document.createElement("h2");
      h2.textContent = sec.title || `סעיף ${secIndex + 1}`;
      sectionDiv.appendChild(h2);

      // אם ל-sections יש תוכן (content)
      if (sec.content) {
        const contentDiv = document.createElement("div");
        contentDiv.innerHTML = sec.content;
        sectionDiv.appendChild(contentDiv);
      }

      // אם יש subSections
      if (Array.isArray(sec.subSections)) {
        sec.subSections.forEach((subSec) => {
          const h3 = document.createElement("h3");
          h3.textContent = subSec.title;
          sectionDiv.appendChild(h3);

          const subDiv = document.createElement("div");
          subDiv.innerHTML = subSec.content || "";
          sectionDiv.appendChild(subDiv);
        });
      }

      container.appendChild(sectionDiv);
    });
  }
  else {
    // אם אין sections, מציגים הודעה
    const p = document.createElement("p");
    p.textContent = "לא נמצאו סעיפים (sections) בקובץ ה-JSON.";
    container.appendChild(p);
  }
}

/**
 * בונה תפריט משנה (Submenu) בתוך ה-sidebar, מתחת לפריט העמוד שנבחר.
 * תפריט זה מציג את רשימת ה-sections מה-JSON בתור LI -> A, לדוגמה:
 * - הקדמה
 * - תרחישים
 * - קבצים
 * - ...
 * לחיצה על אחד מהם תסתיר את שאר הסעיפים ותציג רק את הסעיף הנבחר.
 */
function createSubmenuForSections(pageName, data) {
  // קודם נוודא שננקה תפריט משנה קיים (אם היה לעמוד קודם)
  clearSubmenu();

  // מוצאים ב-sidebar את ה-li[data-page=...]
  const mainSidebar = document.getElementById("main-sidebar");
  const linkElement = mainSidebar.querySelector(`a[data-page="${pageName}"]`);
  if (!linkElement) {
    return; // לא מצאנו קישור כזה, אולי שגיאה?
  }

  // ניצור UL חדש עבור תפריט המשנה
  const subUl = document.createElement("ul");
  subUl.className = "submenu";

  // אם ב-JSON אין sections, לא נבנה תפריט
  if (!Array.isArray(data.sections) || data.sections.length === 0) {
    // בכל זאת נוסיף LI ריק? או נוותר
    const liNoSections = document.createElement("li");
    liNoSections.textContent = "(אין סעיפים)";
    subUl.appendChild(liNoSections);
  } else {
    // יוצרים LI/Links עבור כל section
    data.sections.forEach((sec, index) => {
      const li = document.createElement("li");
      const a = document.createElement("a");
      a.href = "#";
      a.textContent = sec.title || `סעיף ${index + 1}`;

      // אירוע קליק: מציג רק את הסעיף הרצוי
      a.addEventListener("click", (e) => {
        e.preventDefault();
        showSection(index);
      });

      li.appendChild(a);
      subUl.appendChild(li);
    });
  }

  // נוסיף את subUl כילד של אבא-<li> (או אחרי ה־a)
  // כדי להגיע ל־<li> שעוטף את linkElement, נשתמש ב־linkElement.closest("li")
  const parentLi = linkElement.closest("li");
  if (parentLi) {
    parentLi.appendChild(subUl);
  }

  // בהתחלה נציג רק את הסעיף הראשון (אם קיים)
  if (Array.isArray(data.sections) && data.sections.length > 0) {
    showSection(0);
  }
}

/**
 * מנקה את כל תפריטי המשנה (submenu) הקיימים ב-sidebar.
 * (למשל אם עברנו מעמוד5 לעמוד6, נרצה להסיר את submenu של עמוד5)
 */
function clearSubmenu() {
  const oldSubmenus = document.querySelectorAll(".submenu");
  oldSubmenus.forEach((ul) => {
    ul.remove();
  });
}

/**
 * מציג רק את הסקשן בעל אינדקס מסוים, ומסתיר את האחרים.
 */
function showSection(secIndex) {
  const allSections = document.querySelectorAll("#page-content .section-block");
  allSections.forEach((sectionDiv) => {
    const index = parseInt(sectionDiv.getAttribute("data-section-index"), 10);
    if (index === secIndex) {
      sectionDiv.style.display = "block";
    } else {
      sectionDiv.style.display = "none";
    }
  });
}
