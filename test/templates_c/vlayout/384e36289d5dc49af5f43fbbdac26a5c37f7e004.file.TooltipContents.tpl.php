<?php /* Smarty version Smarty-3.1.7, created on 2014-05-30 14:24:40
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/TooltipContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:430117373538894a8e082b7-16333837%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '384e36289d5dc49af5f43fbbdac26a5c37f7e004' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/TooltipContents.tpl',
      1 => 1401295282,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '430117373538894a8e082b7-16333837',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD_STRUCTURE' => 0,
    'FIELD_MODEL' => 0,
    'MODULE' => 0,
    'MODULE_NAME' => 0,
    'USER_MODEL' => 0,
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538894a8ee065',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538894a8ee065')) {function content_538894a8ee065($_smarty_tpl) {?>
<div class="detailViewInfo"><table class="table table-bordered equalSplit detailview-table"><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['RECORD_STRUCTURE']->value['TOOLTIP_FIELDS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
?><tr><td class="fieldLabel narrowWidthType" nowrap><label class="muted marginRight10px"><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label></td><td class="fieldValue narrowWidthType"><span class="value"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getUITypeModel()->getDetailViewTemplateName(),$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('FIELD_MODEL'=>$_smarty_tpl->tpl_vars['FIELD_MODEL']->value,'USER_MODEL'=>$_smarty_tpl->tpl_vars['USER_MODEL']->value,'MODULE'=>$_smarty_tpl->tpl_vars['MODULE_NAME']->value,'RECORD'=>$_smarty_tpl->tpl_vars['RECORD']->value), 0);?>
</span></td></tr><?php } ?></table></div>
<?php }} ?>