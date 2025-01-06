// script.js
let currentPage = null;
let originalContent = ''; // Raw Markdown from server/localStorage
let editor;              // Toast UI Editor instance

document.addEventListener('DOMContentLoaded', () => {
  const sidebarLinks = document.querySelectorAll('#main-sidebar a[data-page]');
  const pageContent  = document.getElementById('page-content');
  const editorContainer = document.getElementById('editorContainer');

  const editToggleBtn = document.getElementById('editToggleBtn');
  const saveChangesBtn = document.getElementById('saveChangesBtn');
  const revertChangesBtn = document.getElementById('revertChangesBtn');

  // 1) Initialize the Toast UI Editor in hidden mode:
  editor = new toastui.Editor({
    el: editorContainer,
    height: '600px',
    initialEditType: 'wysiwyg', // or 'markdown'
    previewStyle: 'vertical',
    usageStatistics: false
    // language: 'en-US' // Hebrew translation is not built-in, but you can create custom translations
  });
  editor.hide(); // Hide by default (in read mode)

  // 2) Function to load a page’s .md content
  async function loadPage(pageName) {
    currentPage = pageName;

    // Check localStorage first
    const localEdits = localStorage.getItem('page-' + pageName);
    if (localEdits) {
      originalContent = localEdits;
    } else {
      // Fetch from server md/[pageName].md
      try {
        const response = await fetch(`md/${pageName}.md`);
        if (!response.ok) {
          pageContent.innerHTML = `<p style="color: red;">שגיאה בטעינת הקובץ <strong>${pageName}.md</strong></p>`;
          return;
        }
        originalContent = await response.text();
      } catch (err) {
        pageContent.innerHTML = `<p style="color: red;">שגיאה בטעינת הקובץ ${pageName}.md</p>`;
        return;
      }
    }

    // Render read-only HTML in page-content
    editor.setMarkdown(originalContent, false /* no cursor move */);
    pageContent.innerHTML = editor.getHTML();

    // Hide the editor in read mode, show the read-only content
    editor.hide();
    editorContainer.style.display = 'none';
    pageContent.style.display = 'block';

    // Reset buttons
    editToggleBtn.style.display = 'inline-block';
    saveChangesBtn.style.display = 'none';
    revertChangesBtn.style.display = 'none';
  }

  // 3) Switch to Edit mode
  editToggleBtn.addEventListener('click', () => {
    if (!currentPage) return; // No page loaded

    // Show the editor with the current content
    editor.setMarkdown(originalContent, false);
    editor.show();
    editorContainer.style.display = 'block';

    // Hide the rendered content
    pageContent.style.display = 'none';

    // Adjust buttons
    editToggleBtn.style.display = 'none';
    saveChangesBtn.style.display = 'inline-block';
    revertChangesBtn.style.display = 'inline-block';
  });

  // 4) Save changes
  saveChangesBtn.addEventListener('click', () => {
    // Grab the updated markdown from the editor
    const updatedMarkdown = editor.getMarkdown();
    originalContent = updatedMarkdown;

    // For demo, store in localStorage
    localStorage.setItem('page-' + currentPage, updatedMarkdown);

    // Switch back to read mode
    pageContent.innerHTML = editor.getHTML();
    editor.hide();
    editorContainer.style.display = 'none';
    pageContent.style.display = 'block';

    // Adjust buttons
    editToggleBtn.style.display = 'inline-block';
    saveChangesBtn.style.display = 'none';
    revertChangesBtn.style.display = 'none';
  });

  // 5) Revert changes (remove localStorage item, reload from server)
  revertChangesBtn.addEventListener('click', async () => {
    localStorage.removeItem('page-' + currentPage);

    // Re-fetch from server
    try {
      const response = await fetch(`md/${currentPage}.md`);
      if (response.ok) {
        originalContent = await response.text();
      } else {
        originalContent = 'שגיאה בטעינת הקובץ';
      }
    } catch (e) {
      originalContent = 'שגיאה בטעינת הקובץ';
    }

    // Show read-only again
    editor.setMarkdown(originalContent, false);
    pageContent.innerHTML = editor.getHTML();
    editor.hide();
    editorContainer.style.display = 'none';
    pageContent.style.display = 'block';

    // Update buttons
    editToggleBtn.style.display = 'inline-block';
    saveChangesBtn.style.display = 'none';
    revertChangesBtn.style.display = 'none';
  });

  // 6) Sidebar links
  sidebarLinks.forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      const pageName = link.getAttribute('data-page');
      loadPage(pageName);
    });
  });
});
