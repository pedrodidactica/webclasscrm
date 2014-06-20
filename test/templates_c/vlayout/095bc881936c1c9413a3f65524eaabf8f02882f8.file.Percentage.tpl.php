<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 19:46:28
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\uitypes\Percentage.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2570538391c18224a0-67169436%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '095bc881936c1c9413a3f65524eaabf8f02882f8' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\uitypes\\Percentage.tpl',
      1 => 1401997451,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2570538391c18224a0-67169436',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538391c186c84',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'MODULE' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538391c186c84')) {function content_538391c186c84($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo())), null, 0);?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><div class="input-append"><input id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name');?>
" type="number" class="input-medium" data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName();?>
"value="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue');?>
" data-fieldinfo='<?php echo $_smarty_tpl->tpl_vars['FIELD_INFO']->value;?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator='<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
'<?php }?> step="any" /><span class="add-on">%</span></div><?php }} ?>