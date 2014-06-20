<?php /* Smarty version Smarty-3.1.7, created on 2014-06-13 15:10:06
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\uitypes\Number.tpl" */ ?>
<?php /*%%SmartyHeaderCode:4109537dfdc12f2278-47248528%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '806a970932a4fb8e3883c3a66077b349d78eb0fb' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\uitypes\\Number.tpl',
      1 => 1401997456,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4109537dfdc12f2278-47248528',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537dfdc135ba1',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'MODULE' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537dfdc135ba1')) {function content_537dfdc135ba1($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo())), null, 0);?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><input id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name');?>
" type="text" class="input-large" data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName();?>
"value="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue');?>
" data-fieldinfo='<?php echo $_smarty_tpl->tpl_vars['FIELD_INFO']->value;?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator=<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
<?php }?> /><?php }} ?>