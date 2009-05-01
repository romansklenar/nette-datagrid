<?php


require_once LIBS_DIR . '/Nette/Forms/Controls/TextInput.php';


/**
 * DatePicker input control.
 *
 * @author     Tomáš Kraina, Roman Sklenář
 * @package    Nette\Extras
 */
class DatePicker extends /*Nette\Forms\*/TextInput
{

	/**
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label, $cols = NULL, $maxLenght = NULL)
	{
		parent::__construct($label, $cols, $maxLenght);
	}


	/**
	 * Returns control's value.
	 * @return mixed 
	 */
	public function getValue()
	{
		if (strlen($this->value)) {
			$tmp = preg_replace('~([[:space:]])~', '', $this->value);
			$tmp = explode('.', $tmp);
			
			// database format Y-m-d
			return $tmp[2] . '-' . $tmp[1] . '-' . $tmp[0];
		}
		
		return $this->value;
	}


	/**
	 * Sets control's value.
	 * @param  string
	 * @return void
	 */
	public function setValue($value)
	{
		$value = preg_replace('~([0-9]{4})-([0-9]{2})-([0-9]{2})~', '$3.$2.$1', $value);
		parent::setValue($value);
	}


	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{		
		$control = parent::getControl();
		$control->class = 'datepicker';
		
		return $control;
	}

}