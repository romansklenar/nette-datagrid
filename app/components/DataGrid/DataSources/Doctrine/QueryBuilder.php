<?php

namespace DataGrid\DataSources\Doctrine;

use Doctrine, Doctrine\ORM\Query\Expr;

/**
 * Query Builder based data source
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
class QueryBuilder extends Mapped
{

	/** @var Doctrine\ORM\QueryBuilder */
	private $qb;

	/** @param QueryBuilder $qb */
	public function __construct(Doctrine\ORM\QueryBuilder $qb)
	{
		$this->qb = $qb;
	}

	public function filter($column, $operation = self::EQUAL, $value = NULL, $chainType = NULL)
	{
		if (!$this->hasColumn($column)) {
			throw new \InvalidArgumentException('Trying to filter data source by unknown column.');
		}

		$nextParamId = count($this->qb->getParameters()) + 1;

		if (is_array($operation)) {
			if ($chainType !== self::CHAIN_AND && $chainType !== self::CHAIN_OR) {
				throw new \InvalidArgumentException('Invalid chain operation type.');
			}
			$conds = array();
			foreach ($operation as $t) {
				$this->validateFilterOperation($t);
				if ($t === self::IS_NULL || $t === self::IS_NOT_NULL) {
					$conds[] = "{$this->mapping[$column]} $t";
				} else {
					$conds[] = "{$this->mapping[$column]} $t ?$nextParamId";
					$this->qb->setParameter(
						$nextParamId++,
						$t === self::LIKE || $t === self::NOT_LIKE ? $this->_formatValueForLikeExpr($value) : $value
					);
				}
			}

			if ($chainType === self::CHAIN_AND) {
				foreach ($conds as $cond) {
					$this->qb->andWhere($cond);
				}
			} elseif ($chainType === self::CHAIN_OR) {
				$this->qb->andWhere(new Expr\Orx($conds));
			}
		} else {
			$this->validateFilterOperation($operation);

			if ($operation === self::IS_NULL || $operation === self::IS_NOT_NULL) {
				$this->qb->andWhere("{$this->mapping[$column]} $operation");
			} else {
				$this->qb->andWhere("{$this->mapping[$column]} $operation ?$nextParamId");
				$this->qb->setParameter(
					$nextParamId,
					$operation === self::LIKE || $operation === self::NOT_LIKE ? $this->_formatValueForLikeExpr($value) : $value
				);
			}
		}
	}

	private function _formatValueForLikeExpr($value)
	{
		$value = str_replace('%', '\\%', $value); //escape wildcard character used in PDO
		$value = \Nette\String::replace($value, '~(?!\\\\)(.?)\\*~', '\\1%'); //replace asterisks
		return str_replace('\\*', '*', $value); //replace escaped asterisks
	}

	public function sort($column, $order = self::ASCENDING)
	{
		if (!$this->hasColumn($column)) {
			throw new \InvalidArgumentException('Trying to sort data source by unknown column.');
		}
		
		$this->qb->addOrderBy($this->mapping[$column], $order === self::ASCENDING ? 'ASC' : 'DESC');
	}

	public function reduce($count, $start = 0)
	{
		if ($count == NULL || $count > 0) { //intentionally ==
			$this->qb->setMaxResults($count == NULL ? NULL : $count);
		} else throw new \OutOfRangeException;

		if ($start == NULL || ($start > 0 && $start < count($this))) {
			$this->qb->setFirstResult($start == NULL ? NULL : $start);
		} else throw new \OutOfRangeException;
	}

	public function getIterator()
	{
		echo $this->qb->getDQL();dump($this->qb->getParameters());
		return new \ArrayIterator($this->qb->getQuery()->getScalarResult());
	}

	public function count()
	{
		$query = clone $this->qb->getQuery();

		$query->setHint(Doctrine\ORM\Query::HINT_CUSTOM_TREE_WALKERS, array(__NAMESPACE__ . '\Utils\CountingASTWalker'));
		$query->setMaxResults(NULL)->setFirstResult(NULL);

		return (int) $query->getSingleScalarResult();
	}

	public function getFilterItems($column)
	{
		//	Pekelník: mušeli bysme nějak implementovat tu funkci z $fluent->distinct()... což namená removeSelect() a setSelect('Distinct <column>') 
		//	Majkl: v ní se to může naklonovat, resetnout select, aplikovat distinct a selectnout
		throw new \NotImplementedException();

		//
		// Tohle je z toho původního commitu (třeba se to bude hodit...)
		// 
		//		$items = $fluent->fetchPairs($columnName, $columnName);
		//		$dataSource = $this->dataGrid->getDataSource();
		//		$dataSource->select($this->name, $dataSource::DISTINCT);
		//		$dataSource->filter(NULL);
		//		$dataSource->reduce(NULL, NULL);
		//		$iterator = $dataSource->getIterator();
		//		$items = iterator_to_array($iterator);
		//		$items = array_combine($items, $items);
	}

}
