<?php


// budoucí metoda Form::addDatePicker()
function Form_addDatePicker(Nette\Forms\Form $_this, $name, $label, $cols = NULL, $maxLength = NULL)
{
	return $_this[$name] = new DatePicker($label, $cols, $maxLength);
}

//Form::extensionMethod('Form::addDatePicker', 'Form_addDatePicker'); // v PHP 5.2
Nette\Forms\Form::extensionMethod('addDatePicker', 'Form_addDatePicker'); // v PHP 5.3


// Funkčnost $form['element']->setReadonly();
function FormControl_setReadOnly(Nette\Forms\FormControl $_this)
{
	$_this->getControlPrototype()->readonly = TRUE;
}

//FormControl::extensionMethod('FormControl::setReadOnly', 'FormControl_setReadOnly'); // v PHP 5.2
Nette\Forms\FormControl::extensionMethod('setReadOnly', 'FormControl_setReadOnly'); // v PHP 5.3


/*
function DibiDataSource_flush(DibiDataSource $_this)
{
	$_this->select(array())->where(array())->orderBy(array())->release();
}
DibiDataSource::extensionMethod('DibiDataSource::flush', 'DibiDataSource_flush');
*/