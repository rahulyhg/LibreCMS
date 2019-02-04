<?php
/*
 * LibreCMS - Copyright (C) Diemen Design 2018
 * This software may be modified and distributed under the terms
 * of the MIT license (http://opensource.org/licenses/MIT).
 */
$getcfg=true;
require_once'db.php';
echo'<script>/*<![CDATA[*/window.top.window.$("#notification").html("");';
$id=isset($_POST['id'])?filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT):filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
$t=isset($_POST['t'])?filter_input(INPUT_POST,'t',FILTER_SANITIZE_STRING):filter_input(INPUT_GET,'t',FILTER_SANITIZE_STRING);
$c=isset($_POST['c'])?filter_input(INPUT_POST,'c',FILTER_SANITIZE_STRING):filter_input(INPUT_GET,'c',FILTER_SANITIZE_STRING);
$s=$db->prepare("SELECT file FROM ".$prefix."$t WHERE id=:id");
$s->execute(array(':id'=>$id));
$r=$s->fetch(PDO::FETCH_ASSOC);
if($r['file']!=''){
  switch($c){
    case'exifFilename':
      $out=basename($r['file']);
      break;
    case'exifCamera':
      $out='Camera';
      break;
    case'exifLens':
      $out='Lens';
      break;
    case'exifAperture':
      $out='Aperture';
      break;
    case'exifFocalLength':
      $out='Focal Length';
      break;
    case'exifShutterSpeed':
      $out='Shutter Speed';
      break;
    case'exifISO':
      $out='ISO';
      break;
    case'exifti':
      $out=time();
      break;
    default:
      $out='nothing';
  }
  $s=$db->prepare("UPDATE ".$prefix."$t SET $c=:out WHERE id=:id");
  $s->execute(
    array(
      ':id'=>$id,
      ':out'=>$out
    )
  );?>
  window.top.window.$('#<?php echo$c;?>').val('<?php echo$out;?>');
<?php }else{?>
  window.top.window.$('#notification').html('<div class="alert alert-info">There is no image to get the EXIF Info from.</div>');
<?php }?>
  window.top.window.Pace.stop();
<?php
echo'/*]]>*/</script>';
