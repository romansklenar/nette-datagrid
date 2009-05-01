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
require_once LIBS_DIR . '\dibi\dibi.php';

function DibiDataSource_releaseOrder(DibiDataSource $_this)
{
	$_this->sorting = array();
}
DibiDataSource::extensionMethod('DibiDataSource::releaseOrder', 'DibiDataSource_releaseOrder');
*/