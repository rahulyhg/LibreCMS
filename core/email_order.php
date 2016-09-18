<?php
require'db.php';
require'tcpdf/tcpdf.php';
$config=$db->query("SELECT * FROM config WHERE id='1'")->fetch(PDO::FETCH_ASSOC);
$id=filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
$w=filter_input(INPUT_GET,'w',FILTER_SANITIZE_STRING);
$act=filter_input(INPUT_GET,'act',FILTER_SANITIZE_STRING);
$q=$db->prepare("SELECT * FROM orders WHERE id=:id");
$q->execute(array(':id'=>$id));
$r=$q->fetch(PDO::FETCH_ASSOC);
$q=$db->prepare("SELECT * FROM login WHERE id=:id");
$q->execute(array(':id'=>$r['cid']));
$c=$q->fetch(PDO::FETCH_ASSOC);
$ti=time();
if($r['qid']!="")$oid=$r['qid'];
if($r['iid']!="")$oid=$r['iid'];
$pdf=new TCPDF(PDF_PAGE_ORIENTATION,PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF-8',false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($config['business']);
$pdf->SetTitle('Order #'.$oid);
$pdf->SetSubject('Order #'.$oid);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT,PDF_MARGIN_TOP,PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE,PDF_MARGIN_BOTTOM);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont('helvetica','',12);
$pdf->AddPage();
$html='<style>';
$html.='body{margin:0;padding:0;}';
$html.='table{border:0;margin:0;}';
$html.='table,table tr{background-color:#fff;}';
$html.='table tr th{background-color:#000;color:#fff;font-size:10px;}';
$html.='h1,h2,h3,h4,h5,h6,p{margin:0;}';
$html.='.col-50{width:50px;}';
$html.='.col-75{width:75px;}';
$html.='.col-100{width:100px;}';
$html.='.col-150{width:150px;}';
$html.='.col-250{width:250px;}';
$html.='.text-center{text-align:center;}';
$html.='.text-right{text-align:right;}';
$html.='.pending{color:#080;}';
$html.='.overdue{color:#800;}';
$html.='</style>';
$html.='<body>';
$pdflogo='';
if(file_exists('../media/orderheading.png'))
	$pdflogo='../media/orderheading.png';
elseif(file_exists('../media/orderheading.jpg'))
	$pdflogo='../media/orderheading.jpg';
elseif(file_exists('../media/orderheading.gif'))
	$pdflogo='../media/orderheading.gif';
elseif(file_exists('../layout/'.$config['theme'].'/images/orderheading.png'))
	$pdflogo='../layout/'.$config['theme'].'/images/orderheading.png';
elseif(file_exists('../layout/'.$config['theme'].'images/orderheading.jpg'))
	$pdflogo='../layout/'.$config['theme'].'/images/orderheading.jpg';
elseif(file_exists('../layout/'.$config['theme'].'images/orderheading.gif'))
	$pdflogo='../layout/'.$config['theme'].'/images/orderheading.gif';
else
	$pdflogo='';
if($pdflogo!=''){
	$html.='<table class="table">';
		$html.='<tr>';
			$html.='<td style="text-align:right;">';
				$html.='<img src="'.$pdflogo.'">';
			$html.='</td>';
		$html.='</tr>';
	$html.='</table>';
}
	$html.='<table class="table">';
		$html.='<tr>';
			$html.='<td>';
				$html.='<h3>From</h3>';
				$html.='<p>';
					$html.='<strong>'.$config['business'].'</strong><br />';
					$html.='ABN: <strong>'.$config['abn'].'</strong><br />';
					$html.=$config['address'].', '.$config['suburb'].', '.$config['city'].', '.$config['state'].', '.$config['postcode'];
				$html.='</p>';
			$html.='</td>';
			$html.='<td>';
				$html.='<h3>To</h3>';
				$html.='<p>';
					$html.='<strong>'.$c['business'].'</strong><br />';
					$html.=$c['name'].'<br />';
					$html.=$c['address'].', '.$c['suburb'].', '.$c['city'].', '.$c['state'].', '.$c['postcode'];
				$html.='</p>';
			$html.='</td>';
			$html.='<td>';
				$html.='<h3>Details</h3>';
				$html.='<p><small>';
					$html.='Order <strong>#'.$r['qid'].$r['iid'].'</strong><br />';
					$html.='Order Date <strong>'.date($config['dateFormat'],$r['qid_ti'].$r['iid_ti']).'</strong><br />';
					$html.='Due Date: <strong class="'.$r['status'].'">'.date($config['dateFormat'],$r['due_ti']).'</strong><br />';
					$html.='Status: <strong class="'.$r['status'].'">'.ucfirst($r['status']).'</strong>';
				$html.='</small></p>';
			$html.='</td>';
		$html.='</tr>';
	$html.='</table>';
	$html.='<br /><br />';
	$html.='<table class="table table-striped">';
		$html.='<thead>';
			$html.='<tr>';
				$html.='<th class="col-75">Item Code</th>';
				$html.='<th class="col-150">Title</th>';
				$html.='<th class="col-150">Option</th>';
				$html.='<th class="col-50 text-center">Quantity</th>';
				$html.='<th class="col-50 text-right">Cost</th>';
				$html.='<th class="col-50 text-right">Total</th>';
			$html.='</tr>';
		$html.='</thead>';
		$html.='<tbody>';
		$i=13;
		$ot=0;
		$st=0;
		$pwc=0;
		$zeb=1;
$s=$db->prepare("SELECT * FROM orderitems WHERE oid=:oid AND status!='delete'");
$s->execute(array(':oid'=>$id));
while($ro=$s->fetch(PDO::FETCH_ASSOC)){
	$si=$db->prepare("SELECT code,title FROM content WHERE id=:id");
	$si->execute(array(':id'=>$ro['iid']));
	$i=$si->fetch(PDO::FETCH_ASSOC);
	$sc=$db->prepare("SELECT * FROM choices WHERE id=:id");
	$sc->execute(array(':id'=>$ro['cid']));
	$c=$sc->fetch(PDO::FETCH_ASSOC);
			$html.='<tr';
				if($zeb==1){
					$html.=' style="background-color:#f4f4f4;"';
					$zeb=0;
				}else{
					$html.=' style="backgroound-color:#fff;"';
					$zeb=1;
				}
				$html.='>';
				$html.='<td class="col-75"><small>'.$i['code'].'</small></td>';
				$html.='<td class="col-150"><small>';
	if($ro['title']=='')
					$html.=$i['title'];
	else
					$html.=$ro['title'];
				$html.='</small></td>';
				$html.='<td class="col-150"><small>'.$c['title'].'</small></td>';
				$html.='<td class="col-50 text-center"><small>'.$ro['quantity'].'</small></td>';
				$html.='<td class="col-50 text-right"><small>'.$ro['cost'].'</small></td>';
	$st=$ro['cost']*$ro['quantity'];
				$html.='<td class="col-50 text-right">'.$st;$ot=$ot+$st;$html.='</td>';
			$html.='</tr>';
}
		$html.='</tbody>';
		$html.='<tfoot>';
			$html.='<tr style="background-color:#f0f0f0;">';
				$html.='<td colspan="3">&nbsp;</td>';
				$html.='<td class="col-75 text-right"><strong>Total</strong></td>';
				$html.='<td class="col-75 text-right '.$r['status'].'"><strong>'.$ot.'</strong></td>';
			$html.='</tr>';
		$html.='</tfoot>';
	$html.='</table>';
	$html.='<br /><br />';
	$html.='<table class="table">';
		$html.='<tbody>';
			$html.='<tr>';
				$html.='<td>';
					$html.='<h4>Notes</h4>';
					$html.='<p style="font-size:8px;">'.$r['notes'].'</p>';
				$html.='</td>';
				$html.='<td>';
					$html.='<h4>Banking Details</h4>';
					$html.='<p><small>';
						$html.='Bank: <strong>'.$config['bank'].'</strong><br />';
						$html.='Account Name: <strong>'.$config['bankAccountName'].'</strong><br />';
						$html.='Account Number: <strong>'.$config['bankAccountNumber'].'</strong><br />';
						$html.='BSB: <strong>'.$config['bankBSB'].'</strong>';
					$html.='</small></p>';
				$html.='</td>';
			$html.='</tr>';
		$html.='</tbody>';
	$html.='</table>';
$html.='</body>';
$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output(__DIR__.'/../media/orders/'.$oid.'.pdf','F');
chmod('../media/orders/'.$oid.'.pdf',0777);?>
<script>/*<![CDATA[*/
	window.top.window.$('#busy').css("display","none");
<?php if($c['email']==''||$act=='print'){?>
	window.top.window.open('media/orders/<?php echo$oid;?>.pdf');
<?php }else{
	require"class.phpmailer.php";
	$mail=new PHPMailer();
	$mail->IsSMTP();
	$toname=$c['name'];
	$mail->SetFrom($config['email'],$config['business']);
	$mail->AddAddress($c['email']);
	$mail->IsHTML(true);
	$mail->Subject='Order #'.$oid;
	$msg=$config['orderEmailLayout'];
	$msg=str_replace('{name}',$c['name'],$msg);
	$name=explode(' ',$c['name']);
	$msg=str_replace('{first}',$name[0],$msg);
	$msg=str_replace('{last}',$name[1],$msg);
	$msg=str_replace('{date}',date($config['dateFormat'],$r['ti']),$msg);
	$msg=str_replace('{order_number}',$oid,$msg);
	$msg=str_replace('{notes}',$r['notes'],$msg);
	$mail->Body=$msg;
	$mail->AltBody=$msg;
	$mail->AddAttachment('../media/orders/'.$oid.'.pdf');
	if($mail->Send()){?>
	window.top.window.$('.notifications').notify({type:'success',icon:'',message:{text:'The Order was Sent Successfully'}}).show();
<?php }else{?>
	window.top.window.$('.notifications').notify({type:'danger',icon:'',message:{text:'There was an issue sending the Order'}}).show();
<?php }
}?>
/*]]>*/</script>
