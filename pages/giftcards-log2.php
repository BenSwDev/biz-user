<?php
/**
 * @var TfusaBaseUser $_CURRENT_USER
 */
if (!$_CURRENT_USER->select_site()){
    $_CURRENT_USER->select_site($_CURRENT_USER->active_site());
    echo '<script>$(function(){$(".sites-select select").val(' , $_CURRENT_USER->active_site() , ');});</script>';
}

$siteID = $_CURRENT_USER->active_site() ?: 0;

$free = typemap($_GET['free'], 'string');
$page = preg_replace('/[^a-z0-9_-]+/i', '', $_GET['page']);

?>
<script src="/user/assets/js/giftcards.js"></script>
<section class="giftcards">
    <div class="title">גיפטקארד רכישות ומימושים</div>


    <div class="searchOrder">
	<div class="ttl">חפש פעולות</div>
	<form method="GET" autocomplete="off" action="">
        <input type="hidden" name="page" value="<?=$_GET['page']?>">
<?
        $createDate = $_GET['createDate'] ? typemap(date2db($_GET['createDate']), 'date') : '';
        $createDateTo = $_GET['createDateTo'] ? typemap(date2db($_GET['createDateTo']), 'date') : '';
        $usingDate = $_GET['usingDate'] ? typemap(date2db($_GET['usingDate']), 'date') : '';
        $usingDateTo = $_GET['usingDateTo'] ? typemap(date2db($_GET['usingDateTo']), 'date') : '';
?>
        <div class="inputWrap half">
                <input type="text" name="createDate" placeholder="תאריך הפקה מ" class="searchFrom" value="<?=($createDate ? db2date($createDate, '/') : '')?>" readonly>
            </div>
            <div class="inputWrap half">
                <input type="text" name="createDateTo" placeholder="תאריך הפקה עד" class="searchFrom" value="<?=($createDateTo ? db2date($createDateTo, '/') : '')?>" readonly>
            </div>
            <div class="inputWrap half">
                <input type="text" name="usingDate" placeholder="תאריך מימוש מ" value="<?=($usingDate ? db2date($usingDate, '/') : '')?>" class="searchTo" readonly>
            </div>
            <div class="inputWrap half">
                <input type="text" name="usingDateTo" placeholder="תאריך מימוש עד" value="<?=($usingDateTo ? db2date($usingDateTo, '/') : '')?>" class="searchTo" readonly>
            </div>
        <div class="inputWrap">
            <select  name="type" >
                <option value="">סוג מימוש</option>
                <option value="1" <?=$_GET['type'] == 1 ? " selected " : ""?> >חלקי</option>
                    <option value="2" <?=$_GET['type'] == 2 ? " selected " : ""?> >מלא</option>
            </select>
        </div>

        <div class="inputWrap">
            <input type="text" name="free" placeholder="חיפוש חופשי" value="<?=htmlspecialchars($_GET['free'])?>" />
        </div>

		<a class="clear" href="?page=<?=$page?>">נקה</a>
		<input type="submit" value="חפש">
		
	</form>	
</div>
    <div style="clear:both;"></div>
    <div class="add-new" style="display: none" onclick="loadGiftCardData(0)">הוסף חדש</div>
    <div class="page-options" style="display: none" onclick="loadGeneralForm()">הגדרות תצוגת עמוד</div>

    <div class="clear"></div>
</section>
<style>
table.giftcards-log{background:#fff;margin:20px auto;text-align:center;box-sizing:border-box}
table.giftcards-log td,table.giftcards-log th{padding:10px;border-bottom:.5px solid #0dabb6;border-left:.5px solid #0dabb6;border-right:.5px solid #0dabb6;border-top:.5px solid #0dabb6;}
.giftcard.gift-pop{position:fixed;top:0;left:0;bottom:0;background:rgba(0,0,0,.6);width:100%;right:0;height:100vh;z-index:9}
.giftcard.gift-pop .gift_container{position:absolute;top:50%;right:50%;transform:translateY(-50%) translateX(50%);width:100%;max-width:800px;background:#e0e0e0;padding:10px;text-align:right;box-sizing:border-box}
.giftcard.gift-pop .gift_inside{background:#fff;box-shadow:0 0 2px rgb(0 0 0 / 60%);position:relative;}
.giftcard.gift-pop .gift_inside>.title{padding:20px;box-sizing:border-box}
.giftcard.gift-pop .gift_container ul {list-style: none;font-size: 0;padding: 10px;box-sizing: border-box;max-height: calc(100vh - 220px);overflow: auto;}
.giftcard.gift-pop .gift_container ul li {display: inline-block;width: 100%;max-width: 33.33%;font-size: 16px;padding-left: 20px;box-sizing: Border-box;}
.giftcard.gift-pop .gift_container ul li>div .title{display:inline-block;width:130px;color:#9e9e9e;padding-bottom:4px}
.giftcard.gift-pop .gift_container ul li>div .con{display:inline-block;width:100%}
.giftcard.gift-pop .gift_container ul li:last-child{padding:0;}
.giftcard.gift-pop.mimush .gift_container ul li:last-child{max-width:66%}
.giftcard.gift-pop .gift_container ul li>div{min-height:30px;margin-bottom:10px}
.giftcard.gift-pop .gift_inside>.close{position:Absolute;top:10px;left:10px;width:20px;height:20px;padding: 4px;box-sizing:border-box;border:1px solid #0dabb6;cursor:pointer;border-radius:20px}
.giftcard.gift-pop .gift_inside>.close svg{width:100%;height:auto;fill:#0dabb6}
.giftcard.gift-pop .gift_inside>hr{height:2px;background:#e0e0e0;display:block;margin-bottom:10px}
.bottom-btns{display:block;text-align:center}
.bottom-btns>div{cursor:pointer;background:#0dabb6;display:inline-block;margin:0 5px 10px 5px;line-height:40px;padding:0 20px;box-sizing:border-box;color:white}
.bottom-btns>div.refund {width:118px; background-color:#e79b14}
.fast-find{background:#0dabb6;margin:20px auto 0;display:block;padding:5px;border:1px solid #0dabb6;border-radius:8px;left:0;right:0;position:relative;box-sizing:border-box;max-width:300px;width:100%}
.fast-find .inputWrap{background:#fff;border-radius:8px;position:relative;height:50px}
.fast-find .inputWrap .submit{position:absolute;top:50%;left:5px;width:45px;height:45px;transform:translateY(-50%);background:#000;fill:#fff;border-radius:50px;padding:12px;box-sizing:border-box;cursor:pointer}
.fast-find .inputWrap input{position:absolute;top:0;right:0;left:0;bottom:0;width:100%;height:100%;border-radius:8px;background:0 0;font-size:18px;padding:6px 15px 0 15px;box-sizing:border-box}
.fast-find .inputWrap input+label{position:absolute;top:0;right:15px;font-size:14px;font-weight:500;color:#0dabb6}


.searchOrder {margin: 20px auto 0;display: block;padding: 13px 30px;border: 1px solid #0dabb6;border-radius: 8px;background: #fff;left: 0;right: 0;position: relative;max-width: 240px;overflow: hidden;}
.searchOrder form {margin-top: 10px;}
.searchOrder form .inputWrap {margin: 5px;}
.searchOrder form .inputWrap input[type=text] {height: 40px;box-sizing: border-box;padding-right: 10px;font-size: 16px;border: 1px #ccc solid;border-radius: 5px;width: 100%;}
.searchOrder form .clear {text-decoration: none;font-size: 16px;display: inline-block;vertical-align: top;background: #fff;color: #0dabb6;border-radius: 5px;margin: 5px;border: 1px #0dabb6 solid;float: right;line-height: 40px;width: 60px;text-align: center;}
.searchOrder form input[type=submit] {display: block;vertical-align: top;background: #0dabb6;color: #fff;border-radius: 5px;cursor: pointer;margin: 5px;width: 100px;float: left;font-size: 20px;line-height: 40px;}
.searchOrder form .inputWrap select {height: 40px;box-sizing: border-box;padding-right: 10px;font-size: 16px;border: 1px #ccc solid;border-radius: 5px;text-align: right;width: 100%;}
.giftcard.gift-pop .gift_container ul li>div .con input {height: 30px;border: 1px #aaa solid;padding: 0 10px;width: 100%;box-sizing: border-box;}
.giftcard.gift-pop.mimush .gift_container {max-width: 570px;}

td.control div.pay {cursor:pointer}
td.control div.pay.refunded, .bottom-btns div.pay.refunded {width: 46px;height: 46px;border-radius: 5px;background-color: #fb4040;color: #fff;position: relative;float: left;margin-right: 5px;display: flex;align-items: center;justify-content: center;text-align: center;font-size: 14px; line-height:18px}

@media (min-width: 992px) {
    .giftcard.gift-pop {max-width:calc(100vw - 300px);right:auto;}
    .bottom-btns>div.refund {margin-right:140px;}
}

@media(max-width:700px){
	.giftcard.gift-pop .gift_container ul li{max-width:100%}
}
</style>
<div class="fast-find">
    <div class="inputWrap">
        <input type="text" name="giftnum" id="giftnum" placeholder="הקלידו מספר שובר">
        <label for="giftnum">איתור שובר מהיר</label>
        <div class="submit" onclick="searchShovar($('#giftnum').val())"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" x="0" y="0" viewBox="0 0 447.2 447.2" xml:space="preserve"><path d="M420.4 192.2c-1.8-0.3-3.7-0.4-5.5-0.4H99.3l6.9-3.2c6.7-3.2 12.8-7.5 18.1-12.8l88.5-88.5c11.7-11.1 13.6-29 4.6-42.4 -10.4-14.3-30.5-17.4-44.7-6.9 -1.2 0.8-2.2 1.8-3.3 2.8l-160 160C-3.1 213.3-3.1 233.5 9.4 246c0 0 0 0 0 0l160 160c12.5 12.5 32.8 12.5 45.3-0.1 1-1 1.9-2 2.7-3.1 9-13.4 7-31.3-4.6-42.4l-88.3-88.6c-4.7-4.7-10.1-8.6-16-11.7l-9.6-4.3h314.2c16.3 0.6 30.7-10.8 33.8-26.9C449.7 211.5 437.8 195.1 420.4 192.2z"></path></svg></div>
    </div>  
</div>
<style>
.giftcards-log thead th{position:sticky;z-index:1;background:white;top:0;box-shadow:0 -1px 1px #333 inset;line-height:1;height:30px}
	.giftcards-log tbody th{position:sticky;z-index:1;background:white;bottom:0;font-weight:bold;height:50px;vertical-align:middle;box-shadow:0 1px 1px #333 inset;line-height:1}
	.usageType1 td, .usageType1{color:#00af00}
	.usageType2 td, .usageType2{color:#028993}
	.usageType3 td, .usageType3{color:#00007c}
    .usageType9 td, .usageType9{color:#e79b14}
	.usageTypes{line-height:1;font-size:12px}
	.usageTypes div{margin-top:3px}

.gift_container .mimushim .del-btn {text-align: left; background: url(/user/assets/img/X.jpg); background-position: right 0; background-repeat: no-repeat; display: inline-block; width: 90px; height: 20px; float: left; cursor: pointer;}
.gift_container .mimushim .del-log {text-align: left; display: inline-block; width: 60px; height: 20px; float: left;}
</style>
<table class="giftcards-log" cellspacing="0">
    <thead>
        <tr>
            <th>מספר</th>
            <th>שם הכרטיס</th>
            <th>מקור</th>
            <th>ת. הפקה</th>
            <th>עלות</th>
            <th>עמלה</th>
            <th>זכאות</th>
            <th>ת.מימוש</th>
            <th>שווי</th>
            <th>מומש</th>
            <th width="80">סטטוס</th>
            <th>תוקף</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<?php
    $terminals = [];

        $sql_sum = "select pID,sum(useageSum) as totalUsage from giftCardsUsage left join gifts_purchases using (pID) left join giftCards on (gifts_purchases.giftCardID = giftCards.giftCardID) where giftCards.siteID in (".$siteID.")  group by pID";
        $sums = udb::key_row($sql_sum,"pID");
        //print_r($sums);
        $sql = "select siteID,giftCardCommission from sites where siteID in(".$siteID.")";
        $coms = udb::key_row($sql,"siteID");

        $where = ["gifts_purchases.terminal = 'direct'"];

        if($_GET['free']){
            $isID = '0';
            if (is_numeric($_GET['free']))
                $isID = 'gifts_purchases.pID = ' . intval($_GET['free']);

            $where[] = " (" . $isID . " OR gifts_purchases.`giftTitle` like '%".udb::escape_string($free)."%' or gifts_purchases.giftSender like '%".udb::escape_string($free)."%' or gifts_purchases.famname like '%".udb::escape_string($free)."%' or gifts_purchases.ordersID like '%".udb::escape_string($free)."%')" ;
        }

        /**** this piece of code is too complicated and WRONG (meaning it throws warnings in Mysql and DOESN'T return correct results) ***/

//        if($_GET['createDate']) {
//            if($_GET['createDateTo']) {
//                $useDate = implode('-',array_reverse(explode('/',trim($_GET['createDate']))));
//                $useDate2 = implode('-',array_reverse(explode('/',trim($_GET['createDateTo']))));
//                $useDate  =  date("Y-m-d",strtotime($useDate));
//                $useDate2  =  date("Y-m-d",strtotime($useDate2));
//                $where[] = " gifts_purchases.transDate BETWEEN  STR_TO_DATE('".$useDate."','%Y-%m-%d') AND STR_TO_DATE('".$useDate2."','%Y-%m-%d')";
//            }
//            else {
//                $useDate = implode('-',array_reverse(explode('/',trim($_GET['createDate']))));
//                $where[] = " STR_TO_DATE(gifts_purchases.transDate,'%Y-%m-%d') >= '".$useDate."'";
//            }
//
//        }
//        else {
//            if($_GET['createDateTo']) {
//                $useDate  =  implode('-',array_reverse(explode('/',trim($_GET['createDateTo']))));
//                $where[] = " STR_TO_DATE(gifts_purchases.transDate,'%Y-%m-%d') <= '".$useDate."'";
//            }
//        }
//
//        if($_GET['usingDate']) {
//            if($_GET['usingDateTo']) {
//                $useDate = implode('-',array_reverse(explode('/',trim($_GET['usingDate']))));
//                $useDate2 = implode('-',array_reverse(explode('/',trim($_GET['usingDateTo']))));
//
//                $where[] = " giftCardsUsage.usageDate BETWEEN  STR_TO_DATE('".$useDate."','%Y-%m-%d') AND STR_TO_DATE('".$useDate2."','%Y-%m-%d')";
//            }
//            else {
//                $useDate = implode('-',array_reverse(explode('/',trim($_GET['usingDate']))));
//                $where[] = " STR_TO_DATE(giftCardsUsage.usageDate,'%Y-%m-%d') >= '".$useDate."'";
//            }
//
//        }
//        else {
//            if($_GET['usingDateTo']) {
//                $useDate  =  implode('-',array_reverse(explode('/',trim($_GET['usingDateTo']))));
//                $where[] = " STR_TO_DATE(giftCardsUsage.usageDate,'%Y-%m-%d') <= '".$useDate."'";
//            }
//        }

        /**** this is a CORRECT way to deal with date ranges ***/

        if($tmp = UtilsDate::date2db($_GET['createDate']))
            $where[] = " gifts_purchases.transDate >= '" . $tmp . " 00:00:00'";
        if($tmp = UtilsDate::date2db($_GET['createDateTo']))
            $where[] = " gifts_purchases.transDate <= '" . $tmp . " 23:59:59'";

        if($tmp = UtilsDate::date2db($_GET['usingDate']))
            $where[] = " giftCardsUsage.usageDate >= '" . $tmp . " 00:00:00'";
        if($tmp = UtilsDate::date2db($_GET['usingDateTo']))
            $where[] = " giftCardsUsage.usageDate <= '" . $tmp . " 23:59:59'";

        /********************************************************/



        if(intval($_GET['selectedSite'])) {
            $where[] = " gifts_purchases.siteID=" . intval($_GET['selectedSite']) ." ";
        }

        $sqlNew = "SELECT gifts_purchases.*,giftCards.daysValid,giftCards.title
                        , GROUP_CONCAT(DATE(`giftCardsUsage`.`usageDate`) SEPARATOR ',') AS `usageDate`, SUM(`giftCardsUsage`.`useageSum`) AS `useageSum`
					FROM gifts_purchases 
					LEFT join giftCardsUsage on (giftCardsUsage.pID = gifts_purchases.pID) 
					LEFT join giftCards on (giftCards.giftCardID = gifts_purchases.giftCardID) 
					WHERE gifts_purchases.paid=1 AND gifts_purchases.siteID in (" . $siteID . ") AND " . implode(" AND ",$where) . " 
					GROUP BY gifts_purchases.pID
					ORDER by gifts_purchases.pID DESC";
        $list = udb::single_list($sqlNew);

        if ($list){
            if (count($list) <= 20)
                $transW = "p.transID IN (" . implode(',', array_map(function($a){return $a['transID'];}, $list)) . ")";
            else
                $transW = "p.siteID IN (" . $siteID . ") AND p.transID > 0";

            $que = "SELECT p.transID, COUNT(DISTINCT p.pID) AS `cnt`, GROUP_CONCAT(DISTINCT p.ordersID SEPARATOR ', ') AS `list`, SUM(`giftCardsUsage`.`useageSum`) AS `useageSum`
                    FROM `gifts_purchases` AS `p` LEFT join giftCardsUsage ON (giftCardsUsage.pID = p.pID)
                    WHERE " . $transW . " 
                    GROUP BY transID 
                    ORDER BY NULL";
            $trans = udb::key_row($que, 'transID');
        }
        else
            $trans = [];


        $displayed = [];
        foreach($list as $item) {
			$commrate = ($item['commPrec']) ?: $coms[$item['siteID']]['giftCardCommission'];

            $commission = $item['commSum'];
			$usageType = $item['refunded'] ? 9 : (!$item['useageSum']? 1 : ($item['voucherSum'] - $item['useageSum']>0? 2 : 3));
            $displayed[] = $item['pID'];
            
			if($_GET['type']) {
                if($_GET['type'] == 1) {
                    if(!$item['useageSum'] || $item['voucherSum'] == $item['useageSum']){
                        continue;
                    }
                }
                if($_GET['type'] == 2) {
                    if( $item['voucherSum'] != $item['useageSum']){
                        continue;
                    }
                }
            }

            $terminal = null;
            if ($terminals[$item['siteID']])
                $terminal = $terminals[$item['siteID']];
            elseif (strtoupper(Terminal::hasTerminal($item['siteID'], 'vouchers') ?: '') == 'CARDCOM')
                $terminals[$item['siteID']] = $terminal = new CardComGeneral($item['siteID'], 'vouchers');


?>
        <tr onclick="showPOP(<?=$item['pID']?>)" class="usageType<?=$usageType?>">
            <td style="width:130px;"><?=$item['ordersID']?></td>
            <td style="text-align:right;width: 170px"><?=($item['giftTitle'] ?: $item['title'])?></td>
            <td style="width:120px;">Vouchers.co.il</td>
            <td><?=date("d/m/Y",strtotime($item['transDate']))?></td>
            <td width="100">₪<?=$item['sum']?></td>
            <td width="60"><?=($commrate . '%')?></td>
            <td width="100"><?=($commission) ? "₪" . $commission   : ""?></td>
            <td><?=$item['usageDate'] ? implode('<br />', array_map('db2date', explode(',', $item['usageDate']))) : ''?></td>
            <td width="100">₪<?=$item['voucherSum']?></td>
            <td width="80">₪<?=number_format($item['useageSum'])?></td>
            <td width="100" style="text-align:center;"><?
                if ($item['refunded'])
                    echo 'בוצע זיכוי';
                elseif(!$item['useageSum']) {
                    echo 'הונפק';
                }
                else {
                    echo ($item['voucherSum'] > $item['useageSum']) ? 'מומש חלקית' : 'מומש';
                }
                ?></td><?
                    $useExpirationDate = date("d/m/Y", strtotime(" +".($item['validMonths'] ?: $item['daysValid'])." months", strtotime($item['reciveTime'] ? $item['reciveTime'] : date("Y-m-d"))));
                    if($item['extendExpiry']) {
                        $useExpirationDate = date("d/m/Y",strtotime($item['extendExpiry']));
                    }
                ?>
            <td style="width:170px;">בתוקף עד <?=$useExpirationDate?></td>
            <td style="width:160px;" class="control">
<?php
        if ($terminal){
            if ($item['hasInvoice']){
                $click = "window.open('download_invoice_gc.php?gcid=" . $item['pID'] . "', 'pay_gc" . $item['pID'] . "')";
?>
                <div class="pay invoice done" onclick="<?=$click?>"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path d="M430.584,0H218.147v144.132c0,9.54-7.734,17.274-17.274,17.274H56.741v325.917c0,13.628,11.049,24.677,24.677,24.677h349.166c13.628,0,24.677-11.049,24.677-24.677V24.677C455.261,11.049,444.212,0,430.584,0z M333.321,409.763H192.675c-9.54,0-17.274-7.734-17.274-17.274s7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274S342.861,409.763,333.321,409.763z M333.321,328.502H192.675c-9.54,0-17.274-7.734-17.274-17.274c0-9.54,7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274C350.595,320.768,342.861,328.502,333.321,328.502zM333.321,247.243H192.675c-9.54,0-17.274-7.734-17.274-17.274s7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274S342.861,247.243,333.321,247.243z"/><path d="M183.389,0c-6.544,0-12.82,2.599-17.448,7.229L63.968,109.198c-4.628,4.628-7.229,10.904-7.229,17.448v0.211h126.86V0H183.389z"/></svg><div>חשבונית</div></div>
<?php
            }
            elseif ($terminal->has_invoice && !$item['refunded']){
?>
                <div class="pay invoice" data-gc="<?=$item['pID']?>"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path d="M430.584,0H218.147v144.132c0,9.54-7.734,17.274-17.274,17.274H56.741v325.917c0,13.628,11.049,24.677,24.677,24.677h349.166c13.628,0,24.677-11.049,24.677-24.677V24.677C455.261,11.049,444.212,0,430.584,0z M333.321,409.763H192.675c-9.54,0-17.274-7.734-17.274-17.274s7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274S342.861,409.763,333.321,409.763z M333.321,328.502H192.675c-9.54,0-17.274-7.734-17.274-17.274c0-9.54,7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274C350.595,320.768,342.861,328.502,333.321,328.502zM333.321,247.243H192.675c-9.54,0-17.274-7.734-17.274-17.274s7.734-17.274,17.274-17.274h140.646c9.54,0,17.274,7.734,17.274,17.274S342.861,247.243,333.321,247.243z"/><path d="M183.389,0c-6.544,0-12.82,2.599-17.448,7.229L63.968,109.198c-4.628,4.628-7.229,10.904-7.229,17.448v0.211h126.86V0H183.389z"/></svg><div>חשבונית</div></div>
<?php
            }

            if ($item['refunded']){
                $click = "window.open('download_invoice_gc.php?gcid=" . $item['pID'] . "&type=refund', 'pay_gc" . $item['pID'] . "')";
?>
                <div class="pay refunded" onclick="<?=$click?>">בוצע זיכוי</div>
<?php
            }
            elseif ($trans[$item['transID']]['useageSum'] <= 0){
                $itemTrans = $trans[$item['transID']];
?>
                <div class="pay refund" onclick="askGCRefund(<?=$item['pID']?>, this)" data-cnt="<?=$itemTrans['cnt']?>" data-list="<?=$itemTrans['list']?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 20" width="22" height="20"><path d="M20.51 2.49C20.18 2.16 19.79 2 19.33 2L2.67 2C2.21 2 1.82 2.16 1.49 2.49 1.16 2.81 1 3.21 1 3.67L1 16.33C1 16.79 1.16 17.19 1.49 17.51 1.82 17.84 2.21 18 2.67 18L19.33 18C19.79 18 20.18 17.84 20.51 17.51 20.84 17.19 21 16.79 21 16.33L21 3.67C21 3.21 20.84 2.81 20.51 2.49ZM19.67 16.33C19.67 16.42 19.63 16.5 19.57 16.57 19.5 16.63 19.42 16.67 19.33 16.67L2.67 16.67C2.58 16.67 2.5 16.63 2.43 16.57 2.37 16.5 2.33 16.42 2.33 16.33L2.33 10 19.67 10 19.67 16.33ZM19.67 6L2.33 6 2.33 3.67C2.33 3.58 2.37 3.5 2.43 3.43 2.5 3.37 2.58 3.33 2.67 3.33L19.33 3.33C19.42 3.33 19.5 3.37 19.57 3.43 19.63 3.5 19.67 3.58 19.67 3.67L19.67 6 19.67 6ZM3.67 14L6.33 14 6.33 15.33 3.67 15.33 3.67 14ZM7.67 14L11.67 14 11.67 15.33 7.67 15.33 7.67 14Z"></path></svg><div>זיכוי</div></div>
<?php
            }
        }
?>
            </td>
        </tr>
<?php
		$totals['total_actions']++;
		$totals['useageSum']+=$item['useageSum'];
		$totals['commission']+=$commission;
		$totalsU[$usageType]++;
			
			
        }
?>
		<tr>
			<th><?=$totals['total_actions']?> רשומות</th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
            <th>₪<?=number_format($totals['commission'], 2)?></th>
            <th></th>
            <th></th>
			<th>₪<?=number_format($totals['useageSum'])?></th>
            <th></th>
			<th class="usageTypes">
				<div class="usageType1"><?=$totalsU[1]?: "0"?> - הנפקות</div>
				<div class="usageType3"><?=$totalsU[3]?: "0"?> - מימושים מלאים</div>
				<div class="usageType2"><?=$totalsU[2]?: "0"?> - מימושים חלקיים</div>
			</th>
            <th>&nbsp;</th>
		</tr>
    </tbody>
</table>

<div class="giftcard gift-pop gift" style="display: none">

</div>
<div class="giftcard gift-pop mimush" style="display: none">
     <div class="gift_container">
        <div class="gift_inside">
            <div class="title">מימוש הגיפט קארד - <span id="mimushLeftSum"></span></div>
            <div class="close" onclick="$('.giftcard.gift-pop.mimush').fadeOut('fast')"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21 21" width="21" height="21"><path class="shp0" d="M1.3 1.3C1.8 0.9 2.5 0.9 2.9 1.3L11 9.4 19.1 1.3C19.5 0.9 20.2 0.9 20.7 1.3 21.1 1.8 21.1 2.5 20.7 2.9L12.6 11 20.7 19.1C21.1 19.5 21.1 20.2 20.7 20.7 20.4 20.9 20.2 21 19.9 21 19.6 21 19.3 20.9 19.1 20.7L11 12.6 2.9 20.7C2.7 20.9 2.4 21 2.1 21 1.8 21 1.5 20.9 1.3 20.7 0.9 20.2 0.9 19.5 1.3 19.1L9.4 11 1.3 2.9C0.9 2.5 0.9 1.8 1.3 1.3Z"></path></svg></div>
        
			 <form id="mimushShovar">
			 <ul>
				 <li>
					  <div class="gift-balance">
							<div class="title">סכום למימוש</div>
							<div class="con"><input type="text" name="sumToUse" id="sumToUse" class="num" value=""></div>
						</div>
				 </li>
				 <li>
					  <div class="gift-balance">
							<div class="title">הערות מממש</div>
							<div class="con"><input type="text" name="commentsUsage" id="commentsUsage" value=""></div>
						</div>
				 </li>
			 </ul>
			 <div class="bottom-btns">
				 <div class="part">ממש את השובר</div>
			 </div>
			 </form>
		</div>
     </div>
</div>

<?
    //tbl: giftCardsSetting
    //@@fields: giftCardsSettingID, title, backgroundImage, logo, siteDescription, smallLetters, siteID, addManager, updateManager, addDate, updateDate
    $globalGiftData = [];
    $globalGiftSitesData = [];
    $sql = "select * from giftCardsSetting where siteID in (".$siteID.")";
    $globalGiftSitesData = udb::key_row($sql,"siteID");
    if(intval($_GET['siteID'])) {
        if($globalGiftSitesData[intval($_GET['siteID'])]) {
            $globalGiftData = $globalGiftSitesData[intval($_GET['siteID'])];
        }
    }
    if(!$globalGiftSitesData) {
        $globalGiftData['title'] = $siteName;
    }
    else {
        foreach ($globalGiftData as $item) {
            $globalGiftData = $item;
            break;
        }
    }
    $disabled = array(' disabled="disabled" ','style="display:none"' ,' readonly ');
    if(isset($_SESSION['user_id']) || intval($_SESSION['user_id'])) {
        $disabled[0] = "";
        $disabled[1] = "";
        $disabled[2] = "";
    }
?>

<script>
function askGCRefund(pid, e){
    let data = $(e).data(), html = (data.cnt > 1) ? '<b style="color:red">שימו לב!</b> הזיכוי יופעל על ' + data.cnt + ' שוברים: ' + data.list : '';

    swal.fire({icon:'question', title:'האם את/ה בטוח/ה שרוצה לזכות את השובר ?', html:html, showDenyButton:true, confirmButtonText:'כן', denyButtonText:'לא'}).then((result) => {
        if (result.isConfirmed) {
            $.post('ajax_giftcards.php', {act:'refundDirect', pid:pid}, function(res){
                if (!res || res.status === undefined || parseInt(res.status))
                    return swal.fire({icon:'error', title:res.error || res._txt || 'Unknown error'});
                window.location.reload();
            });
        }
    });
}

function deleteUsage(use, pid, code, text){
    swal.fire({icon:'question', title:'האם אתה בטוח שברצונך לבטל את המימוש?', text:'לחיצה על "כן" תחזיר את סכום המימוש לשובר', showDenyButton:true, confirmButtonText:'כן', denyButtonText:'לא'}).then((result) => {
        if (result.isConfirmed) {
            $.post('ajax_giftcards.php', {act:'deleteUsage', pid:pid, uid:use, code:code}, function(res){
                if (!res || res.status === undefined || parseInt(res.status))
                    return swal.fire({icon:'error', title:res.error || res._txt || 'Unknown error'});
                window.location.hash = code;
                window.location.reload();
            });
        }
    });
}

function closeShovarPop(){
    $('.giftcard.gift-pop.gift').fadeOut('fast');
    window.location.hash = '';
}

$(function(){
    $('td.control div.pay').on('click', function(e){
        e.stopPropagation();
    });

    if (window.location.hash)
        searchShovar(window.location.hash.substr(1));
});
</script>

<style>
.global_edit{z-index:99;display:block;position:fixed;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:rgba(0,0,0,.6)}
.popup{z-index:101;display:none;position:fixed;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:rgba(0,0,0,.6)}
.popup .close{position:absolute;top:10px;left:10px;cursor:pointer}
.popup .popup_container{position:absolute;top:50%;right:50%;transform:translateY(-50%) translate(50%);width:calc(100% - 10px);max-width:360px;height:calc(100vh - 10px);max-height:600px;background:#fff;border-radius:8px;overflow:auto;padding:0 40px;box-sizing:border-box}
.popup .title{display:block;text-align:center;font-size:26px;font-weight:500;padding:20px 0}
.global_edit .container{position:absolute;top:50%;right:50%;transform:translateY(-50%) translate(50%);width:calc(100% - 10px);max-width:650px;height:100%;max-height:calc(100vh - 10px);background:#f5f5f5;border-radius:8px;overflow:auto}
.global_edit .container .close{position:absolute;top:14px;left:14px;cursor:pointer;z-index:2}
.global_edit .container .close svg{fill:#aaa;width:17px;height:17px}
.global_edit .container>.title{display:block;font-weight:500;color:#333;font-size:30px;text-align:center;padding:12px 0 13px;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.5);z-index:1;position:relative}
.global_edit .container>.title .domain-icon{width:40px;height:40px;top:10px;right:10px}
.global_edit .container .tabs{display:block;text-align:center;margin:10px 0;font-size:0}
.global_edit .container .tabs .tab{display:inline-block;width:100%;max-width:120px;border-radius:7px;color:#0dabb6;height:40px;line-height:40px;margin:0 5px;font-size:16px;cursor:pointer;transition:all .2s ease;background:#fff;filter:drop-shadow(0 0 1.5px rgba(2, 3, 3, .2))}
.global_edit .container .tabs .tab.active{background:#2ab5bf;background:-moz-linear-gradient(top,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);background:-webkit-linear-gradient(top,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);background:linear-gradient(to bottom,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);color:#fff}
.global_edit .form{display:block;padding:20px;box-sizing:border-box;font-size:0;overflow:auto;position:absolute;left:0;right:0;top:60px;bottom:0;height:auto}
.signature .global_edit .form{top:45px}
.signature a{color:inherit}
.global_edit .inputWrap svg{position:absolute;top:50%;left:10px;transform:translateY(-50%);fill:#0dabb6}
.global_edit .inputWrap{border-radius:3px;font-size:14px;filter:drop-shadow(0 1px 1px rgba(2, 3, 3, .1));position:relative;height:auto;min-height:60px;background-color:#fff;border:1px solid #eee;display:inline-block;width:100%;max-width:98%;margin:0 1% 10px 1%;box-sizing:border-box}
.global_edit .inputWrap.date.four{max-width:58%}
.global_edit .inputWrap.date.time.four{max-width:38%}
.global_edit .inputWrap>label{position:absolute;top:3px;transform:none;right:5px;font-size:14px;color:#0dabb6;font-weight:500;line-height:1;transition:all .2s ease}
.global_edit .inputWrap.signature>label{font-size:20px}
.global_edit .inputWrap>input.empty:not(:focus)+label{font-size:20px;font-weight:400;top:50%;transform:translateY(-50%);padding-right:10px;opacity:.5}
.global_edit .inputWrap>input{font-size:20px;position:absolute;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:0 0;padding:0 10px;box-sizing:border-box;z-index:2;color:#333}
.global_edit .inputWrap>textarea{color:#000;font-size:20px;width:100%;height:100%;background:0 0;padding:20px 10px 10px;box-sizing:border-box;-webkit-transform:translateZ(0);-webkit-overflow-scrolling:touch}
.global_edit .inputWrap.submit{background:#e73219;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border-radius:3px}
.global_edit .cancelOrderBtn{box-sizing:border-box;width:100%;max-width:98%;margin:0 1% 10px 1%;background:#c03;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.global_edit .delOrderBtn{display:none;box-sizing:border-box;width:100%;max-width:98%;margin:0 1% 10px 1%;background:#c03;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.global_edit .signBtn{display:none;box-sizing:border-box;width:100%;max-width:48%;margin:0 1% 40px 1%;background:#03f;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.global_edit .signBtn.show{display:block}
.global_edit .inputWrap>select{position:absolute;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:0 0;font-size:20px;color:#333;padding:0 10px;box-sizing:border-box}
.global_edit .inputWrap:not(.date)>input{color:#333}
.global_edit .inputWrap:not(.date)>input::-webkit-input-placeholder{color:#0dabb6}
.global_edit .inputWrap:not(.date) input:read-only{background:rgba(13 ,171 ,182,.2);cursor:initial}
.global_edit .inputWrap.textarea>textarea::-webkit-input-placeholder{color:#0dabb6}
.global_edit .inputWrap.textarea{height:180px}
.global_edit .inputWrap .short-desc{font-size:16px;padding:20px 5px;display:block;box-sizing:border-box}
.global_edit .statusBtn.del .cancelOrderBtn{display:none}
.global_edit .statusBtn.del .delOrderBtn{display:block}
.global_edit .rooms .room{cursor:pointer;border-radius:3px;font-size:14px;filter:drop-shadow(0 1px 1px rgba(2, 3, 3, .1));position:relative;height:auto;min-height:60px;background-color:#fff;border:1px solid #eee;display:inline-block;width:100%;max-width:98%;margin:0 1% 10px 1%;box-sizing:border-box}
.global_edit .rooms .room .title{float:right;display:inline-block;color:#777;font-size:20px;line-height:58px;position:relative;padding-right:50px}
.signature .global_edit .rooms .room .title{padding-right:10px}
.signature .rooms select{text-align-last:center}
.signature .global_edit .inputWrap input{background:0 0!important}
.global_edit .rooms input:checked+.room{border:1px solid #0dabb6}
.global_edit .rooms input:checked+.room .title{color:#0dabb6}
.global_edit .rooms input:not(:checked)+.room .l::after{content:"";position:absolute;top:0;bottom:0;left:0;right:0;background:rgba(255,255,255,.7)}
.global_edit .rooms .room .title::before{content:'';position:absolute;top:50%;right:10px;width:30px;height:30px;box-sizing:border-box;border:1px solid #d0d0d0;border-radius:30px;transform:translateY(-50%);transition:all .2s ease}
.global_edit .rooms .room .title::after{content:'';position:absolute;top:50%;right:13px;transform:translateY(-50%);width:24px;height:24px;border-radius:25px;background:#0dabb6;opacity:0;transition:all .2s ease}
.global_edit .rooms input[type=radio]{display:none}
.global_edit .rooms input[type=checkbox]{display:none}
.global_edit .rooms .room .l{display:block;text-align:center;position:relative;margin-bottom:10px;clear:both}
.global_edit .rooms .room .l .payments{border-top:1px #ccc solid;margin-top:10px;margin-bottom:10px}
.global_edit .rooms .room .l .payments .meals{height:40px;border-bottom:1px #ccc solid;margin-bottom:10px;padding:5px 0}
.global_edit .rooms .room .l .payments .meals select{width:calc(100% - 20px);height:40px;font-size:16px}
.global_edit .rooms .room .l .payments .dataInp{width:calc(100% - 20px);margin:0 5px}
.global_edit .rooms .room .l .dataInp input{width:100%;border:1px #ccc solid;margin-top:-18px;height:50px;background:0 0;text-align:center;font-size:20px;padding-top:10px;box-sizing:border-box}
.global_edit .rooms input:not(:checked)+.room .l{display:none}
.global_edit .rooms .room .l .dataInp{display:inline-block;width:45px;text-align:center;margin-right:20px;position:relative}
.global_edit .rooms .room .l .dataInp.adults::before,.global_edit .rooms .room .l .dataInp.babies::before,.global_edit .rooms .room .l .dataInp.kids::before{content:'';position:absolute;top:50%;left:0;border-left:2px solid #000;border-bottom:2px solid #000;width:5px;height:5px;transform:rotate(-45deg)}
.global_edit .rooms .room .l .dataInp label{font-size:14px;color:#0dabb6;display:block;margin:0 -10px;text-align:center}
.global_edit .rooms .room .l .dataInp select{height:30px;width:100%;font-size:20px;appearance:none;-webkit-appearance:none}
.global_edit .rooms input:not(:checked)+.room .l .payments{display:none}
.global_edit .rooms input:checked+.room .title::before{border-color:#14adb8}
.global_edit .rooms input:checked+.room .title::after{opacity:1}
.global_edit .text-wrapper{font-size:18px;margin:30px 0}
.global_edit .text-wrapper .question{margin:30px 20px;position:relative}
.global_edit .text-wrapper .question::before{content:"";width:10px;height:10px;background:#0dabb6;display:block;position:absolute;margin-right:-18px;margin-top:6px;border-radius:50%}
.global_edit .text-wrapper .question input[type=checkbox]{height:30px;width:30px;margin-top:-4px;margin-left:5px;position:absolute}
.global_edit .text-wrapper .question input[type=checkbox]+span{padding-right:40px;display:inline-block}
.global_edit .text-wrapper .question select{clear:both;display:block;border:1px #000 solid;padding:8px 10px;margin-top:6px;-webkit-appearance:auto;background:#d4f6f9}
.global_edit .text-wrapper .question.checked::after{content:"";position:absolute;bottom:11px;right:-16px;width:4px;height:8px;border-right:3px #0dabb6 solid;border-bottom:3px #0dabb6 solid;transform:rotate(45deg)}
.global_edit .text-wrapper .question .extra{display:none}
.global_edit .text-wrapper .question.open .extra{display:block}
.global_edit .text-wrapper .question .extra input{width:300px;max-width:calc(100% - 20px);border-bottom:1px #ccc solid;margin:0 10px;font-size:18px}
.global_edit .text-wrapper .question .extra span{display:inline-block}
.global_edit .text-wrapper .question .extra div{margin:10px 0}
.signature .global_edit .inputWrap.signature{margin-bottom:70px;padding-top:40px}
.signature .global_edit .inputWrap.signature .btnWrap{text-align:center;margin-bottom:20px}
.signature .global_edit .inputWrap.signature .waze{background-image:url(../img/waze.png)}
.signature .global_edit .inputWrap.signature .addToCal{background-image:url(../img/dl.png)}
.signature .global_edit .inputWrap.signature .print{background-image:url(../img/printer-4-48.png);background-size:70%!important}
.signature .global_edit .inputWrap.signature .google{background-image:url(../img/google.png)}
.signature .global_edit .inputWrap.signature .signBtn{position:relative;width:80px;height:80px;border-radius:100px;background-color:#0dabb6;display:inline-block;color:#555;background-size:contain;background-repeat:no-repeat;background-position:center center}
.signature .global_edit .inputWrap.signature .signBtn span{position:absolute;bottom:-40px;left:0;right:0;font-size:18px;font-weight:400}
.page-signature .global_edit .inputWrap.signature{height:180px}
.giftpop{z-index:99;display:block;position:fixed;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:rgba(0,0,0,.6)}
.popup{z-index:101;display:none;position:fixed;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:rgba(0,0,0,.6)}
.popup .close{position:absolute;top:10px;left:10px;cursor:pointer}
.popup .popup_container{position:absolute;top:50%;right:50%;transform:translateY(-50%) translate(50%);width:calc(100% - 10px);max-width:360px;height:calc(100vh - 10px);max-height:600px;background:#fff;border-radius:8px;overflow:auto;padding:0 40px;box-sizing:border-box}
.popup .title{display:block;text-align:center;font-size:26px;font-weight:500;padding:20px 0}
.giftpop .container{position:absolute;top:50%;right:50%;transform:translateY(-50%) translate(50%);width:calc(100% - 10px);max-width:650px;height:100%;max-height:calc(100vh - 10px);background:#f5f5f5;border-radius:8px;overflow:auto}
.giftpop .container .close{position:absolute;top:14px;left:14px;cursor:pointer;z-index:2}
.giftpop .container .close svg{fill:#aaa;width:17px;height:17px}
.giftpop .container>.title{display:block;font-weight:500;color:#333;font-size:30px;text-align:center;padding:12px 0 13px;background:#fff;box-shadow:0 0 10px rgba(0,0,0,.5);z-index:1;position:relative}
.giftpop .container>.title .domain-icon{width:40px;height:40px;top:10px;right:10px}
.giftpop .container .tabs{display:block;text-align:center;margin:10px 0;font-size:0}
.giftpop .container .tabs .tab{display:inline-block;width:100%;max-width:120px;border-radius:7px;color:#0dabb6;height:40px;line-height:40px;margin:0 5px;font-size:16px;cursor:pointer;transition:all .2s ease;background:#fff;filter:drop-shadow(0 0 1.5px rgba(2, 3, 3, .2))}
.giftpop .container .tabs .tab.active{background:#2ab5bf;background:-moz-linear-gradient(top,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);background:-webkit-linear-gradient(top,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);background:linear-gradient(to bottom,#2ab5bf 0,#3dbcc5 49%,#0dabb6 52%,#0dabb6 100%);color:#fff}
.giftpop .form{display:block;padding:20px;box-sizing:border-box;font-size:0;overflow:auto;position:absolute;left:0;right:0;top:60px;bottom:0;height:auto}
.signature .giftpop .form{top:45px}
.signature a{color:inherit}
.giftpop .inputWrap svg{position:absolute;top:50%;left:10px;transform:translateY(-50%);fill:#0dabb6}
.giftpop .inputWrap{border-radius:3px;font-size:14px;filter:drop-shadow(0 1px 1px rgba(2, 3, 3, .1));position:relative;height:auto;min-height:60px;background-color:#fff;border:1px solid #eee;display:inline-block;width:100%;max-width:98%;margin:0 1% 10px 1%;box-sizing:border-box}
.giftpop .inputWrap.date.four{max-width:58%}
.giftpop .inputWrap.date.time.four{max-width:38%}
.giftpop .inputWrap>label{position:absolute;top:3px;transform:none;right:5px;font-size:14px;color:#0dabb6;font-weight:500;line-height:1;transition:all .2s ease}
.inputWrap> input[type="file"] + label {padding: 10px;border: 1px #0dabb6 solid;padding-left: 40px;border-radius: 20px;background-color: rgba(240,240,240,0.8);background-image: url(/user/assets/img/upload.svg);background-size: 16px;background-repeat: no-repeat;background-position: left 10px center;cursor:pointer}
.giftpop .inputWrap.signature>label{font-size:20px}
.giftpop .inputWrap>input.empty:not(:focus)+label{font-size:20px;font-weight:400;top:50%;transform:translateY(-50%);padding-right:10px;opacity:.5}
.giftpop .inputWrap>input{font-size:20px;position:absolute;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:0 0;padding:0 10px;box-sizing:border-box;z-index:2;color:#333}
.giftpop .inputWrap>textarea{color:#000;font-size:20px;width:100%;height:100%;background:0 0;padding:20px 10px 10px;box-sizing:border-box;-webkit-transform:translateZ(0);-webkit-overflow-scrolling:touch}
.giftpop .inputWrap.submit{background:#e73219;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border-radius:3px}
.giftpop .cancelOrderBtn{box-sizing:border-box;width:100%;max-width:98%;margin:0 1% 10px 1%;background:#c03;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.giftpop .delOrderBtn{display:none;box-sizing:border-box;width:100%;max-width:98%;margin:0 1% 10px 1%;background:#c03;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.giftpop .signBtn{display:none;box-sizing:border-box;width:100%;max-width:48%;margin:0 1% 40px 1%;background:#03f;color:#fff;text-align:center;font-size:30px;font-weight:500;cursor:pointer;border:1px solid #eee;border-radius:3px;line-height:60px}
.giftpop .signBtn.show{display:block}
.giftpop .inputWrap>select{position:absolute;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:0 0;font-size:20px;color:#333;padding:0 10px;box-sizing:border-box}
.giftpop .inputWrap:not(.date)>input{color:#333}
.giftpop .inputWrap:not(.date)>input::-webkit-input-placeholder{color:#0dabb6}
.giftpop .inputWrap:not(.date) input:read-only{background:rgba(13 ,171 ,182,.2);cursor:initial}
.giftpop .inputWrap.textarea>textarea::-webkit-input-placeholder{color:#0dabb6}
.giftpop .inputWrap.textarea{height:180px}
.giftpop .inputWrap.textarea img{max-height:100%}
.giftpop .inputWrap .short-desc{font-size:16px;padding:20px 5px;display:block;box-sizing:border-box}
.giftpop .statusBtn.del .cancelOrderBtn{display:none}
.giftpop .statusBtn.del .delOrderBtn{display:block}
.giftpop .rooms .room{cursor:pointer;border-radius:3px;font-size:14px;filter:drop-shadow(0 1px 1px rgba(2, 3, 3, .1));position:relative;height:auto;min-height:60px;background-color:#fff;border:1px solid #eee;display:inline-block;width:100%;max-width:98%;margin:0 1% 10px 1%;box-sizing:border-box}
.giftpop .rooms .room .title{float:right;display:inline-block;color:#777;font-size:20px;line-height:58px;position:relative;padding-right:50px}
.signature .giftpop .rooms .room .title{padding-right:10px}
.signature .rooms select{text-align-last:center}
.signature .giftpop .inputWrap input{background:0 0!important}
.giftpop .rooms input:checked+.room{border:1px solid #0dabb6}
.giftpop .rooms input:checked+.room .title{color:#0dabb6}
.giftpop .rooms input:not(:checked)+.room .l::after{content:"";position:absolute;top:0;bottom:0;left:0;right:0;background:rgba(255,255,255,.7)}
.giftpop .rooms .room .title::before{content:'';position:absolute;top:50%;right:10px;width:30px;height:30px;box-sizing:border-box;border:1px solid #d0d0d0;border-radius:30px;transform:translateY(-50%);transition:all .2s ease}
.giftpop .rooms .room .title::after{content:'';position:absolute;top:50%;right:13px;transform:translateY(-50%);width:24px;height:24px;border-radius:25px;background:#0dabb6;opacity:0;transition:all .2s ease}
.giftpop .rooms input[type=radio]{display:none}
.giftpop .rooms input[type=checkbox]{display:none}
.giftpop .rooms .room .l{display:block;text-align:center;position:relative;margin-bottom:10px;clear:both}
.giftpop .rooms .room .l .payments{border-top:1px #ccc solid;margin-top:10px;margin-bottom:10px}
.giftpop .rooms .room .l .payments .meals{height:40px;border-bottom:1px #ccc solid;margin-bottom:10px;padding:5px 0}
.giftpop .rooms .room .l .payments .meals select{width:calc(100% - 20px);height:40px;font-size:16px}
.giftpop .rooms .room .l .payments .dataInp{width:calc(100% - 20px);margin:0 5px}
.giftpop .rooms .room .l .dataInp input{width:100%;border:1px #ccc solid;margin-top:-18px;height:50px;background:0 0;text-align:center;font-size:20px;padding-top:10px;box-sizing:border-box}
.giftpop .rooms input:not(:checked)+.room .l{display:none}
.giftpop .rooms .room .l .dataInp{display:inline-block;width:45px;text-align:center;margin-right:20px;position:relative}
.giftpop .rooms .room .l .dataInp.adults::before,.giftpop .rooms .room .l .dataInp.babies::before,.giftpop .rooms .room .l .dataInp.kids::before{content:'';position:absolute;top:50%;left:0;border-left:2px solid #000;border-bottom:2px solid #000;width:5px;height:5px;transform:rotate(-45deg)}
.giftpop .rooms .room .l .dataInp label{font-size:14px;color:#0dabb6;display:block;margin:0 -10px;text-align:center}
.giftpop .rooms .room .l .dataInp select{height:30px;width:100%;font-size:20px;appearance:none;-webkit-appearance:none}
.giftpop .rooms input:not(:checked)+.room .l .payments{display:none}
.giftpop .rooms input:checked+.room .title::before{border-color:#14adb8}
.giftpop .rooms input:checked+.room .title::after{opacity:1}
.giftpop .text-wrapper{font-size:18px;margin:30px 0}
.giftpop .text-wrapper .question{margin:30px 20px;position:relative}
.giftpop .text-wrapper .question::before{content:"";width:10px;height:10px;background:#0dabb6;display:block;position:absolute;margin-right:-18px;margin-top:6px;border-radius:50%}
.giftpop .text-wrapper .question input[type=checkbox]{height:30px;width:30px;margin-top:-4px;margin-left:5px;position:absolute}
.giftpop .text-wrapper .question input[type=checkbox]+span{padding-right:40px;display:inline-block}
.giftpop .text-wrapper .question select{clear:both;display:block;border:1px #000 solid;padding:8px 10px;margin-top:6px;-webkit-appearance:auto;background:#d4f6f9}
.giftpop .text-wrapper .question.checked::after{content:"";position:absolute;bottom:11px;right:-16px;width:4px;height:8px;border-right:3px #0dabb6 solid;border-bottom:3px #0dabb6 solid;transform:rotate(45deg)}
.giftpop .text-wrapper .question .extra{display:none}
.giftpop .text-wrapper .question.open .extra{display:block}
.giftpop .text-wrapper .question .extra input{width:300px;max-width:calc(100% - 20px);border-bottom:1px #ccc solid;margin:0 10px;font-size:18px}
.giftpop .text-wrapper .question .extra span{display:inline-block}
.giftpop .text-wrapper .question .extra div{margin:10px 0}
.signature .giftpop .inputWrap.signature{margin-bottom:70px;padding-top:40px}
.signature .giftpop .inputWrap.signature .btnWrap{text-align:center;margin-bottom:20px}
.signature .giftpop .inputWrap.signature .waze{background-image:url(../img/waze.png)}
.signature .giftpop .inputWrap.signature .addToCal{background-image:url(../img/dl.png)}
.signature .giftpop .inputWrap.signature .print{background-image:url(../img/printer-4-48.png);background-size:70%!important}
.signature .giftpop .inputWrap.signature .google{background-image:url(../img/google.png)}
.signature .giftpop .inputWrap.signature .signBtn{position:relative;width:80px;height:80px;border-radius:100px;background-color:#0dabb6;display:inline-block;color:#555;background-size:contain;background-repeat:no-repeat;background-position:center center}
.signature .giftpop .inputWrap.signature .signBtn span{position:absolute;bottom:-40px;left:0;right:0;font-size:18px;font-weight:400}
.page-signature .giftpop .inputWrap.signature{height:180px}
.pay_order{z-index:99;display:block;position:fixed;top:0;right:0;left:0;bottom:0;width:100%;height:100%;background:rgba(0,0,0,.6)}
.pay_order .inputLblWrap .switch input:checked+.slider span{display:block;padding-right:0}
.pay_order .inputLblWrap .switch input+.slider span{display:block;font-size:16px;color:#fff;font-weight:500;line-height:34px;padding-right:10px}
.pay_order .inputLblWrap .switch input:checked+.slider:after{content:'';position:absolute;top:44%;right:0;width:10px;height:2px;border-left:2px solid #0dabb6;border-bottom:2px solid #0dabb6;transform:rotate(-45deg);right:11px}
.last-orders .items .order.allpaid ul li.send>div .orderPrice.new{border-color:#0dabb6;background:#f5fcfc}
.last-orders .items .order.allpaid ul li.send>div .orderPrice.new svg{fill:#0dabb6}
.last-orders .items .order.allpaid ul li.send>div .orderPrice.new>span>span{color:#0dabb6}
section.giftcards{text-align:center;box-sizing:border-box}
section.giftcards>.title{display:block;font-size:24px}
.top-btns{display:block;text-align:center}
.giftcard .inside{height:120px;vertical-align:middle;display:table-cell;width:100%}
.giftcard .r{display:inline-block;width:100%;max-width:calc(100% - 300px);font-size:16px}
.giftcard .r .title{font-weight:800;color:#0dabb6}
.giftcard .l{text-align:center;display:inline-block;width:100%;max-width:150px}
.giftcard .active .inside{width:150px}
.page-options{float:left}
.clear{clear:both}
.add-new,.page-options,.save,.top-btns>div{background:#0dabb6;font-size:16px;display:inline-block;padding:15px 30px;box-sizing:border-box;color:#fff;cursor:pointer;border-radius:10px;margin:0 5px}
.edit svg{fill:#0dabb6}
.remove svg{fill:#a1aaad}
.add-new{display:block;float:right;max-width:150px}
.giftcards-list{clear:both;margin:20px 0}
.giftcards-list>.giftcard{font-size:0;background:#fff;text-align:Right;min-height:120px;border-radius:10px;border:1px solid #ccc;box-sizing:border-box;box-shadow:-1px 0 5px rgba(2,3,3,.1);margin-top:20px}
.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;-webkit-transition:.4s;transition:.4s}
.slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background-color:#fff;-webkit-transition:.4s;transition:.4s}
input:checked+.slider{background-color:#0dabb6}
input:focus+.slider{box-shadow:0 0 1px #0dabb6}
.giftcard .active{width:100%;display:inline-block;max-width:150px;text-align:center;font-size:16px;font-weight:800;color:#0dabb6}
.giftcard input[type=checkbox]{display:none}
input:checked+.slider:before{-webkit-transform:translateX(26px);-ms-transform:translateX(26px);transform:translateX(26px)}
.switch{position:relative;display:inline-block;width:60px;height:34px}
.slider.round{border-radius:34px}
.slider.round:before{border-radius:50%}
.giftcard .l svg{width:40px;height:Auto}
.giftcard .l .inside>div{display:inline-block;vertical-align:middle;cursor:pointer}
.giftcard .l .inside{width:150px;padding-left:20px;box-sizing:border-box}
@media (min-width:1000px){
.global_edit{width:calc(100% - 300px);right:300px}
.global_edit .inputWrap{height:60px}
.global_edit .rooms .room{min-height:60px}
.global_edit .inputWrap.half{max-width:48%;margin:0 1% 10px 1%}
.global_edit .inputWrap.four{max-width:23%}
.global_edit .inputWrap.three{max-width:31.33%}
.global_edit .inputWrap.date.four{max-width:28%}
.global_edit .inputWrap.date.time.four{max-width:18%}

.giftpop form>.half {
    display: inline-block;
    width: 100%;
    max-width: 48%;
    margin: 0 1% 10px 1%;
}

.giftpop{width:calc(100% - 300px);right:300px}
.giftpop .inputWrap{height:60px}
.giftpop .rooms .room{min-height:60px}
.giftpop .inputWrap.half{max-width:48%;margin:0 1% 10px 1%}
.giftpop .inputWrap.four{max-width:23%}
.giftpop .inputWrap.three{max-width:31.33%}
.giftpop .inputWrap.date.four{max-width:28%}
.giftpop .inputWrap.date.time.four{max-width:18%}
}
    </style>
