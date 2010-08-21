<?php

namespace DataGrid\DataSources\Doctrine\Utils;
use Doctrine, Doctrine\ORM\Query\AST;

class CountingASTWalker extends Doctrine\ORM\Query\TreeWalkerAdapter
{
	public function walkSelectStatement(AST\SelectStatement $ast)
	{
			$parent = $parentName = NULL;
			foreach ($this->_getQueryComponents() AS $dqlAlias => $qComp) {
				if (array_key_exists('parent', $qComp) && $qComp['parent'] === NULL && $qComp['nestingLevel'] == 0) {
					$parent = $qComp;
					$parentName = $dqlAlias;
					break;
				}
			}

			$pathExpression = new AST\PathExpression(
				AST\PathExpression::TYPE_STATE_FIELD | AST\PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION, $parentName, $parent['metadata']->getSingleIdentifierFieldName()
			);
			$pathExpression->type = AST\PathExpression::TYPE_STATE_FIELD;

			$ast->selectClause->selectExpressions = array(
				new AST\SelectExpression(
					new AST\AggregateExpression('count', $pathExpression, FALSE), NULL
				)
			);
			
			$ast->orderByClause = array(); //reset ORDER BY clause, it is not necessary
	}
}