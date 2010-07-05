<?php

namespace DataGrid\DataSources\Doctrine;
use Nette, Doctrine, DataGrid,
	Doctrine\ORM\Query\Expr;

/**
 * Base class for Doctrine2 based data sources
 * 
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
abstract class MappedDataSource extends Nette\Object implements DataGrid\IDataSource
{
	/** @var Doctrine\ORM\QueryBuilder */
	private $_qb;

	public function __construct($query, array $mapping = array())
	{
		if ($query instanceof Doctrine\ORM\QueryBuilder) {
			if (!$query->getDQLPart('from')) {
				throw new \InvalidStateException('Doctrine\ORM\QueryBuilder instance does not contain any "from" part');
			}
			$this->_qb = $query;
		} elseif (is_string($query)) {
			$this->_qb = Nette\Environment::getEntityManager()->createQueryBuilder()->from($query); /** @todo */
		} else {
			throw new \InvalidArgumentException;
		}

		$this->setMapping($mapping);

	}


	/**
	 * Set columns mapping
	 *
	 * @param $mapping array
	 */
	public function setMapping(array $mapping)
	{
		$this->mapping = $mapping;
	}


	/**
	 * Is column with given name valid?
	 *
	 * @return boolean
	 */
	public function isColumnValid($name)
	{
		return \in_array($name, $this->mapping);
	}


	public function select($columns)
	{
		$this->_qb->addSelect($columns);
	}

	protected function validateFilterOperation($operation)
	{
		static $types = array(
			self::EQUAL,
			self::NOT_EQUAL,
			self::GREATER,
			self::GREATER_OR_EQUAL,
			self::SMALLER,
			self::SMALLER_OR_EQUAL,
			self::LIKE,
			self::NOT_LIKE,
			self::IS_NULL,
			self::IS_NOT_NULL,
		);

		if (!in_array($operation, $types)) {
			throw new \InvalidArgumentException('Invalid filter operation type.');
		}
	}

	public function filter($column, $value, $type = self::EQUAL, $chainType = NULL)
	{
		$nextParamId = count($this->_qb->getParameters()) + 1;
		
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
				foreach ($conds as $cond)  {
					$this->_qb->andWhere($cond);
				}
			} elseif ($chainType === self::CHAIN_OR) {
				$this->_qb->andWhere(new Expr\Orx($conds));
			}

			$paramUsed && $this->_qb->setParameter($nextParamId++, $value);
		} else {
			$this->validateFilterOperation($type);

			if ($type === self::IS_NULL || $type === self::IS_NOT_NULL) {
				$this->_qb->andWhere("$column $type");
			} else {
				$this->_qb->andWhere("$column $type ?$nextParamId")->setParameter($nextParamId, $value);
			}
		}
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