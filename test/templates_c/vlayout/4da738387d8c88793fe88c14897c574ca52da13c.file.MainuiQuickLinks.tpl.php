<?php /* Smarty version Smarty-3.1.7, created on 2014-06-17 19:10:00
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\MailManager\MainuiQuickLinks.tpl" */ ?>
<?php /*%%SmartyHeaderCode:32445537bcd3a5ccdd0-40334568%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4da738387d8c88793fe88c14897c574ca52da13c' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\MailManager\\MainuiQuickLinks.tpl',
      1 => 1401997391,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '32445537bcd3a5ccdd0-40334568',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537bcd3a5dc7d',
  'variables' => 
  array (
    'MAILBOX' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537bcd3a5dc7d')) {function content_537bcd3a5dc7d($_smarty_tpl) {?>

<div class="quickWidget">
	<div class="accordion-heading accordion-toggle quickWidgetHeader">
		<h5 class="title widgetTextOverflowEllipsis"><?php echo vtranslate('LBL_Mailbox','MailManager');?>
</h5>
		<div class="clearfix"></div>
	</div>
	<?php if ($_smarty_tpl->tpl_vars['MAILBOX']->value&&$_smarty_tpl->tpl_vars['MAILBOX']->value->exists()){?>
		<div class="widgetContainer accordion-body collapse in">
			<input type=hidden name="mm_selected_folder" id="mm_selected_folder">
			<input type="hidden" name="_folder" id="mailbox_folder">
			<div>
				<div class="row-fluid">
					<div class="span10">
						<ul class="nav nav-list">
							<li>
								<a href="javascript:void(0);" onclick="MailManager.mail_compose();"><?php echo vtranslate('LBL_Compose','MailManager');?>
</a>
							</li>
							<li>
								<a href='#Reload' id="_mailfolder_mm_reload" onclick="MailManager.reload_now();"><?php echo vtranslate('LBL_Refresh','MailManager');?>
</a>
							</li>
							<li>
								<a href='#Settings' id="_mailfolder_mm_settings" onclick="MailManager.open_settings();"><?php echo vtranslate('JSLBL_Settings','MailManager');?>
</a>
							</li>
							<li>
								<a href="#Drafts" id="_mailfolder_mm_drafts" onclick="MailManager.folder_drafts(0);"><?php echo vtranslate('LBL_Drafts','MailManager');?>
</a>
							</li>
						</ul>
					</div>				
				</div>
			</div>
		</div>
	<?php }?>
</div>
<?php }} ?>