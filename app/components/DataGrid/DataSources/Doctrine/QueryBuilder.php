<?php

namespace DataGrid\DataSources\Doctrine;

use Doctrine;

/**
 * Query Builder based data source
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
class QueryBuilder extends Mapped
{

	/** @var Doctrine\ORM\QueryBuilder */
	private $qb;

	/**
	 * @param QueryBuilder $qb
	 */
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

		if (is_array($type)) {
			if ($chainType !== self::CHAIN_AND && $chainType !== self::CHAIN_OR) {
				throw new \InvalidArgumentException('Invalid chain operation type.');
			}
			$conds = array();
			$paramUsed = FALSE;
			foreach ($type as $t) {
				$this->validateFilterOperation($t);
				if ($t === self::IS_NULL || $t === self::IS_NOT_NULL) {
					$conds[] = "$column $t";
				} else {
					$conds[] = "$column $t ?$nextParamId";
					$paramUsed = TRUE;
				}
			}

			if ($chainType === self::CHAIN_AND) {
				foreach ($conds as $cond) {
					$this->qb->andWhere($cond);
				}
			} elseif ($chainType === self::CHAIN_OR) {
				$this->qb->andWhere(new Expr\Orx($conds));
			}

			$paramUsed && $this->qb->setParameter($nextParamId++, $value);
		} else {
			$this->validateFilterOperation($type);

			if ($type === self::IS_NULL || $type === self::IS_NOT_NULL) {
				$this->qb->andWhere("$column $type");
			} else {
				$this->qb->andWhere("$column $type ?$nextParamId")->setParameter($nextParamId, $value);
			}
		}
	}

	public function sort($column, $order = self::ASCENDING)
	{
		$this->qb->addOrderBy($column, $order === self::ASCENDING ? 'ASC' : 'DESC');
	}

	public function reduce($count, $start = 0)
	{
		if (($count !== NULL && $count < 1) || ($start !== NULL && ($start < 0 || $start >= count($this)))) {
			throw new \OutOfRangeException;
		}
		$this->qb->setMaxResults($count)->setFirstResult($start);
	}

	public function getIterator()
	{
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
