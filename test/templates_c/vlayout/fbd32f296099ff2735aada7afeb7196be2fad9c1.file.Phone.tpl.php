<?php /* Smarty version Smarty-3.1.7, created on 2014-06-09 19:56:15
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\uitypes\Phone.tpl" */ ?>
<?php /*%%SmartyHeaderCode:9416537dfd64926d75-42614590%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fbd32f296099ff2735aada7afeb7196be2fad9c1' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\uitypes\\Phone.tpl',
      1 => 1401997450,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9416537dfd64926d75-42614590',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537dfd6494a00',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'MODULE' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537dfd6494a00')) {function content_537dfd6494a00($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo())), null, 0);?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><input id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name');?>
" type="text" class="input-large" data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName();?>
"value="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue');?>
" data-fieldinfo='<?php echo $_smarty_tpl->tpl_vars['FIELD_INFO']->value;?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator=<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
<?php }?> /><?php }} ?>