<?php

namespace DataGrid\Columns;
use Nette;

/**
 * Representation of textual data grid column.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class TextColumn extends Column
{
	/**
	 * Formats cell's content.
	 * @param  mixed
	 * @param  \DibiRow|array
	 * @return string
	 */
	public function formatContent($value, $data = NULL)
	{
		$value = htmlSpecialChars($value);

		if (is_array($this->replacement) && !empty($this->replacement)) {
			if (in_array($value, array_keys($this->replacement))) {
				$value = $this->replacement[$value];
			}
		}

		foreach ($this->formatCallback as $callback) {
			if (is_callable($callback)) {
				$value = call_user_func($callback, $value, $data);
			}
		}

		// translate & truncate
		if ($value instanceof Nette\Web\Html) {
			$text = $this->dataGrid->translate($value->getText());
			if ($this->maxLength != 0) {
				$text = Nette\String::truncate($text, $this->maxLength);
			}
			$value->setText($text);
			$value->title = $this->dataGrid->translate($value->title);

		} else {
			if ($this->maxLength != 0) {
				$value = Nette\String::truncate($value, $this->maxLength);
			}
		}

		return $value;
	}


	/**
	 * Filters data source.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		if (!$this->hasFilter()) return;

		$dataGrid = $this->getDataGrid(TRUE);

		if (strstr($value, '*')) {
			// rewrite asterix to regex usage (*str -> *str$, str* -> ^str*, st*r -> st.*r)
			$f = $value[0];
			$l = $value[strlen($value)-1];
			if ($f == '*' && $l == '*') $value = "^$value$";
			elseif ($f == '*' && $l != '$') $value = "$value$";
			elseif ($l == '*' && $f != '^') $value = "^$value";
			$value = str_replace('.*', '*', $value);
			$value = str_replace('*', '.*', $value);

			$dataGrid->getDataSource()->filter($this->name, $value, 'LIKE'); //asterisks are converted internally

		} elseif ($value === 'NULL' || $value === 'NOT NULL') {
			
			$dataGrid->getDataSource()->filter($this->name, NULL, "IS $value");

		} else {

			$dataGrid->getDataSource()->filter($this->name, "*$value*", 'LIKE');
		}
	}
}
