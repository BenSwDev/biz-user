<?php
function hdate($date){
    return typemap(implode('-', array_reverse(explode('/', trim($date)))), 'date');
}

function db2dateD($date){
    return db2date($date, '.');
}

/**
 * @var TfusaBaseUser $_CURRENT_USER
 */
$sid = intval($_GET['sid']) ?: $_CURRENT_USER->select_site();
if($sid && !$_CURRENT_USER->has($sid)){
    echo 'Access denied';
    return;
}

$title = "רשימת לקוחות";

if($_GET['from']){
    $timeFrom = typemap(implode('-',array_reverse(explode('/',trim($_GET['from'])))),"date");
}else{
    $timeFrom = '2001-01-01';
    $_GET['from'] = implode('/',array_reverse(explode('-',trim($timeFrom))));
}

if($_GET['to']){
    $timeUntil = typemap(implode('-',array_reverse(explode('/',trim($_GET['to'])))),"date");
}else{
    $timeUntil = date("Y-m-t");
    $_GET['to'] = implode('/',array_reverse(explode('-',trim($timeUntil))));
}

$canDup = true;
$where = ["c.siteID IN (" . ($sid ?: $_CURRENT_USER->sites(true)) . ")"];

//$clients = udb::key_row("SELECT crm_clients.*, sites.siteName FROM `crm_clients` INNER JOIN `sites` USING(`siteID`) WHERE crm_clients.siteID IN (" . $_CURRENT_USER->sites(true) . ") ORDER BY `clientID` DESC", 'clientID');

if(!$_GET['sort'] || !in_array($_GET['sort'], ['ASC', 'DESC']))
    $_GET['sort'] = "DESC";

if ($_GET['source'] == 'health2' || $_GET['source'] == 'health'){
    $where[] = "c.source = 'health'";

    if ($_GET['source'] == 'health2')
        $canDup = false;
}
elseif ($_GET['source'])
    $where[] = "c.source = '" . udb::escape_string(typemap($_GET['source'], 'string')) . "'";

if ($_GET['ads'] > 0)
    $where[] = "c.allowAds = 1";
elseif ($_GET['ads'] < 0)
    $where[] = "c.allowAds = 0";

if ($_GET['sfld'] == 'phone'){
    $sorter = 'c.clientMobile ' . udb::escape_string($_GET['sort']);
}
elseif ($_GET['timeType'] == 'u'){
    $where[] = "c.updateTime BETWEEN '" . udb::escape_string($timeFrom) . "' AND '" . udb::escape_string($timeUntil) . "'";
    $sorter  = 'c.updateTime ' . udb::escape_string($_GET['sort']);
}
else {
    $where[] = "c.createTime BETWEEN '" . udb::escape_string($timeFrom) . "' AND '" . udb::escape_string($timeUntil) . "'";
    $sorter  = 'c.createTime ' . udb::escape_string($_GET['sort']);
}

if ($freeText = udb::escape_string(typemap($_GET['free'] ?? '', 'string'))){
    if (is_numeric($freeText))
        $list = ['c.clientEmail', 'c.clientPhone', 'c.clientMobile', 'c.clientPassport', 'c.clientID'];
    else
        $list = ['c.clientName', 'c.clientEmail'];

    $where[] = "(" . implode(" LIKE '%" . $freeText . "%' OR ", $list) . " LIKE '%" . $freeText . "%')";
}


$pager = new UserPager();
$pager->setPage(300);

$dups = $canDup ? udb::key_value("SELECT `clientMobile`, COUNT(*) AS `cnt` FROM `crm_clients` AS `c` WHERE " . implode(' AND ', $where) . " GROUP BY `clientMobile` HAVING `cnt` > 1 ORDER BY NULL") : [];

$clients = udb::key_row("SELECT SQL_CALC_FOUND_ROWS c.*, sites.siteName,settlements.`TITLE` AS clientCity FROM `crm_clients` AS `c` INNER JOIN `sites` USING(`siteID`) LEFT JOIN settlements ON(c.settlementID = settlements.settlementID) WHERE " . implode(" AND ", $where) . ($canDup ? "" : " GROUP BY c.clientMobile ") . " ORDER BY " . $sorter . $pager->sqlLimit(), 'clientID');

$totalCnt = udb::single_value("SELECT FOUND_ROWS()");

$pager->setTotal($totalCnt);

//$payTypeSums = [];
//
//if ($orders){
//    $pays = udb::key_row("SELECT `orderID`, SUM(`sum`) AS `total`, GROUP_CONCAT(CONCAT_WS('-', `payType`, `provider`) SEPARATOR '|') AS `ppType` FROM `orderPayments` WHERE `complete` = 1 AND `cancelled` = 0 AND `subType` <> 'card_test' AND `orderID` IN (" . implode(',', array_keys($orders)) . ") GROUP BY `orderID` ORDER BY NULL", 'orderID');
//    foreach($orders as $id => &$order){
//        $order['paid'] = $pays[$id]['total'] ?? 0;
//        $order['ppType'] = $pays[$id]['ppType'] ?? '';
//    }
//    unset($pays, $order);
//
//    $que = "SELECT CONCAT_WS('-', `payType`, `provider`) AS `pt`, SUM(`sum`) AS `total`, COUNT(*) AS `cnt`
//            FROM `orderPayments`
//            WHERE `complete` = 1 AND `cancelled` = 0 AND `subType` <> 'card_test' AND `orderID` IN (" . implode(',', array_keys($orders)) . ")
//            GROUP BY CONCAT_WS('-', `payType`, `provider`)
//            ORDER BY NULL";
//    $payTypeSums = udb::key_row($que, 'pt');
//}


//include "partials/orders_menu.php"; 
?>

<div class="searchOrder">
	<div class="ttl" style="cursor:pointer;margin:-10px;padding:10px" onclick="$('#searchForm').toggleClass('hide');">חפש לקוח</div>
	<form method="GET" autocomplete="off" action="" class="hide"  id="searchForm">
		<input type="hidden" name="page" value="clients" />
        <input type="hidden" name="sfld" value="" />

<?php
    if (!$_CURRENT_USER->single_site){
        $sname = udb::key_value("SELECT `siteID`, `siteName` FROM `sites` WHERE `siteID` IN (" . $_CURRENT_USER->sites(true) . ")");
?>
        <div class="inputWrap">
            <select name="sid" id="sid" title="שם מתחם">
                <option value="0">כל המתחמים</option>
<?php
        foreach($sname as $id => $name)
            echo '<option value="' , $id , '" ' , ($id == $sid ? 'selected' : '') , '>' , $name , '</option>';
?>
            </select>
		</div>
<?php
    }
?>
        <div class="inputWrap">
            <select name="timeType" id="otype" title="">
                <option value="c">לפי תאריך הוספה</option>
                <option value="u" <?=($_GET['timeType'] == 'u' ? 'selected' : '')?>>לפי תאריך עדכון</option>
            </select>
        </div>
		<div class="inputWrap">
			<input type="text" name="from" placeholder="מתאריך" class="searchFrom" value="<?=typemap($_GET['from'], 'string')?>" readonly>
		</div>
		<div class="inputWrap">
			<input type="text" name="to" placeholder="עד לתאריך" value="<?=typemap($_GET['to'], 'string')?>" class="searchTo" readonly>
		</div>

        <div class="inputWrap">
            <select name="source" id="source" title="">
                <option value="">כל המקורות</option>
                <option value="treatment" <?=($_GET['source'] == 'treatment' ? 'selected' : '')?>>מטיפולים</option>
                <option value="order" <?=($_GET['source'] == 'order' ? 'selected' : '')?>>מהזמנות</option>
                <option value="health" <?=($_GET['source'] == 'health' ? 'selected' : '')?>>מהצהרות בריאות</option>
                <option value="health2" <?=($_GET['source'] == 'health2' ? 'selected' : '')?>>מהצהרות בריאות (ללא כפולים)</option>
            </select>
        </div>
        <div class="inputWrap">
            <select name="ads" id="ads" title="">
                <option value="">כל הלקוחות</option>
                <option value="1" <?=($_GET['ads'] > 0 ? 'selected' : '')?>>רק עם דיוור מאושר</option>
                <option value="-1" <?=($_GET['ads'] < 0 ? 'selected' : '')?>>רק בלי דיוור מאושר</option>
            </select>
        </div>
		<div class="inputWrap">
            <select name="sort" id="sort" title="">
                <option value="ASC">תאריך רחוק לקרוב</option>
                <option value="DESC" <?=($_GET['sort'] == 'DESC' ? 'selected' : '')?>>תאריך קרוב לרחוק</option>
            </select>
		</div>
        <div class="inputWrap">
            <input type="text" name="free" placeholder="חיפוש חופשי" value="<?=$freeText?>" />
        </div>

		<a class="clear" href="?page=<?=typemap($_GET['page'], 'string')?>">נקה</a>
		<input type="submit" value="חפש">
	</form>
</div>

<style>
.item.order.isSpa td.f,.item.order.isSpa td.c {direction:ltr; text-align:right}
.item.order.isSpa td.f.rtl, .item.order.isSpa td.c.rtl {direction:rtl}

.orders_num.o-ctrl {
    background: #c9f2fd;
}

.orders_num {
    cursor: pointer;
    position: relative;
}

.orders_num.o-ctrl::after {
    opacity: 1;
}

.orders_num.o-up::after {
    opacity: 0.2;
}
.orders_num::after {
    content: "";
    width: 6px;
    height: 6px;
    box-sizing: border-box;
    border-left: 2px black solid;
    border-bottom: 2px black solid;
    display: block;
    position: absolute;
    bottom: 0;
    margin: 0 auto;
    left: 0;
    right: 0;
    transform: rotate(-45deg);
    opacity: 0;
}

.orders_num.o-down::after {
    opacity: 0.5;
    transform: rotate(135deg);
}
.excel {
    line-height: 44px;
    margin: 10px 5px;
    display: inline-block;
    font-size: 16px;
    color: #0dabb6;
    background: white;
    border: 1px
    #0dabb6 solid;
    padding: 0 10px;
    cursor: pointer;
    border-radius: 10px;
}

</style>
<section class="orders" style="max-width:none">
	<div class="last-orders">
		<div class="title"><?=$title?>
			<!-- div id="btns-reports" class="btns">
				<div id="b-makor" onclick="show_makor()">מקורות הגעה</div>
				<div id="b-paytypes" onclick="show_paytypes()">אמצעי תשלום</div>
				<div id="b-agents" onclick="show_agents()">סוכנים</div>
				<div id="b-all" onclick="show_all()" class='active'>מקיף</div>
			</div -->
		</div>
        <div class="excel" id="expExcel" onclick="window.location.href+='&exp=excel'">ייצוא לאקסל</div>

		<div class="AAAreport-container">
            <?php echo $pager->render() ?>
			<table class="reports" id="reports">
				<thead>
				<tr class='sticky top'>
					<th>#</th>
					<th>שם מלא</th>
                    <th>ת.ז.</th>
					<th class="sortable <?=($_GET['sfld'] == 'phone' ? $_GET['sort'] : '')?>" data-sort-by="phone">טלפון</th>
                    <!-- th>טלפון נוסף</th -->
                    <th>דוא"ל</th>
                    <th>עיר</th>
                    <th>כתובת</th>
                    <th>דיוור</th>
                    <th>תאריך לידה</th>
					<th>תאריך הוספה</th>
                    <th>עדכון אחרון</th>
                    <?=($sid ? '' : '<th>שם העסק</th>')?>
                    <th>&nbsp;</th>
				</tr>
				</thead>
				<tbody>

<?php
//        UserUtilsNew::init();
//
//        $mcache = [];
//		$cuponTypes = UserUtilsNew::$CouponsfullList;
//		$cuponTypes['online'] = 'הזמנה אונליין';
        foreach($clients as $client) {
//            $que = "SELECT orders.timeFrom, orders.therapistID, orders.treatmentLen, orders.price FROM `orders` WHERE orders.orderID <> orders.parentOrder AND `parentOrder` = " . $client['parentOrder'];
//            $tipulim = udb::single_list($que);
//
//            $totalPay = 0;
//            foreach($tipulim as $sub){
//                $mcache[$sub['therapistID']] = $master = $mcache[$sub['therapistID']] ?? new MasterPay($sub['therapistID']);
//
//                $pay = $master->get_treat_pay($sub['timeFrom'], $sub['treatmentLen'], $sub['price']);
//                $totalPay += $pay['total'];
//            }
//
//            $extraCost = 0;
//            if ($client['extras']){
//                $tmp = json_decode($client['extras'], true);
//                $extraCost = $tmp['total'];
//            }
//            $payTypes = explode('|', $client['ppType']);
//
//            $payTypes = $payTypes ? implode('<br />', array_unique(array_map(function ($str){return  UserUtilsNew::method(...explode('-', $str));}, $payTypes))) : '';
?>
				<tr class="item order isSpa" data-cid="<?=$client['clientID']?>" data-sid="<?=$client['siteID']?>" <?=($dups[$client['clientMobile']] ? 'style="background-color:#FDD"' : '')?>>
                    <td class="c"><?=$client['clientID']?></td>
					<td class="c rtl"><?=$client['clientName']?></td>
                    <td class="c"><?=$client['clientPassport']?></td>
					<td class="c"><?=$client['clientMobile']?></td>
                    <!-- td class="c"><?=$client['clientPhone']?></td -->
                    <td class="c"><?=$client['clientEmail']?></td>
                    <td class="c"><?=$client['clientCity']?></td>
                    <td class="c"><?=$client['clientAddress']?></td>
                    <td style="text-align:center"><?=($client['allowAds'] ? '<span style="color:green">כן</span>' : '<span style="color:red">לא</span>')?></td>
					<td class="c"><?=date('d.m.y', strtotime($client['clientBirthday']))?></td>
					<td class="c"><?=date('d.m.y', strtotime($client['createTime']))?></td>
					<td class="c"><?=date('d.m.y', strtotime($client['updateTime']))?></td>
                    <?=($sid ? '' : '<td class="c">' . $client['siteName'] . '</td>')?>
                    <td><img class="del" src="/user/assets/img/X.jpg" width="15" height="15" style="margin:auto" alt="מחק לקוח" /></td>
<? /*                    
					<td class="c"><?=implode('<br />', array_map('db2dateD', explode('#', $client['treatDates'])))?></td>
					<td class="c"><?=number_format($client['countTreatments'])?></td>
					<td class="c"><?=number_format($client['treatCost'])?></td>
                    <td class="c"><?=number_format($totalPay)?></td>
					<td class="c"><?=number_format($client['treatCost'] - $totalPay)?></td>
                    <td class="c"><?=number_format($client['roomCost']) ?></td>
					<td class="c"><?=number_format($extraCost)?></td>
                    <td class="c"><?=number_format($client['price'] + $client['discount'])?></td>
                    <td class="c"><?=number_format($client['discount'])?></td>
					<td class="c number"><?=number_format($client['price'])?></td>
					<td class="c number"><?=number_format($client['paid'])?></td>
					<td class="c number"><?=number_format($client['price'] - $client['paid'])?></td>
                    <td><?=$client['userName']?></td>
                    <td><?=$client['sourceID']? $cuponTypes[$client['sourceID']] : "הזמנה רגילה"?></td>
                    <td><?=$payTypes?><?//print_r($payTypesIDs)?></td> */ ?>
				</tr>
				
				<?

//				/*****************************Total Lines Create **********************************/
//				$totalOrders ++;
//				$totalTreatCost += $order['treatCost'];
//				$totalpayAll += $totalPay;
//				$totalTreatIncome += $order['treatCost'] - $totalPay;
//				$totalExtraIncome += $order['roomCost'] + $extraCost;
//				$totalRooms += $order['roomCost'];
//				$totalExtras += $extraCost;
//				$totalMechiron += $order['treatCost'] + $order['roomCost'] + $extraCost;
//				$totalDiscount += $order['treatCost'] + $order['roomCost'] + $extraCost - $order['price'];
//				$totalPrice +=$order['price'];
//				$totalBalance += ($order['price'] - $order['paid']);
//				$totalPaid +=$order['paid'];
//				$totalTreatments += $order['countTreatments'];
//
//				/***********************************************************************************/
//
//				/***************************** Total Agents Lines Create **********************************/
//
//				$a_name[$order['agent_buserID']] = $order['userName'];
//				$a_totalOrders[$order['agent_buserID']] ++;
//				$a_totalTreatCost[$order['agent_buserID']] += $order['treatCost'];
//				$a_totalpayAll[$order['agent_buserID']] += $totalPay;
//				$a_totalTreatIncome[$order['agent_buserID']] += $order['treatCost'] - $totalPay;
//				$a_totalExtraIncome[$order['agent_buserID']] += $order['roomCost'] + $extraCost;
//				$a_totalRooms[$order['agent_buserID']] += $order['roomCost'];
//				$a_totalExtras[$order['agent_buserID']] += $extraCost;
//				$a_totalMechiron[$order['agent_buserID']] += $order['treatCost'] + $order['roomCost'] + $extraCost;
//				$a_totalDiscount[$order['agent_buserID']] += $order['treatCost'] + $order['roomCost'] + $extraCost - $order['price'];
//				$a_totalPrice[$order['agent_buserID']] +=$order['price'];
//				$a_totalBalance[$order['agent_buserID']] += ($order['price'] - $order['paid']);
//				$a_totalPaid[$order['agent_buserID']] +=$order['paid'];
//				$a_totalTreatments[$order['agent_buserID']] += $order['countTreatments'];
//
//
//
//				/***********************************************************************************/
//
//				/***************************** Total payType Lines Create **********************************/
//
//
//
//				/***********************************************************************************/
//
//
//
//
//				/***************************** Total Source Lines Create **********************************/
//
//				$m_name[$order['sourceID']] = $order['sourceID']? $cuponTypes[$order['sourceID']] : "הזמנה רגילה";
//				$m_totalOrders[$order['sourceID']] ++;
//				$m_totalTreatCost[$order['sourceID']] += $order['treatCost'];
//				$m_totalpayAll[$order['sourceID']] += $totalPay;
//				$m_totalTreatIncome[$order['sourceID']] += $order['treatCost'] - $totalPay;
//				$m_totalExtraIncome[$order['sourceID']] += $order['roomCost'] + $extraCost;
//				$m_totalRooms[$order['sourceID']] += $order['roomCost'];
//				$m_totalExtras[$order['sourceID']] += $extraCost;
//				$m_totalMechiron[$order['sourceID']] += $order['treatCost'] + $order['roomCost'] + $extraCost;
//				$m_totalDiscount[$order['sourceID']] += $order['treatCost'] + $order['roomCost'] + $extraCost - $order['price'];
//				$m_totalPrice[$order['sourceID']] +=$order['price'];
//				$m_totalBalance[$order['sourceID']] += ($order['price'] - $order['paid']);
//				$m_totalPaid[$order['sourceID']] +=$order['paid'];
//				$m_totalTreatments[$order['sourceID']] += $order['countTreatments'];
//
//
//				/***********************************************************************************/
//
//
//


        }

		
/*        foreach($a_name as $key =>  $name ){
?>
				<tr class='agents' data-userid="<?=$key?>">
					<td><b><?=$name?></b><br><?=$a_totalOrders[$key]?> הזמנות</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=number_format($a_totalTreatments[$key]);?></td>
					<td>₪<?=number_format($a_totalTreatCost[$key]);?><div>מחיר ממוצע ₪<?=number_format(($a_totalTreatCost[$key]/$a_totalTreatments[$key]),2);?></div></td>
					<td>₪<?=number_format($a_totalpayAll[$key]);?></td>
					<td>₪<?=number_format($a_totalTreatIncome[$key]);?></td>
					<td>₪<?=number_format($a_totalRooms[$key]);?></td>
					<td>₪<?=number_format($a_totalExtras[$key]);?></td>
                    <td>₪<?=number_format($a_totalMechiron[$key]);?></td>
                    <td>₪<?=number_format($a_totalDiscount[$key]);?></td>
					<td>₪<?=number_format($a_totalPrice[$key])?><div>מחיר ממוצע ₪<?=number_format(($a_totalPrice[$key]/$a_totalOrders[$key]),2);?></div><div></div></td>
					<td>₪<?=number_format($a_totalPaid[$key])?></td>
					<td>₪<?=number_format($a_totalBalance[$key])?></td>
                    <td><?=$name?></td>
                    <td></td>
                    <td></td>
                </tr>
<?php
        }

		foreach($m_name as $key =>  $name ){
?>
				<tr class='makor' data-makorid="<?=$key?>">
					<td><b><?=$name?></b><br><?=$m_totalOrders[$key]?> הזמנות</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=number_format($m_totalTreatments[$key]);?></td>
					<td>₪<?=number_format($m_totalTreatCost[$key]);?><div>מחיר ממוצע ₪<?=number_format(($m_totalTreatCost[$key]/$m_totalTreatments[$key]),2);?></div></td>
					<td>₪<?=number_format($m_totalpayAll[$key]);?></td>
					<td>₪<?=number_format($m_totalTreatIncome[$key]);?></td>
					<td>₪<?=number_format($m_totalRooms[$key]);?></td>
					<td>₪<?=number_format($m_totalExtras[$key]);?></td>
                    <td>₪<?=number_format($m_totalMechiron[$key]);?></td>
                    <td>₪<?=number_format($m_totalDiscount[$key]);?></td>
					<td>₪<?=number_format($m_totalPrice[$key])?><div>מחיר ממוצע ₪<?=number_format(($m_totalPrice[$key]/$m_totalOrders[$key]),2);?></div><div></div></td>
					<td>₪<?=number_format($m_totalPaid[$key])?></td>
					<td>₪<?=number_format($m_totalBalance[$key])?></td>
                    <td></td>
                    <td><?=$name?></td>
                    <td></td>
                </tr>
<?php
        }

        foreach($payTypeSums as $pt => $prow ){
?>
				<tr class='payTypes' data-paytype="<?=$pt?>">
					<td><b><?=(UserUtilsNew::method(...explode('-', $pt)) ?: $pt)?></b><br><?=$prow['cnt']?> הזמנות</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
                    <td></td>
                    <td></td>
					<td></td>
					<td>₪<?=number_format($prow['total'])?></td>
					<td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
<?php
        }*/

?>
				<tr class='sticky bottom total' id="totalall">
					<td>סה"כ: </td>
					<td colspan="15"><?=$totalCnt?> לקוחות</td>

<? /*					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?=number_format($totalTreatments);?></td>
					<td>₪<?=number_format($totalTreatCost);?><div>מחיר ממוצע ₪<?=$totalTreatments? number_format(($totalTreatCost/$totalTreatments),2) : "";?></div></td>
					<td>₪<?=number_format($totalpayAll);?></td>
					<td>₪<?=number_format($totalTreatIncome);?></td>
					<td>₪<?=number_format($totalRooms);?></td>
					<td>₪<?=number_format($totalExtras);?></td>
                    <td>₪<?=number_format($totalMechiron);?></td>
                    <td>₪<?=number_format($totalDiscount);?></td>
					<td>₪<?=number_format($totalPrice)?><div>מחיר ממוצע ₪<?=$totalTreatments? number_format(($totalPrice/$totalTreatments),2) : "";?></div></td>
					<td>₪<?=number_format($totalPaid)?></td>
					<td>₪<?=number_format($totalBalance)?></td>
                    <td></td>
                    <td></td>
                    <td></td> */ ?>
                </tr>
			</table>
		</div>
	</div>
</section>

<style>
.report-container{max-height:calc(100vh - 250px);overflow:auto;padding-left:20px;clear:both;position:relative;top:10px}
.reports{font-size:14px;border-collapse:collapse}
.reports td, .reports th{padding:5px;vertical-align:middle;border:1px #ccc solid;text-align:right}
.reports td.number{direction:ltr}
.reports .sticky{position:sticky;background:white;}
.reports .sticky.top{top:-20px}
.reports .sticky.bottom{bottom:-20px}
.reports .bottom td{font-weight:bold}
.reports tr:hover{background:#cfeef0;cursor:pointer}
.reports .agents{display:none}
.reports .makor{display:none}
.reports .payTypes{display:none}
.reports .total{display:table-row }
.reports td > div{font-size:12px;font-weight:normal}
.last-orders .btns {float: left;}
.last-orders .btns > div {display: inline-block;font-size: 16px;line-height: 34px;padding: 0 20px;margin: 0 2px;border: 1px #0dabb6 solid;color: #999;font-weight: normal;background: white;border-radius: 10px;cursor: pointer;}
.last-orders .btns > div.active {color: white;background: #0dabb6;}
.ASC {background:url('/user/assets/img/arrow-up.png') no-repeat left center;}
.DESC {background:url('/user/assets/img/arrow-down.png') no-repeat left center;}
</style>

<script>


//$('.orders_num').click(function(){
//    var table = $(this).parents('table').eq(0)
//    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
//    this.asc = !this.asc
//    $(".orders_num").removeClass("o-ctrl");
//	$(this).addClass('o-ctrl');
//	if (!this.asc){
//		rows = rows.reverse();
//		$(this).removeClass('o-up');
//		$(this).addClass('o-down');
//	}else{
//		$(this).addClass('o-up');
//		$(this).removeClass('o-down');
//	}
//    for (var i = 0; i < rows.length; i++){table.append(rows[i])}
//});

function comparer(index) {
    return function(a, b) {
        var valA = getCellValue(a, index).replace(/[^\d.-]/g, ''), valB = getCellValue(b, index).replace(/[^\d.-]/g, '')
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}
function getCellValue(row, index){ return $(row).children('td').eq(index).text() }


$('.reports .agents td').click(function(){
	//debugger;
	var uid = $(this).parent().data('userid');
	$('.reports tbody tr').css('display','none');
	$(".reports tr[data-userid='"+uid+"']").css('display','table-row');
	$(this).parent().addClass('sticky bottom');
});

$('.reports .makor td').click(function(){
	//debugger;
	var uid = $(this).parent().data('makorid');
	$('.reports tbody tr').css('display','none');
	$(".reports tr[data-makorid='"+uid+"']").css('display','table-row');
	$(this).parent().addClass('sticky bottom');
});

$('.reports .payTypes td').click(function(){
	
	var pid = $(this).parent().data('paytype');
	$('.reports tbody tr').css('display','none');	
	$(this).parent().css('display','table-row');
	$(this).parent().addClass('sticky bottom');
	$(".reports tbody tr").each(function(){
		//debugger;
		var ptypes = $(this).data('paytypes');
		if (ptypes.indexOf(pid) >= 0){			
			$(this).css('display','table-row');
		}
	})
	
});

$(function(){
    $('.sortable').on('click', function(){
        let dir = $(this).hasClass('ASC') ? 'DESC' : 'ASC', fld = $(this).data('sort-by');

        $('input[name="sfld"]').val(fld);
        $('#sort').val(dir);

        $('#searchForm').find('input[type="submit"]').click();
    });

    $("img.del").on('click', function(){
        let data = $(this).closest('tr').data();

        if (data.cid && data.sid)
            Swal.fire({
                title: 'בטוח?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'מחק',
                cancelButtonText: 'בטל'
            }).then((result) => {
                if (result.isConfirmed)
                    $.post('ajax_client.php', {act:'delete', sid:data.sid, cid:data.cid}).then(() =>  $(this).closest('tr').remove());
            });
        else
            Swal.fire('Error', 'Cannot delete this client', 'error');
    });

    $('#expExcel').on('click', function(){
        window.location.href = 'ajax_excel_clients.php' + window.location.search;
    });
});

//function show_agents(){
//
//	$('.reports tbody tr:not(#totalall)').removeClass('sticky bottom')
//	$('.reports tbody tr').css('display','none');
//	$(".reports tbody .agents").css('display','table-row');
//	$("#totalall").css('display','table-row');
//	$("#btns-reports > div").removeClass('active');
//	$("#b-agents").addClass('active');
//
//
//}
//
//function show_makor(){
//
//	$('.reports tbody tr:not(#totalall)').removeClass('sticky bottom')
//	$('.reports tbody tr').css('display','none');
//	$(".reports tbody .makor").css('display','table-row');
//	$("#totalall").css('display','table-row');
//	$("#btns-reports > div").removeClass('active');
//	$("#b-makor").addClass('active');
//
//
//}
//
//function show_paytypes(){
//	//debugger;
//	$('.reports tbody tr:not(#totalall)').removeClass('sticky bottom')
//	$('.reports tbody tr').css('display','none');
//	$(".reports tbody .payTypes").css('display','table-row');
//	$("#totalall").css('display','table-row');
//	$("#btns-reports > div").removeClass('active');
//	$("#b-paytypes").addClass('active');
//
//
//}
//
//function show_all(){
//	$('.reports tbody tr:not(#totalall)').removeClass('sticky bottom')
//	$('.reports tbody tr').attr('style','');
//	$("#btns-reports > div").removeClass('active');
//	$("#b-all").addClass('active');
//}

</script>