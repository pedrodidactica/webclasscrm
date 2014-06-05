<?php /* Smarty version Smarty-3.1.7, created on 2014-06-02 19:34:41
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Calendar/CalendarSharedUsers.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2033042807538cd1d1181be2-43561700%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '400466ac4b1cb040217b56f1575cd61d811f026b' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Calendar/CalendarSharedUsers.tpl',
      1 => 1401295277,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2033042807538cd1d1181be2-43561700',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'CURRENTUSER_MODEL' => 0,
    'MODULE' => 0,
    'SHAREDUSERS' => 0,
    'ID' => 0,
    'USER' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538cd1d11f874',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538cd1d11f874')) {function content_538cd1d11f874($_smarty_tpl) {?>
<div name='calendarViewTypes'><div id="calendarview-feeds" style="margin-left:10px;"><label class="checkbox" style="text-shadow: none"><input type="checkbox" data-calendar-sourcekey="Events33_<?php echo $_smarty_tpl->tpl_vars['CURRENTUSER_MODEL']->value->getId();?>
" data-calendar-feed="Events" data-calendar-userid="<?php echo $_smarty_tpl->tpl_vars['CURRENTUSER_MODEL']->value->getId();?>
" > <span class="label" style="text-shadow: none"><?php echo vtranslate('LBL_MINE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></label><?php  $_smarty_tpl->tpl_vars['USER'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['USER']->_loop = false;
 $_smarty_tpl->tpl_vars['ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SHAREDUSERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['USER']->key => $_smarty_tpl->tpl_vars['USER']->value){
$_smarty_tpl->tpl_vars['USER']->_loop = true;
 $_smarty_tpl->tpl_vars['ID']->value = $_smarty_tpl->tpl_vars['USER']->key;
?><label class="checkbox"><input type="checkbox" data-calendar-sourcekey="Events33_<?php echo $_smarty_tpl->tpl_vars['ID']->value;?>
" data-calendar-feed="Events" data-calendar-userid="<?php echo $_smarty_tpl->tpl_vars['ID']->value;?>
" > <span class="label" style="text-shadow: none"><?php echo $_smarty_tpl->tpl_vars['USER']->value;?>
</span></label><?php } ?></div></div>
<script type="text/javascript">
jQuery(document).ready(function() {
	SharedCalendar_SharedCalendarView_Js.initiateCalendarFeeds();
});
</script><?php }} ?>