<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 19:52:15
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\ImportAdvanced.tpl" */ ?>
<?php /*%%SmartyHeaderCode:31351539f4aef3ad375-50624490%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '72b0936c1c1cc531672496b84c528cad212045fd' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\ImportAdvanced.tpl',
      1 => 1401997405,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '31351539f4aef3ad375-50624490',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'USER_INPUT' => 0,
    'HAS_HEADER' => 0,
    'MERGE_FIELDS' => 0,
    'ENCODED_MANDATORY_FIELDS' => 0,
    'MODULE' => 0,
    'ERROR_MESSAGE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f4aef416b1',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f4aef416b1')) {function content_539f4aef416b1($_smarty_tpl) {?>
<div class="contentsDiv span10 marginLeftZero"><form action="index.php" enctype="multipart/form-data" method="POST" name="importAdvanced"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
" /><input type="hidden" name="view" value="Import" /><input type="hidden" name="mode" value="import" /><input type="hidden" name="type" value="<?php echo $_smarty_tpl->tpl_vars['USER_INPUT']->value->get('type');?>
" /><input type="hidden" name="has_header" value='<?php echo $_smarty_tpl->tpl_vars['HAS_HEADER']->value;?>
' /><input type="hidden" name="file_encoding" value='<?php echo $_smarty_tpl->tpl_vars['USER_INPUT']->value->get('file_encoding');?>
' /><input type="hidden" name="delimiter" value='<?php echo $_smarty_tpl->tpl_vars['USER_INPUT']->value->get('delimiter');?>
' /><input type="hidden" name="merge_type" value='<?php echo $_smarty_tpl->tpl_vars['USER_INPUT']->value->get('merge_type');?>
' /><input type="hidden" name="merge_fields" value='<?php echo $_smarty_tpl->tpl_vars['MERGE_FIELDS']->value;?>
' /><input type="hidden" id="mandatory_fields" name="mandatory_fields" value='<?php echo $_smarty_tpl->tpl_vars['ENCODED_MANDATORY_FIELDS']->value;?>
' /><table style="width:100%;margin-left:auto;margin-right:auto;" cellpadding="2" cellspacing="12" class="searchUIBasic"><tr><td class="font-x-large" align="left" colspan="2"><strong><?php echo vtranslate('LBL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate($_smarty_tpl->tpl_vars['FOR_MODULE']->value,$_smarty_tpl->tpl_vars['FOR_MODULE']->value);?>
</strong></td></tr><?php if ($_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value!=''){?><tr><td class="style1" align="left" colspan="2"><?php echo $_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value;?>
</td></tr><?php }?><tr><td class="leftFormBorder1" colspan="2" valign="top"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('Import_Step4.tpl','Import'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td></tr><tr><td align="right" colspan="2"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('Import_Advanced_Buttons.tpl','Import'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td></tr></table></form></div><?php }} ?>