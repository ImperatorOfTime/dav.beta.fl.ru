<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

    if ( !hasPermissions('tservices') ) {
        exit;
    }

    $aDatetypes = array( 'create' => 'date', 'accept' => 'accept_date', 'close' => 'close_date' );
    $sDate = ( isset($_GET['filterdate']) ) ? (in_array($_GET['filterdate'], array_keys($aDatetypes)) ? $aDatetypes[$_GET['filterdate']] : 'date') : 'date';

    if($_GET['date_begin']=='' || $_GET['date_end']=='') {
    	$date_begin = date("d-m-Y", mktime(0,0,0,date("m")-1,date("d"),date("Y")));
    	$date_end = date("d-m-Y");
    } else {
    	$date_begin = $_GET['date_begin'];
    	$date_end = $_GET['date_end'];
    }

    $s_date_begin = substr($date_begin, 6,4)."-".substr($date_begin, 3,2)."-".substr($date_begin, 0,2);
    $s_date_end = substr($date_end, 6,4)."-".substr($date_end, 3,2)."-".substr($date_end, 0,2).' 23:59:59';

    $orders = $DB->rows("-- #0026621

SELECT 
  DISTINCT ON (tso.id)
    tso.id, tso.tax_price, tso.frl_id,
    frl.login as frl_login,
    emp.login as emp_login,
    tso.date,
    tso.accept_date,
    tso.close_date,
    tso.status,
    fb1.rating as emp_feedback,
    fb2.rating as frl_feedback,
    ac.sum as frl_balance

FROM
    tservices_orders as tso

INNER JOIN freelancer as frl ON tso.frl_id=frl.uid 
INNER JOIN employer as emp ON tso.emp_id=emp.uid 
LEFT JOIN tservices_orders_feedbacks as fb1 ON fb1.id=tso.emp_feedback_id
LEFT JOIN tservices_orders_feedbacks as fb2 ON fb2.id=tso.frl_feedback_id
LEFT JOIN account as ac ON ac.uid=tso.frl_id

WHERE
    (tso.{$sDate} BETWEEN ? AND ?)

ORDER BY tso.id 

LIMIT 2000 OFFSET 0", $s_date_begin, $s_date_end);

    $statuses = array(
        '0' => '�� �����������',
        '-1' => '��������� ���������',
        '-2' => '������������ �������',
        '1' => '� ������',
        '2' => '�������� �����������',
        '3' => '�������� �������������'
    );
    
    $feedbacks = array(
      '-1' => '�������������',
       '1' => '�������������',
        '' => '�����������'
    );
    
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
   
?>

<h3>������� ������� ������� �����</h3>
<br />
<form name="frm" method="get" action=".">
    <input type="hidden" name="mode" value="orders">
    <label class="flt-lbl">������ �������:</label>
    <input type="text" maxlength="10" id="date_begin" name="date_begin" value="<?=$date_begin?>" class="apf-date" readonly="readonly">
    <span class="apf-date" id="date_begin_btn">&nbsp;</span>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

    <label class="flt-lbl">����� �������:</label>
    <input type="text" maxlength="10" id="date_end" name="date_end" value="<?=$date_end?>" class="apf-date" readonly="readonly">
    <span class="apf-date" id="date_end_btn">&nbsp;</span>
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <select name="filterdate">
        <option value="create"<?php echo $_GET['filterdate']=='create'?' selected="selected"':''?>>���� �������� ������</option>
        <option value="accept"<?php echo $_GET['filterdate']=='accept'?' selected="selected"':''?>>���� ������������� ������������</option>
        <option value="close"<?php echo $_GET['filterdate']=='close'?' selected="selected"':''?>>���� ���������� �������������� � ������</option>
    </select>
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    
    <input type="submit" value="��������">
    
</form>

<br/><br/>


<table cellpadding="2" cellspacing="2" border="1" width="100%"  class="brd-tbl">
<tr valign="top" align="center">
	<td style="text-align:center"><b>� ������</b></td>
	<td style="text-align:center"><B>��������</B></TD>
	<td style="text-align:center"><B>�����������</B></TD>
	<td style="text-align:center"><B>���� �������� ������</B></td>
	<td style="text-align:center"><B>���� ������������� ������������</B></td>
	<td style="text-align:center"><B>���� ���������� �������������� � ������</B></td>
        <td style="text-align:center"><b>������ ������</b></td>
        <td style="text-align:center"><b>����� �� �����������</b></td>
        <td style="text-align:center"><b>����� �� ������������</b></td>
        <td style="text-align:center"><b>����� ��������� ��������</b></td>
        <td style="text-align:center"><b>������ �� ����� �����������</b></td>
</tr>

<?php foreach ($orders as $order) { 
    $bill = new billing($order['frl_id']);
    $paid_tax = $order['tax_price']; //$order['status'] > 1 ? $order['tax_price'] : 0; // ������� ��������� ��������
?>
<tr valign="top">
    <td style="text-align:center" align="center"><a href="/tu/order/<?=$order['id']?>/"><?=$order['id']?></a></td>
    <td style="text-align:center">&nbsp;<?=$order['emp_login'] ?></td>
    <td style="text-align:center">&nbsp;<?=$order['frl_login'] ?></td>
    <td style="text-align:center">&nbsp;<?= dateFormat("d.m.Y H:i", $order['date'])?></td>
    <td style="text-align:center">&nbsp;<?= $order['accept_date'] ? dateFormat("d.m.Y H:i", $order['accept_date']) : "-"?></td>
    <td style="text-align:center">&nbsp;<?= $order['close_date'] ? dateFormat("d.m.Y H:i", $order['close_date']) : "-"?></td>
    <td style="text-align:center">&nbsp;<?=$statuses[$order['status']]?></td>
    <td style="text-align:center">&nbsp;<?=$feedbacks[$order['frl_feedback']]?></td>
    <td style="text-align:center">&nbsp;<?=$feedbacks[$order['emp_feedback']]?></td>
    <td style="text-align:center">&nbsp;<?=$paid_tax?></td>
    <td style="text-align:center">&nbsp;<?=to_money($bill->acc['sum'], 2)?> ���.</td>
</tr>
<?php } ?>

</table>

<script type="text/javascript">
window.addEvent("domready", function() {
	new tcal ({ 'formname': 'frm', 'controlname': 'date_begin', 'iconId': 'date_begin_btn' });
	new tcal ({ 'formname': 'frm', 'controlname': 'date_end', 'iconId': 'date_end_btn' });
});
</script>