<?php


abstract class BasePresenter extends /*Nette\Application\*/Presenter
{

	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		return $template;
	}

}
