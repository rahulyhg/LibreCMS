<?php
/**
 * LibreCMS - Copyright (C) Diemen Design 2019
 *
 * Core - Add Postage Option
 *
 * add_postoption.php version 2.0.2
 *
 * LICENSE: This source file may be modifired and distributed under the terms of
 * the MIT license that is available through the world-wide-web at the following
 * URI: http://opensource.org/licenses/MIT.  If you did not receive a copy of
 * the MIT License and are unable to obtain it through the web, please
 * check the root folder of the project for a copy.
 *
 * @category   Administration - Core - Add Postage Option
 * @package    core/add_postoption.php
 * @author     Dennis Suitters <dennis@diemen.design>
 * @copyright  2014-2019 Diemen Design
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @version    2.0.2
 * @link       https://github.com/DiemenDesign/LibreCMS
 * @notes      This PHP Script is designed to be executed using PHP 7+
 * @changes    v2.0.1 Create File
 * @changes    v2.0.2 Add i18n.
 * @changes    v2.0.2 Fix ARIA Attributes.
 */
if(session_status()==PHP_SESSION_NONE)session_start();
require'db.php';
$config=$db->query("SELECT * FROM `".$prefix."config` WHERE id='1'")->fetch(PDO::FETCH_ASSOC);
function svg($svg,$class=null,$size=null){
	echo'<i class="libre'.($size!=null?' libre-'.$size:'').($class!=null?' '.$class:'').'">'.file_get_contents('svg'.DS.$svg.'.svg').'</i>';
}
function localize($t){
  static $tr=NULL;
  global $config;
  if(is_null($tr)){
    if(file_exists('i18n'.DS.$config['language'].'.txt'))
      $lf='i18n'.DS.$config['language'].'.txt';
    else
      $lf='i18n'.DS.'en-AU.txt';
    $lfc=file_get_contents($lf);
    $tr=json_decode($lfc,true);
  }
  if(is_array($tr)){
    if(!array_key_exists($t,$tr))
      echo'Error: No "'.$t,'" Key in '.$config['language'];
    else
      return$tr[$t];
  }else
    echo'Error: '.$config['language'].' is malformed';
}
$t=isset($_POST['t'])?filter_input(INPUT_POST,'t',FILTER_SANITIZE_STRING):'';
$v=isset($_POST['v'])?filter_input(INPUT_POST,'v',FILTER_SANITIZE_STRING):0;
if($t!=''){
  $s=$db->prepare("INSERT INTO `".$prefix."choices` (rid,contentType,title,value) VALUES (0,'postoption',:t,:v)");
  $s->execute([':t'=>$t,':v'=>$v]);
  if($v==0)$v='';
  $id=$db->lastInsertId();?>
<script>
  window.top.window.$('#postoption').append('<div id="l_<?php echo$id;?>" class="form-group row"><div class="input-group col"><span class="input-group-text"><?php echo localize('Service');?></span><input type="text" class="form-control" name="service" value="<?php echo$t;?>" readonly role="textbox"><span class="input-group-text"><?php echo localize('Cost');?></span><input type="text" class="form-control" name="cost" value="<?php echo$v;?>" readonly role="textbox"><div class="input-group-append"><form target="sp" action="core/purge.php" role="form"><input type="hidden" name="id" value="<?php echo$id;?>" role="textbox"><input type="hidden" name="t" value="choices" role="textbox"><button class="btn btn-secondary trash" data-tooltip="tooltip" title="<?php echo localize('Delete');?>" role="button" aria-label="'.localize('aria_delete').'"><?php svg('libre-gui-trash');?></button></form></div></div></div>');
</script>
<?php }
