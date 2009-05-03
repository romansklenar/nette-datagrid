<?php

require_once dirname(__FILE__) . '/DataGridRenderer.php';



/**
 * Converts a data grid into the HTML output.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář
 * @example    http://nettephp.com/extras/datagrid
 * @package    Nette\Extras\DataGrid
 * @version    $Id: DataGridRenderer.php 7 2009-05-02 22:09:15Z RSklenar@seznam.cz $
 */
class CustomDataGridRenderer extends DataGridRenderer
{
	/** @var array  of HTML tags */
	public $wrappers = array(
		'form' => array(
			'container' => 'class=gridform',
			'errors' => TRUE,
		),
		
		'error' => array(
			'container' => 'ul class=error',
			'item' => 'li',
		),
		
		'grid' => array(
			'container' => 'table class="grid ui-widget ui-widget-content ui-corner-all"',
		),
		
		'row.header' => array(
			'container' => 'tr class="header ui-widget-header"',
			'cell' => array(
				'container' => 'th', // .checker, .action
			),
		),
		
		'row.filter' => array(
			'container' => 'tr class=filters',
			'cell' => array(
				'container' => 'td', // .action
			),
			'control' => array(
				'.input' => 'ui-widget-content',
				'.select' => 'ui-widget-content',
				'.submit' => 'ui-widget-content',
			),
		),
		
		'row.content' => array(
			'container' => 'tr', // .even, .selected
			'.even' => 'ui-state-hover',
			'cell' => array(
				'container' => 'td', // .checker, .action
			),
		),
		
		'row.footer' => array(
			'container' => 'tr class=footer',
			'cell' => array(
				'container' => 'th', // .action
			),
		),
	
	);

	
	/**
	 * @param  string
	 * @return Html
	 */
	protected function getWrapper($name)
	{
		$data = $this->getValue($name);		
		if ($data instanceof Html) return clone $data;
		$el = Html::el($data);

		$pattern = '/(?<attr>[\w]+)="(?<value>[\w|\s|_| |-]+)"/i';
		if (preg_match_all($pattern, $data, $matches)) { 
			$attrs = array();
			foreach ($matches['attr'] as $key => $attr) {
				$attrs[$attr] = explode(' ', $matches['value'][$key]);
			}
			$el->attrs = $attrs;
		}
		return $el;
	}
}