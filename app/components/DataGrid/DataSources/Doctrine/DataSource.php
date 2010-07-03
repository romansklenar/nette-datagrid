<?php

namespace DataGrid\DataSources\Doctrine;
use Nette, Doctrine, DataGrid,
	Doctrine\ORM\Query\Expr;

/**
 * Doctrine 2 data source for DataGrid
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
class DataSource extends Nette\Object implements DataGrid\IDataSource
{
	/** @var Doctrine\ORM\QueryBuilder */
	private $_qb;

	public function __construct($query)
	{
		if ($query instanceof Doctrine\ORM\QueryBuilder) {
			if (!$query->getDQLPart('from')) {
				throw new \InvalidStateException('Doctrine\ORM\QueryBuilder instance does not contain any "from" part');
			}
			$this->_qb = $query;
		} elseif (is_string($query)) {
			$this->_qb = Nette\Environment::getEntityManager()->createQueryBuilder()->from($qb); /** @todo */
		} else {
			throw new \InvalidArgumentException;
		}
	}

	public function select($columns)
	{
		$this->_qb->addSelect($columns);
	}

	protected function isValidFilterOperation($operation)
	{
		return in_array($operation, array(
			self::EQUAL,
			self::NOT_EQUAL,
			self::GREATER,
			self::GREATER_OR_EQUAL,
			self::SMALLER,
			self::SMALLER_OR_EQUAL,
			self::LIKE,
			self::NOT_LIKE,
		));
	}

	public function filter($column, $value, $type = self::EQUAL)
	{
		if (!$this->isValidFilterOperation($type)) {
			throw new \InvalidArgumentException('Invalid filter operation type.');
		}
		$nextParamId = count($this->_qb->getParameters()) + 1;
		$this->_qb->andWhere("$column $type ?$nextParamId")->setParameter($nextParamId, $value);
	}

	public function sort($column, $order = self::ASCENDING)
	{
		$this->_qb->addOrderBy($column, $order === self::ASCENDING ? 'ASC' : 'DESC');
	}

	public function reduce($count, $start = 0)
	{
		if ($count < 1 || $start < 0 || $start >= count($this)) {
			throw new \OutOfRangeException;
		}
		$this->_qb->setMaxResults($count)->setFirstResult($start);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_qb->getQuery()->getScalarResult());
	}

	public function count()
	{
		$query = clone $this->_qb->getQuery();

		$query->setHint(Doctrine\ORM\Query::HINT_CUSTOM_TREE_WALKERS, array(__NAMESPACE__ . '\CountingASTWalker'));
		$query->setMaxResults(NULL)->setFirstResult(NULL);

        return (int) $query->getSingleScalarResult();
	}
}