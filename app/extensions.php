<?php 

/**
 * Extension methods difinition file.
 *
 * @copyright  Copyright (c) 2009 TOPSPIN, s.r.o.
 * @package    PodaciDenik
 * @version    $Id$
 */



// budoucí metoda Form::addDatePicker()
function Form_addDatePicker(Form $_this, $name, $label, $cols = NULL, $maxLength = NULL)
{
	return $_this[$name] = new DatePicker($label, $cols, $maxLength);
}

Form::extensionMethod('Form::addDatePicker', 'Form_addDatePicker'); // v PHP 5.2
//Form::extensionMethod('addDatePicker', 'Form_addDatePicker'); // v PHP 5.3


// Funkčnost $form['element']->setReadonly();
function FormControl_setReadOnly(FormControl $_this)
{
	$_this->getControlPrototype()->readonly = TRUE;
}

FormControl::extensionMethod('FormControl::setReadOnly', 'FormControl_setReadOnly'); // v PHP 5.2


/*
function DibiDataSource_flush(DibiDataSource $_this)
{
	$_this->select(array())->where(array())->orderBy(array())->release();
}
DibiDataSource::extensionMethod('DibiDataSource::flush', 'DibiDataSource_flush');
*/