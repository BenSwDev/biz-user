<?php
// issue-reporter.php
// ============= ADMIN INTERFACE ONLY =============
//
// CHANGES/FIXES:
// 1) Improved design via "issue-reporter-design.css" for filters row & tabs
// 2) Admin can now edit/delete any comment written by an admin (no more "only same admin" check)
// 3) Inline editing of Issue Type, Status, and Assigned Admin
// 4) All existing functionalities intact, except references to capture.js / record.js removed

session_start();
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ----------------------------
// Multi-Admin (no DB) Settings
// ----------------------------
$admins = [
	'ben' => 'Bs7777$',
    'rebecca' => 'Ra1205$',
    'shlomi'  => '2907',
    'shuli'   => '2907',
    'roi'     => '2907'
];

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: issue-reporter.php");
    exit;
}

// Handle login form
if (isset($_POST['admin_username'], $_POST['admin_password'])) {
    $u = trim($_POST['admin_username']);
    $p = trim($_POST['admin_password']);
    if (isset($admins[$u]) && $admins[$u] === $p) {
        $_SESSION['admin_user'] = $u;
    } else {
        $loginError = "שם משתמש או סיסמא לא נכונים";
    }
}

// If not logged in, show login form
if (!isset($_SESSION['admin_user'])) {
    ?>
    <!DOCTYPE html>
    <html lang="he" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>התחברות למערכת ניהול הבעיות</title>
        <link rel="stylesheet" href="issue-reporter-design.css">
    </head>
    <body>
    <div class="container-fluid p-3">
      <h1>התחברות למערכת ניהול הבעיות</h1>
      <?php if (!empty($loginError)): ?>
          <div class="error-message" style="display:block; margin-bottom:1rem;">
              <?=htmlspecialchars($loginError)?>
          </div>
      <?php endif; ?>
      <form method="post" style="max-width:300px;">
        <div class="mb-3">
          <label>שם משתמש</label>
          <input type="text" name="admin_username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>סיסמא</label>
          <input type="password" name="admin_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">התחבר</button>
      </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Now we are logged in
function getCurrentUser()
{
    return $_SESSION['admin_user'] ?? 'unknown_admin';
}

$issuesFile = __DIR__ . '/issues.json';
$uploadsDir = __DIR__ . '/issues-uploads';

$statuses = ["not started", "in progress", "done"];
$issueTypes = [
    "דיווח על תקלה",
    "בעיית תצוגה",
    "תרגום שגוי",
    "הצעות ובקשות",
    "אחר"
];

// ----------------- Utility Functions -----------------
function loadIssues($filePath)
{
    $data = @file_get_contents($filePath);
    if (!$data) return [];
    $arr = json_decode($data, true);
    return is_array($arr) ? $arr : [];
}
function saveIssues($filePath, $issues)
{
    $json = json_encode($issues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filePath, $json) !== false;
}
function &findIssueById(&$issues, $id)
{
    foreach ($issues as &$issue) {
        if ($issue['id'] == $id) return $issue;
    }
    $null = null;
    return $null;
}
function deleteIssueImages($issue, $uploadsDir)
{
    if (!empty($issue['images'])) {
        foreach ($issue['images'] as $img) {
            $p = $uploadsDir . '/' . $img;
            if (file_exists($p)) @unlink($p);
        }
    }
}
function addComment(&$issue, $text, $author)
{
    $issue['comments'] ??= [];
    $issue['comments'][] = [
        'text' => trim($text),
        'timestamp' => date('Y-m-d H:i:s'),
        'author' => $author
    ];
}

// ----------------- Load All Issues -----------------
$issues = loadIssues($issuesFile);

// ----------------- Handle POST (AJAX) -------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Delete image
    if (isset($_POST['delete_image'])) {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $imgName = trim($_POST['image_name'] ?? '');
        $found = &findIssueById($issues, $issue_id);
        if ($found && in_array($imgName, $found['images'])) {
            $found['images'] = array_values(array_filter($found['images'], fn($x) => $x !== $imgName));
            $p = $uploadsDir . '/' . $imgName;
            if (file_exists($p)) @unlink($p);
            saveIssues($issuesFile, $issues);
            echo "OK";
        } else {
            echo "Error: image not found or invalid issue.";
        }
        exit;
    }

    // 2) Create admin issue
    if (isset($_POST['create_admin_issue'])) {
        $issue_type = trim($_POST['issue_type'] ?? '');
        $problem = trim($_POST['problem'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $adminUser = getCurrentUser();
        if ($issue_type === '' || $problem === '') {
            echo "Error: missing fields";
            exit;
        }
        $newId = (string)time();
        // Minimal image handling for admin issues (none by default)
        $newIssue = [
            'id' => $newId,
            'issue_type' => $issue_type,
            'username' => "Admin: $adminUser",
            'problem' => $problem,
            'description' => $desc,
            'images' => [],
            'status' => 'not started',
            'date_reported' => date('Y-m-d H:i:s'),
            'ended_at' => null,
            'comments' => [],
            'assigned_admin' => $adminUser
        ];
        $issues[] = $newIssue;
        if (saveIssues($issuesFile, $issues)) {
            echo "issue_created_ok";
        } else {
            echo "Error: failed to save admin issue";
        }
        exit;
    }

    // 3) Inline update (assigned_admin, status, issue_type)
    if (isset($_POST['inline_update']) && $_POST['inline_update'] === 'edit_issue') {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $newAssigned = trim($_POST['new_assigned'] ?? '');
        $newStatus = trim($_POST['new_status'] ?? '');
        $newType = trim($_POST['new_type'] ?? '');

        $found = &findIssueById($issues, $issue_id);
        if (!$found) {
            echo "Error: Issue not found";
            exit;
        }
        // Validate status
        if (!in_array($newStatus, ["not started","in progress","done"])) {
            echo "Error: invalid status";
            exit;
        }
        // Validate type
        if (!in_array($newType, [
            "דיווח על תקלה",
            "בעיית תצוגה",
            "תרגום שגוי",
            "הצעות ובקשות",
            "אחר"
        ])) {
            echo "Error: invalid issue type";
            exit;
        }

        // Update
        $found['assigned_admin'] = $newAssigned;
        $found['status'] = $newStatus;
        $found['issue_type'] = $newType;
        if ($newStatus === 'done') {
            $found['ended_at'] = date('Y-m-d H:i:s');
        } else {
            $found['ended_at'] = null;
        }

        saveIssues($issuesFile, $issues);
        echo "issue_edited_ok";
        exit;
    }

    // 4) Add comment (admin)
    if (isset($_POST['add_comment'])) {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $text = trim($_POST['comment'] ?? '');
        if (!$issue_id || !$text) {
            echo "Error: missing data";
            exit;
        }
        $found = &findIssueById($issues, $issue_id);
        if (!$found) {
            echo "Error: issue not found";
            exit;
        }
        addComment($found, $text, getCurrentUser());
        saveIssues($issuesFile, $issues);
        echo "comment_added_ok";
        exit;
    }

    // 5) Edit comment (admin) – now ANY admin can edit ANY existing admin comment
    if (isset($_POST['edit_comment'])) {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $cIndex = (int)($_POST['comment_index'] ?? -1);
        $newText = trim($_POST['comment_text'] ?? '');
        if (!$issue_id || $cIndex < 0 || !$newText) {
            echo "Error: missing data";
            exit;
        }
        $found = &findIssueById($issues, $issue_id);
        if (!$found) {
            echo "Error: issue not found";
            exit;
        }
        if (!isset($found['comments'][$cIndex])) {
            echo "Error: comment not found";
            exit;
        }
        // Remove any "must match your name" check
        $found['comments'][$cIndex]['text'] = $newText;
        $found['comments'][$cIndex]['timestamp'] = date('Y-m-d H:i:s');

        saveIssues($issuesFile, $issues);
        echo "comment_edited_ok";
        exit;
    }

    // 6) Delete comment (admin) – now ANY admin can delete ANY existing admin comment
    if (isset($_POST['delete_comment'])) {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $cIndex = (int)($_POST['comment_index'] ?? -1);
        if (!$issue_id || $cIndex < 0) {
            echo "Error: missing data";
            exit;
        }
        $found = &findIssueById($issues, $issue_id);
        if (!$found) {
            echo "Error: issue not found";
            exit;
        }
        if (!isset($found['comments'][$cIndex])) {
            echo "Error: comment not found";
            exit;
        }
        // Remove any "must match your name" check
        array_splice($found['comments'], $cIndex, 1);

        saveIssues($issuesFile, $issues);
        echo "comment_deleted_ok";
        exit;
    }

    // 7) Delete entire issue
    if (isset($_POST['delete_entire_issue'])) {
        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $found = &findIssueById($issues, $issue_id);
        if ($found) {
            deleteIssueImages($found, $uploadsDir);
            $issues = array_filter($issues, fn($x) => $x['id'] != $issue_id);
            saveIssues($issuesFile, $issues);
            echo "issue_deleted_ok";
        } else {
            echo "Error: Issue not found.";
        }
        exit;
    }
}

// ----------------- Prepare data for UI -------------
usort($issues, function($a, $b){
    // Sort by date_reported descending
    return strcmp($b['date_reported'], $a['date_reported']);
});
// Ensure assigned_admin is set
foreach($issues as &$iss){
    if(!isset($iss['assigned_admin'])){
        $iss['assigned_admin'] = '';
    }
}
unset($iss);

// Build stats
function calcStats($list){
    global $statuses, $issueTypes;
    $byUser = [];
    $byType = [];
    $byStatus = ['not started'=>0,'in progress'=>0,'done'=>0];
    $doneTimes = [];
    foreach($list as $x){
        $byUser[$x['username']] = ($byUser[$x['username']] ?? 0) + 1;
        $byType[$x['issue_type']] = ($byType[$x['issue_type']] ?? 0) + 1;
        if(isset($byStatus[$x['status']])){
            $byStatus[$x['status']]++;
        }
        if($x['status']==='done' && !empty($x['ended_at']) && !empty($x['date_reported'])){
            $start = strtotime($x['date_reported']);
            $end   = strtotime($x['ended_at']);
            if($end>$start){
                $doneTimes[] = $end - $start;
            }
        }
    }
    $avgTime = 0;
    if(count($doneTimes)>0){
        $avgTime = array_sum($doneTimes)/count($doneTimes);
    }
    return [
        'byUser' => $byUser,
        'byType' => $byType,
        'byStatus' => $byStatus,
        'avgTime' => $avgTime
    ];
}
$stats = calcStats($issues);

// Group issues by type & status
function buildIssuesByType($all, $issueTypes){
    $issuesByType=[];
    foreach($issueTypes as $t){
        $issuesByType[$t] = ['not started'=>[], 'in progress'=>[], 'done'=>[]];
    }
    $issuesByType["*"] = ['not started'=>[], 'in progress'=>[], 'done'=>[]];
    foreach($all as $iss){
        $tp = $iss['issue_type'];
        $st = $iss['status'];
        if(isset($issuesByType[$tp][$st])){
            $issuesByType[$tp][$st][] = $iss;
        }
        $issuesByType["*"][$st][] = $iss;
    }
    return $issuesByType;
}
$issuesByType = buildIssuesByType($issues, $issueTypes);

// "My tasks"
$myUser = getCurrentUser();
$issuesByType["_my_"] = [
    'not started'=>[],
    'in progress'=>[],
    'done'=>[]
];
foreach($issues as $iss){
    if($iss['assigned_admin'] === $myUser){
        $issuesByType["_my_"][$iss['status']][]=$iss;
    }
}
function countAllByType($tpArray){
    $sum=0;
    foreach($tpArray as $arr){
        $sum += count($arr);
    }
    return $sum;
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="UTF-8">
<title>ניהול בעיות (Admin)</title>
<link rel="stylesheet" href="issue-reporter-design.css">
</head>
<body>

<?php
$currentUserEsc = htmlspecialchars(getCurrentUser(), ENT_QUOTES, 'UTF-8');
echo "<script>const CURRENT_ADMIN_USER = '{$currentUserEsc}';</script>";
?>

<div class="bg-dark text-white d-flex justify-content-between align-items-center p-3 mb-2">
  <h5 class="mb-0">ברוך הבא, <?=htmlspecialchars(getCurrentUser())?> (Admin)</h5>
  <div>
    <a href="?logout=1" class="btn btn-sm btn-secondary">Logout</a>
  </div>
</div>

<div class="container-fluid admin-container">
  <h1 class="text-center mb-4">ניהול בעיות</h1>

  <!-- Filters Row (styled by issue-reporter-design.css) -->
  <div class="filters-row">
    <div class="col-auto">
      <div class="btn-group">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userFilterBtn">
          סנן לפי משתמש
        </button>
        <ul class="dropdown-menu" id="userFilterMenu"></ul>
      </div>
    </div>
    <div class="col-auto">
      <div class="btn-group">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dateRangeBtn">
          סנן לפי זמן
        </button>
        <ul class="dropdown-menu" id="dateRangeMenu">
          <li><a class="dropdown-item" href="#" data-value="all">הכל</a></li>
          <li><a class="dropdown-item" href="#" data-value="today">היום</a></li>
          <li><a class="dropdown-item" href="#" data-value="week">השבוע</a></li>
          <li><a class="dropdown-item" href="#" data-value="month">החודש</a></li>
        </ul>
      </div>
    </div>
    <div class="col-auto">
      <input type="text" class="form-control" id="idFilterInput" placeholder="חיפוש לפי מזהה...">
    </div>
    <div class="col d-flex justify-content-end gap-2">
      <button class="btn btn-primary" id="createIssueBtn">צור בעיה חדשה</button>
      <button class="btn btn-secondary" id="refreshIssuesBtn">רענן</button>
      <button class="btn btn-info text-white" id="toggleAllContainersBtn">פתח/כווץ הכל</button>
      <button class="btn btn-info text-white" id="statisticsBtn">סטטיסטיקות</button>
    </div>
  </div>

  <!-- Tabs Row (styled by issue-reporter-design.css) -->
  <ul class="nav nav-tabs" id="issueNavTabs">
    <?php
      // My tasks
      $myCount = countAllByType($issuesByType["_my_"]);
    ?>
    <li class="nav-item">
      <button class="nav-link" data-tab-type="_my_">המשימות שלי (<?=$myCount?>)</button>
    </li>
    <?php $wildCount = countAllByType($issuesByType["*"]); ?>
    <li class="nav-item">
      <button class="nav-link" data-tab-type="*" style="margin-right:10px;">הכל (<?=$wildCount?>)</button>
    </li>
    <?php foreach($issueTypes as $tp):
      $c = countAllByType($issuesByType[$tp]);
    ?>
    <li class="nav-item">
      <button class="nav-link" data-tab-type="<?=$tp?>"><?=htmlspecialchars($tp)?> (<?=$c?>)</button>
    </li>
    <?php endforeach; ?>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content" id="tabContainer">
    <?php
    // Helper to render table HTML for each status in each tab
    function renderTableHTML($list, $status, $type, $admins, $issueTypes, $statuses){
      ob_start();
      ?>
      <div class="table-responsive issue-table-container"
           data-status="<?=htmlspecialchars($status)?>"
           data-type="<?=htmlspecialchars($type)?>"
           data-total="<?=count($list)?>"
           data-page="1">
        <h4>
          <?=htmlspecialchars($status)?> (<?=count($list)?>)
          <button class="btn btn-sm btn-outline-secondary toggle-collapse-btn" type="button">Collapse</button>
        </h4>
        <table class="table table-striped align-middle" data-list-count="<?=count($list)?>">
          <thead>
            <tr>
              <th>ID</th>
              <th>סוג</th>
              <th>משתמש</th>
              <th>נושא</th>
              <th>תיאור</th>
              <th>תמונות</th>
              <th class="sortable" data-sort-key="date_reported">תאריך דיווח</th>
              <th>בוצע בתאריך</th>
              <th>סטטוס</th>
              <th>משוייך ל</th>
              <th>פעולות</th>
            </tr>
          </thead>
          <tbody>
          <?php if(!count($list)): ?>
            <tr>
              <td colspan="11" class="text-center text-muted">אין רשומות לסטטוס זה</td>
            </tr>
          <?php else: ?>
            <?php foreach($list as $iss): ?>
            <tr data-issue-id="<?=htmlspecialchars($iss['id'])?>"
                data-username="<?=htmlspecialchars($iss['username'])?>"
                data-datereported="<?=htmlspecialchars($iss['date_reported'])?>"
                data-assigned-admin="<?=htmlspecialchars($iss['assigned_admin'])?>">

              <td><?=htmlspecialchars($iss['id'])?></td>
              <td>
                <!-- Issue Type inline -->
                <form class="inline-edit-issue-form" style="display:flex; gap:5px; align-items:center;">
                  <input type="hidden" name="inline_update" value="edit_issue">
                  <input type="hidden" name="issue_id" value="<?=htmlspecialchars($iss['id'])?>">
                  <input type="hidden" class="assigned-field" name="new_assigned" value="<?=htmlspecialchars($iss['assigned_admin'])?>">
                  <input type="hidden" class="status-field" name="new_status" value="<?=htmlspecialchars($iss['status'])?>">

                  <select name="new_type" class="form-select form-select-sm type-select">
                    <?php foreach($issueTypes as $tOpt): ?>
                      <option value="<?=htmlspecialchars($tOpt)?>"
                        <?php if($iss['issue_type'] === $tOpt) echo 'selected'; ?>>
                        <?=htmlspecialchars($tOpt)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td><?=htmlspecialchars($iss['username'])?></td>
              <td><?=htmlspecialchars($iss['problem'])?></td>
              <td><?=nl2br(htmlspecialchars($iss['description']))?></td>
              <td>
                <?php if(!empty($iss['images'])): ?>
                  <?php foreach($iss['images'] as $img): ?>
                  <div style="position:relative; display:inline-block;">
                    <img src="issues-uploads/<?=htmlspecialchars($img)?>"
                         alt=""
                         style="width:50px;height:50px;object-fit:cover;margin:2px;"
                         class="issue-img-thumb">
                    <button class="btn btn-danger btn-sm p-0 delete-image-btn"
                            style="position:absolute; top:0; right:0; width:22px; height:22px;"
                            data-image="<?=htmlspecialchars($img)?>"
                            title="מחק תמונה">
                      ×
                    </button>
                  </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </td>
              <td><?=htmlspecialchars($iss['date_reported'])?></td>
              <td><?=(!empty($iss['ended_at']) ? htmlspecialchars($iss['ended_at']) : 'N/A')?></td>
              <td>
                <!-- Status inline -->
                <form class="inline-edit-issue-form" style="display:flex; gap:5px; align-items:center;">
                  <input type="hidden" name="inline_update" value="edit_issue">
                  <input type="hidden" name="issue_id" value="<?=htmlspecialchars($iss['id'])?>">
                  <input type="hidden" class="assigned-field" name="new_assigned" value="<?=htmlspecialchars($iss['assigned_admin'])?>">
                  <input type="hidden" class="type-field" name="new_type" value="<?=htmlspecialchars($iss['issue_type'])?>">

                  <select name="new_status" class="form-select form-select-sm status-select">
                    <?php foreach($statuses as $stOpt): ?>
                      <option value="<?=htmlspecialchars($stOpt)?>"
                        <?php if($iss['status'] === $stOpt) echo 'selected'; ?>>
                        <?=htmlspecialchars($stOpt)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <!-- Assigned admin inline -->
                <form class="inline-edit-issue-form" style="display:flex; gap:5px; align-items:center;">
                  <input type="hidden" name="inline_update" value="edit_issue">
                  <input type="hidden" name="issue_id" value="<?=htmlspecialchars($iss['id'])?>">
                  <input type="hidden" class="type-field" name="new_type" value="<?=htmlspecialchars($iss['issue_type'])?>">
                  <input type="hidden" class="status-field" name="new_status" value="<?=htmlspecialchars($iss['status'])?>">

                  <select name="new_assigned" class="form-select form-select-sm assigned-select">
                    <option value="">לא משוייך</option>
                    <?php foreach($admins as $admName => $admPass): ?>
                      <option value="<?=htmlspecialchars($admName)?>"
                        <?php if($iss['assigned_admin'] === $admName) echo 'selected'; ?>>
                        <?=htmlspecialchars($admName)?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <button class="btn btn-sm btn-primary view-comments-btn"
                        data-issue-id="<?=htmlspecialchars($iss['id'])?>"
                        data-comments='<?=json_encode($iss['comments'], JSON_UNESCAPED_UNICODE)?>'
                        title="צפה בהערות">
                  הערות
                </button>
                <button class="btn btn-sm btn-danger delete-issue-btn" title="מחק דיווח זה">
                  מחק
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
        <?php if(count($list) > 5): ?>
          <div class="d-flex justify-content-center align-items-center p-2 gap-2">
            <button class="btn btn-secondary pagination-prev-btn">Prev</button>
            <span class="pagination-info"></span>
            <button class="btn btn-secondary pagination-next-btn">Next</button>
          </div>
        <?php endif; ?>
      </div>
      <?php
      return ob_get_clean();
    }

    // Render each tab's content
    // 1) My tasks
    echo "<div class='tab-pane' data-type='_my_' id='tabPane_my'>";
    foreach($statuses as $st){
      echo renderTableHTML($issuesByType["_my_"][$st], $st, "_my_", $admins, $issueTypes, $statuses);
    }
    echo "</div>";

    // 2) "*" tab
    echo "<div class='tab-pane' data-type='*' id='tabPane_star'>";
    foreach($statuses as $st){
      echo renderTableHTML($issuesByType["*"][$st], $st, "*", $admins, $issueTypes, $statuses);
    }
    echo "</div>";

    // 3) Each issueType tab
    foreach($issueTypes as $tp){
      $id = md5($tp);
      echo "<div class='tab-pane' data-type='{$tp}' id='tabPane_{$id}'>";
      foreach($statuses as $st){
        echo renderTableHTML($issuesByType[$tp][$st], $st, $tp, $admins, $issueTypes, $statuses);
      }
      echo "</div>";
    }
    ?>
  </div>
</div>

<!-- Toast Container -->
<div id="toastContainer"></div>

<!-- Create Admin Issue Modal -->
<div class="modal" id="createIssueModal">
  <div class="modal-dialog">
    <form class="modal-content" id="adminCreateIssueForm">
      <div class="modal-header">
        <h5 class="modal-title">צור בעיה חדשה (Admin)</h5>
        <button type="button" class="btn-close close-modal-btn"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="create_admin_issue" value="1">
        <div class="mb-3">
          <label class="form-label">סוג בעיה</label>
          <select name="issue_type" class="form-select" required>
            <option value="">בחר</option>
            <?php foreach($issueTypes as $opt): ?>
              <option value="<?=htmlspecialchars($opt)?>"><?=htmlspecialchars($opt)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">נושא</label>
          <input type="text" class="form-control" name="problem" required>
        </div>
        <div class="mb-3">
          <label>תיאור</label>
          <textarea name="description" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary close-modal-btn">בטל</button>
        <button type="submit" class="btn btn-primary">צור</button>
      </div>
    </form>
  </div>
</div>

<!-- Statistics Modal -->
<div class="modal" id="statsModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">סטטיסטיקות</h5>
        <button type="button" class="btn-close close-modal-btn"></button>
      </div>
      <div class="modal-body">
        <?php
        $byUser = $stats['byUser'];
        $byType = $stats['byType'];
        $byStatus = $stats['byStatus'];
        $avgTime = $stats['avgTime'];
        ?>
        <h6>לפי משתמש</h6>
        <ul>
          <?php foreach($byUser as $u=>$cnt): ?>
          <li><?=htmlspecialchars($u)?>: <?=htmlspecialchars($cnt)?></li>
          <?php endforeach; ?>
        </ul>
        <h6>לפי סוג</h6>
        <ul>
          <?php foreach($byType as $k=>$c): ?>
          <li><?=htmlspecialchars($k)?>: <?=$c?></li>
          <?php endforeach; ?>
        </ul>
        <h6>סטטוס</h6>
        <ul>
          <li>Not Started: <?=$byStatus['not started']?></li>
          <li>In Progress: <?=$byStatus['in progress']?></li>
          <li>Done: <?=$byStatus['done']?></li>
        </ul>
        <h6>זמן ממוצע להשלמה</h6>
        <?php if($avgTime>0): ?>
          <p><?=round($avgTime/60,2)?> דקות</p>
        <?php else: ?>
          <p>אין מספיק נתונים</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Comments Modal (Admin) -->
<div class="modal" id="commentsModal">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">הערות</h5>
        <button type="button" class="btn-close close-modal-btn"></button>
      </div>
      <div class="modal-body" id="commentsModalBody"></div>
    </div>
  </div>
</div>

<!-- Image Overlay -->
<div id="imageOverlay" class="image-overlay">
  <div class="image-overlay-content" id="imageOverlayContent">
    <span class="close-overlay" id="closeOverlayBtn">&times;</span>
    <img id="imageOverlayImg" src="" alt="Full Image">
  </div>
</div>

<script src="issue-reporter-comments-modal.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){

  // Toast function
  const toastContainer = document.getElementById('toastContainer');
  function showToast(msg, variant='success'){
    const div = document.createElement('div');
    div.classList.add('toast',`text-bg-${variant}`,'show');
    div.innerHTML=`
      <div class="toast-body d-flex justify-content-between">
        <span>${msg}</span>
        <button type="button" class="btn-close btn-close-white"></button>
      </div>`;
    toastContainer.appendChild(div);
    const closeBtn = div.querySelector('.btn-close');
    let hideTimeout = setTimeout(()=>removeToast(div),3000);
    closeBtn.addEventListener('click',()=>removeToast(div));
    div.addEventListener('mouseenter',()=>clearTimeout(hideTimeout));
    div.addEventListener('mouseleave',()=>{
      hideTimeout=setTimeout(()=>removeToast(div),1000);
    });
  }
  function removeToast(toastEl){
    toastEl.classList.remove('show');
    setTimeout(()=>{
      if(toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
    },300);
  }

  // Create Admin Issue
  const createIssueModalEl = document.getElementById('createIssueModal');
  const adminCreateForm = document.getElementById('adminCreateIssueForm');
  document.getElementById('createIssueBtn').addEventListener('click',()=>{
    adminCreateForm.reset();
    openModal(createIssueModalEl);
  });
  adminCreateForm.addEventListener('submit',(e)=>{
    e.preventDefault();
    const fd = new FormData(adminCreateForm);
    fetch('issue-reporter.php',{method:'POST',body:fd})
      .then(r=>r.text())
      .then(res=>{
        if(res.includes('issue_created_ok')){
          showToast('Issue created','success');
          closeModal(createIssueModalEl);
          location.reload();
        } else {
          showToast(res,'danger');
        }
      })
      .catch(err=>showToast(err,'danger'));
  });

  // Stats modal
  const statsModalEl = document.getElementById('statsModal');
  document.getElementById('statisticsBtn').addEventListener('click',()=>{
    openModal(statsModalEl);
  });

  // Refresh
  document.getElementById('refreshIssuesBtn').addEventListener('click',()=>location.reload());

  // Toggle all containers
  const toggleAllBtn = document.getElementById('toggleAllContainersBtn');
  toggleAllBtn.addEventListener('click',()=>{
    const activeTabPane = document.querySelector('.tab-pane.active');
    if(!activeTabPane)return;
    const containers = activeTabPane.querySelectorAll('.issue-table-container');
    containers.forEach(cont=>{
      const btn = cont.querySelector('.toggle-collapse-btn');
      if(btn) btn.click();
    });
  });

  // Generic modals
  document.querySelectorAll('.modal').forEach(modal=>{
    modal.addEventListener('click',(e)=>{
      if(e.target.classList.contains('modal')){
        closeModal(modal);
      }
    });
    const closeB = modal.querySelectorAll('.close-modal-btn');
    closeB.forEach(btn=>{
      btn.addEventListener('click',()=>closeModal(modal));
    });
  });
  function openModal(m){
    m.style.display='flex';
    m.classList.add('show');
  }
  function closeModal(m){
    m.style.display='none';
    m.classList.remove('show');
  }

  // Comments
  document.querySelectorAll('.view-comments-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const issueId = btn.dataset.issueId||'';
      const comments = btn.dataset.comments||'[]';
      const ev = new CustomEvent('openCommentsModal',{
        detail:{issueId,comments,isAdmin:true}
      });
      document.dispatchEvent(ev);
    });
  });

  // Delete entire issue
  document.querySelectorAll('.delete-issue-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      if(!confirm('מחק דיווח זה?'))return;
      const row = btn.closest('tr');
      const issueId = row.dataset.issueId;
      const fd = new FormData();
      fd.set('delete_entire_issue','1');
      fd.set('issue_id',issueId);
      fetch('issue-reporter.php',{method:'POST',body:fd})
        .then(r=>r.text())
        .then(res=>{
          if(res.includes('issue_deleted_ok')){
            showToast('נמחק בהצלחה','success');
            setTimeout(()=>location.reload(),600);
          } else {
            showToast(res,'danger');
          }
        })
        .catch(err=>showToast(err,'danger'));
    });
  });

  // Delete image
  document.querySelectorAll('.delete-image-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      if(!confirm('מחק תמונה זו?'))return;
      const row = btn.closest('tr');
      const issueId = row.dataset.issueId;
      const imgName = btn.dataset.image;
      const fd = new FormData();
      fd.set('delete_image','1');
      fd.set('issue_id',issueId);
      fd.set('image_name',imgName);
      fetch('issue-reporter.php',{method:'POST',body:fd})
        .then(r=>r.text())
        .then(res=>{
          if(res==='OK'){
            showToast('תמונה נמחקה','success');
            setTimeout(()=>location.reload(),600);
          } else {
            showToast(res,'danger');
          }
        })
        .catch(err=>showToast(err,'danger'));
    });
  });

  // Inline editing (type, status, assigned)
  document.querySelectorAll('.inline-edit-issue-form').forEach(frm=>{
    frm.addEventListener('change',(e)=>{
      e.preventDefault();
      const issueId = frm.querySelector('input[name="issue_id"]')?.value||'';
      if(!issueId)return;
      const row = frm.closest('tr');
      if(!row)return;

      // Gather the 3 selects from this row
      const typeSelect = row.querySelector('.type-select');
      const statusSelect = row.querySelector('.status-select');
      const assignedSelect = row.querySelector('.assigned-select');
      if(!typeSelect || !statusSelect || !assignedSelect) return;

      const newType = typeSelect.value;
      const newStatus = statusSelect.value;
      const newAssigned = assignedSelect.value;

      // Build a single FormData
      const fd = new FormData();
      fd.set('inline_update','edit_issue');
      fd.set('issue_id',issueId);
      fd.set('new_type',newType);
      fd.set('new_status',newStatus);
      fd.set('new_assigned',newAssigned);

      fetch('issue-reporter.php',{method:'POST',body:fd})
        .then(r=>r.text())
        .then(res=>{
          if(res.includes('issue_edited_ok')){
            showToast('עודכן בהצלחה','success');
            setTimeout(()=>location.reload(),500);
          } else {
            showToast(res,'danger');
          }
        })
        .catch(err=>showToast(err,'danger'));
    });
  });

  // Tab switching
  const navLinks = document.querySelectorAll('.nav-link[data-tab-type]');
  const tabPanes = document.querySelectorAll('.tab-pane');
  function showTabPane(typeKey){
    navLinks.forEach(ln=>ln.classList.remove('active'));
    tabPanes.forEach(tp=>tp.classList.remove('active'));
    const foundLink = document.querySelector(`.nav-link[data-tab-type="${typeKey}"]`);
    if(foundLink) foundLink.classList.add('active');
    const foundPane = document.querySelector(`.tab-pane[data-type="${typeKey}"]`);
    if(foundPane) foundPane.classList.add('active');
  }
  navLinks.forEach(ln=>{
    ln.addEventListener('click',()=>{
      const tk = ln.getAttribute('data-tab-type');
      showTabPane(tk);
    });
  });

  // Default tab => My tasks
  showTabPane("_my_");

  // Image overlay
  const imageOverlay = document.getElementById('imageOverlay');
  const imageOverlayImg = document.getElementById('imageOverlayImg');
  const closeOverlayBtn = document.getElementById('closeOverlayBtn');
  document.querySelectorAll('.issue-img-thumb').forEach(img=>{
    img.addEventListener('click',()=>{
      imageOverlayImg.src = img.src;
      imageOverlay.style.display='flex';
      imageOverlay.classList.add('open');
    });
  });
  closeOverlayBtn.addEventListener('click',()=>closeImageOverlay());
  imageOverlay.addEventListener('click',(e)=>{
    if(e.target===imageOverlay) closeImageOverlay();
  });
  function closeImageOverlay(){
    imageOverlay.classList.remove('open');
    imageOverlay.style.display='none';
    imageOverlayImg.src='';
  }

  // Sorting by date_reported
  let sortAsc = false;
  document.querySelectorAll('th.sortable[data-sort-key="date_reported"]').forEach(th=>{
    th.addEventListener('click',()=>{
      sortAsc=!sortAsc;
      doSortingByDate(sortAsc);
    });
  });
  function doSortingByDate(asc){
    const activeTabPane = document.querySelector('.tab-pane.active');
    if(!activeTabPane)return;
    const containers = activeTabPane.querySelectorAll('.issue-table-container');
    containers.forEach(cont=>{
      const tbody = cont.querySelector('tbody');
      if(!tbody)return;
      let rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort((rA,rB)=>{
        const da = rA.dataset.datereported||'';
        const db = rB.dataset.datereported||'';
        return asc ? da.localeCompare(db) : db.localeCompare(da);
      });
      rows.forEach(r=>tbody.appendChild(r));
    });
  }

  // Filter logic
  const allRows = document.querySelectorAll('tbody tr');
  let currentUserFilter = 'All';
  let currentRange = 'all';
  const userFilterBtn = document.getElementById('userFilterBtn');
  const userFilterMenu = document.getElementById('userFilterMenu');
  const dateRangeBtn = document.getElementById('dateRangeBtn');
  const dateRangeMenu = document.getElementById('dateRangeMenu');
  const idFilterInput = document.getElementById('idFilterInput');

  // Build user list
  const setU = new Set(['All']);
  allRows.forEach(r => setU.add(r.dataset.username||''));
  const userList = [...setU];

  userFilterMenu.innerHTML = `
    <li class="px-2 py-2">
      <input type="text" class="form-control" id="userSearchInput" placeholder="חפש משתמש...">
    </li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item user-item" href="#" data-value="All">הכל</a></li>
    ${
      userList
        .filter(u=>u!=='All')
        .map(u=>`<li><a class="dropdown-item user-item" href="#" data-value="${u}">${u}</a></li>`)
        .join('')
    }
  `;
  userFilterMenu.querySelectorAll('.user-item').forEach(a=>{
    a.addEventListener('click',evt=>{
      evt.preventDefault();
      currentUserFilter=a.dataset.value;
      userFilterBtn.textContent='משתמש: '+(currentUserFilter==='All'?'הכל':currentUserFilter);
      applyFilters();
    });
  });
  const userSearchInput = document.getElementById('userSearchInput');
  if(userSearchInput){
    userSearchInput.addEventListener('input',()=>{
      const txt = userSearchInput.value.toLowerCase();
      userFilterMenu.querySelectorAll('.user-item').forEach(x=>{
        const v = x.dataset.value.toLowerCase();
        x.parentElement.style.display = (v.includes(txt) || !txt) ? '' : 'none';
      });
    });
  }

  dateRangeMenu.querySelectorAll('a.dropdown-item').forEach(a=>{
    a.addEventListener('click',evt=>{
      evt.preventDefault();
      currentRange = a.dataset.value;
      dateRangeBtn.textContent='סינון זמן: '+range2label(currentRange);
      applyFilters();
    });
  });
  function range2label(v){
    switch(v){
      case 'today':return 'היום';
      case 'week':return 'השבוע';
      case 'month':return 'החודש';
      default:return 'הכל';
    }
  }
  idFilterInput.addEventListener('input',applyFilters);

  function applyFilters(){
    const now = new Date();
    allRows.forEach(r=>{
      let show = true;
      if(currentUserFilter!=='All' && r.dataset.username!==currentUserFilter) show=false;
      if(show && currentRange!=='all'){
        const dtStr = r.dataset.datereported||'';
        if(!dtStr) {
          show=false;
        } else {
          const dt = new Date(dtStr.replace(/-/g,'/'));
          if(isNaN(dt.getTime())) {
            show=false;
          } else {
            if(currentRange==='today'){
              if(dt.toDateString()!==now.toDateString()) show=false;
            } else if(currentRange==='week'){
              const diff = now - dt;
              if(diff>7*24*3600*1000) show=false;
            } else if(currentRange==='month'){
              if(dt.getMonth()!==now.getMonth()||dt.getFullYear()!==now.getFullYear()) show=false;
            }
          }
        }
      }
      const issueIdTxt = r.querySelector('td')?.textContent.trim()||'';
      const filterId = idFilterInput.value.trim();
      if(show && filterId){
        if(!issueIdTxt.includes(filterId)) show=false;
      }
      r.style.display = show?'':'none';
    });
  }

  // highlight assigned to me
  allRows.forEach(row=>{
    if(row.dataset.assignedAdmin===CURRENT_ADMIN_USER){
      row.classList.add('highlight-assigned');
    }
  });

  // Polling to detect changes in issues.json
  let lastModified = null;
  function checkIssuesFileUpdate(){
    fetch('issues.json',{method:'HEAD'})
      .then(res=>{
        const lm = res.headers.get('Last-Modified');
        if(lastModified && lm && lm!==lastModified){
          location.reload();
        } else {
          lastModified=lm;
        }
      })
      .catch(err=>console.log('Error checking issues.json',err));
  }
  setInterval(checkIssuesFileUpdate,5000);
  checkIssuesFileUpdate();

});
</script>
</body>
</html>
