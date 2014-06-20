<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 20:06:57
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\Import_Finish_Buttons.tpl" */ ?>
<?php /*%%SmartyHeaderCode:20850539f4e616991a9-54973533%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b811269f026b34717762d261c13fad5759e329c4' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\Import_Finish_Buttons.tpl',
      1 => 1401997403,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20850539f4e616991a9-54973533',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'MODULE' => 0,
    'OWNER_ID' => 0,
    'MERGE_ENABLED' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f4e616d3b3',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f4e616d3b3')) {function content_539f4e616d3b3($_smarty_tpl) {?>

<button name="next" class="create btn"
	   onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=Import&return_module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&return_action=index'" ><strong><?php echo vtranslate('LBL_IMPORT_MORE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button>
&nbsp;&nbsp;
<button name="next" class="cancel btn"
		onclick="return window.open('index.php?module=<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
&for_module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=List&start=1&foruser=<?php echo $_smarty_tpl->tpl_vars['OWNER_ID']->value;?>
','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');"><strong><?php echo vtranslate('LBL_VIEW_LAST_IMPORTED_RECORDS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button>
&nbsp;&nbsp;
<?php if ($_smarty_tpl->tpl_vars['MERGE_ENABLED']->value=='0'){?>
<button name="next" class="delete btn"
		onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=Import&mode=undoImport&foruser=<?php echo $_smarty_tpl->tpl_vars['OWNER_ID']->value;?>
'"><strong><?php echo vtranslate('LBL_UNDO_LAST_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button>
&nbsp;&nbsp;
<?php }?>
<button name="cancel" class="edit btn btn-success"
		onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=List'"><strong><?php echo vtranslate('LBL_FINISH_BUTTON_LABEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php }} ?>