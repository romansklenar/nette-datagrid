<?php

/**
 * Example empty presenter.
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class CustomerPresenter extends BasePresenter
{
	public function startup()
	{
		$this->flashMessage("Sorry, this is DataGrid demo application only.", 'info');
		$this->redirect('Example:default');
	}
}