<?php /* Smarty version Smarty-3.1.7, created on 2014-05-20 21:46:56
         compiled from "C:\xampp\htdocs\vtigerCRM\includes\runtime/../../layouts/vlayout\modules\Install\InstallPreProcess.tpl" */ ?>
<?php /*%%SmartyHeaderCode:12716537bbf40272cf6-83356139%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '63afde72be8899c4ac093bcd8940f224c66e64a6' => 
    array (
      0 => 'C:\\xampp\\htdocs\\vtigerCRM\\includes\\runtime/../../layouts/vlayout\\modules\\Install\\InstallPreProcess.tpl',
      1 => 1373768346,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12716537bbf40272cf6-83356139',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537bbf4027e87',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537bbf4027e87')) {function content_537bbf4027e87($_smarty_tpl) {?>
<input type="hidden" id="module" value="Install" />
<input type="hidden" id="view" value="Index" />
<div class="container-fluid page-container">
	<div class="row-fluid">
		<div class="span6">
			<div class="logo">
				<img src="<?php echo vimage_path('vt1.png');?>
"/>
			</div>
		</div>
		<div class="span6">
			<div class="head pull-right">
				<h3><?php echo vtranslate('LBL_INSTALLATION_WIZARD','Install');?>
</h3>
			</div>
		</div>
	</div>
<?php }} ?>