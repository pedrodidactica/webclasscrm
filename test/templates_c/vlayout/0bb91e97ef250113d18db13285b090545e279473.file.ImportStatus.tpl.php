<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 20:34:58
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\ImportStatus.tpl" */ ?>
<?php /*%%SmartyHeaderCode:27889539f54f26ff3f4-59731199%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0bb91e97ef250113d18db13285b090545e279473' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\ImportStatus.tpl',
      1 => 1401997404,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '27889539f54f26ff3f4-59731199',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'CONTINUE_IMPORT' => 0,
    'MODULE' => 0,
    'ERROR_MESSAGE' => 0,
    'IMPORT_RESULT' => 0,
    'INVENTORY_MODULES' => 0,
    'IMPORT_ID' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f54f278be1',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f54f278be1')) {function content_539f54f278be1($_smarty_tpl) {?>

<script type="text/javascript">
jQuery(document).ready(function() {
	setTimeout(function() {
		jQuery("[name=importStatusForm]").get(0).submit();
		}, 2000);
});
</script>
<div class="contentsDiv span10 marginLeftZero"><form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importStatusForm"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
" /><input type="hidden" name="view" value="Import" /><?php if ($_smarty_tpl->tpl_vars['CONTINUE_IMPORT']->value=='true'){?><input type="hidden" name="mode" value="continueImport" /><?php }else{ ?><input type="hidden" name="mode" value="" /><?php }?></form><table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well"><tr><td class="font-x-large" align="left" colspan="2"><?php echo vtranslate('LBL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate($_smarty_tpl->tpl_vars['FOR_MODULE']->value,$_smarty_tpl->tpl_vars['FOR_MODULE']->value);?>
 -<span class="redColor"><?php echo vtranslate('LBL_RUNNING',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 ... </span></td></tr><?php if ($_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value!=''){?><tr><td class="style1" align="left" colspan="2"><?php echo $_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value;?>
</td></tr><?php }?><tr><td valign="top"><table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents"><tr><td><?php echo vtranslate('LBL_TOTAL_RECORDS_IMPORTED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td width="10%">:</td><td width="30%"><?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['IMPORTED'];?>
 / <?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['TOTAL'];?>
</td></tr><tr><td colspan="3"><table cellpadding="10" cellspacing="0" class="calDayHour"><tr><td><?php echo vtranslate('LBL_NUMBER_OF_RECORDS_CREATED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td width="10%">:</td><td width="10%"><?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['CREATED'];?>
</td></tr><tr><td><?php echo vtranslate('LBL_NUMBER_OF_RECORDS_UPDATED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td width="10%">:</td><td width="10%"><?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['UPDATED'];?>
</td></tr><?php if (in_array($_smarty_tpl->tpl_vars['FOR_MODULE']->value,$_smarty_tpl->tpl_vars['INVENTORY_MODULES']->value)==false){?><tr><td><?php echo vtranslate('LBL_NUMBER_OF_RECORDS_SKIPPED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td width="10%">:</td><td width="10%"><?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['SKIPPED'];?>
</td></tr><tr><td><?php echo vtranslate('LBL_NUMBER_OF_RECORDS_MERGED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td width="10%">:</td><td width="10%"><?php echo $_smarty_tpl->tpl_vars['IMPORT_RESULT']->value['MERGED'];?>
</td></tr><?php }?></table></td></tr></table></td></tr><tr><td align="right"><button name="cancel" class="delete btn btn-danger"onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=Import&mode=cancelImport&import_id=<?php echo $_smarty_tpl->tpl_vars['IMPORT_ID']->value;?>
'"><strong><?php echo vtranslate('LBL_CANCEL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></td></tr></table></div><?php }} ?>