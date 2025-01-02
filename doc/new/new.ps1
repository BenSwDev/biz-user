# new.ps1
# PowerShell script to list only specific PHP files (with their paths and contents)
# from the specified folder and write them to new.txt.

# --- Configuration ---
$basePath  = "C:\Users\Ben Swissa\Desktop\backup\Employee - Spa Plus\user\pages"  # מיקום תיקיית קבצי ה-PHP
$txtPath   = "C:\Users\Ben Swissa\Desktop\backup\Employee - Spa Plus\user\doc\new"
$newTxt    = Join-Path $txtPath "new.txt"

# נכין רשימה של שמות קבצי ה-PHP הדרושים:
# הוספנו calendar_ver2.php עבור עמוד 12
# הסבר לכל אחד לפי רשימת העמודים:
$targetFiles = @(
    'calendar_ver2.php',     # עמוד 12 (יומן זמינות והזמנות)
    'orders.php',            # עמודים 13,16,24,27 (וריאציה / חיפוש / יצירת הזמנה)
    'clients.php',           # עמוד 15 (לקוחות)
    'prices.php',            # עמוד 17 (מחירים)
    'agreements.php',        # עמוד 18 (הגדרות)
    'reviews.php',           # עמודים 19,28 (חוות דעת + userType=2)
    'stats.php',             # עמוד 20 (סטטיסטיקות)
    'vilasavail.php'       # עמוד 21 (עדכון פנויים)
# עמוד 22 כנראה לא קיים כקובץ נפרד (או נקרא home.php) ולכן לא נוסף כאן
)

# --- Step 1: Clear or create 'new.txt' ---
Set-Content -Path $newTxt -Value $null

# --- Step 2: Gather only the relevant PHP files (recursively) ---
$phpFiles = Get-ChildItem -Path $basePath -Include *.php -File -Recurse |
        Where-Object { $targetFiles -contains $_.Name }

# --- Step 3: Write PHP file paths & contents to 'new.txt' ---
foreach ($file in $phpFiles) {
    Add-Content -Path $newTxt -Value "`r`n========================================"
    Add-Content -Path $newTxt -Value "File Path: $($file.FullName)"
    Add-Content -Path $newTxt -Value "----------------------------------------"

    # Get file contents and append them to 'new.txt'
    Get-Content -Path $file.FullName | Add-Content -Path $newTxt
}

Write-Host "Only the specified PHP files have been processed. Please check 'new.txt' for details."
