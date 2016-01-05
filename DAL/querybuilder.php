<?php namespace DAL;

use Application\Registry;
use Application\Exceptions\QueryBuilderException;

class QueryBuilder {
	protected $table = NULL;
	protected $selection = '';
	protected $selectionArgs = array();
	protected $joins = '';
	protected $limit = '';
	protected $orderColumns = array();
	
	public function where($selection, array $selectionArgs) {
		if (empty($selection)) {
			if ($selectionArgs != null && count($selectionArgs) > 0) {
				throw new QueryBuilderException('Valid selection required when including arguments');
			}
			
			return $this;
		}
		
		if (!empty($this->selection)) {
			$this->selection .= ' AND ';
		}
		$this->selection .= '( ' . $selection . ' )';
		$this->selectionArgs = array_merge($this->selectionArgs, $selectionArgs);
		
		return $this;
	}
	
	public function table($table) {
		$this->table = $table;
		return $this;
	}
	
	public function leftJoin($table, $on) {
		$this->joins .= ' LEFT JOIN ' . $table . ' ON (' . $on . ')';
		return $this;
	}
	
	public function innerJoin($table, $on) {
		$this->joins .= ' INNER JOIN ' . $table . ' ON (' . $on . ')';
		return $this;
	}
	
	public function limit($limit, $offset = 0) {
		$this->limit = ' LIMIT ';
		if ($offset != 0) $this->limit .= (int) $offset . ', ';
		$this->limit .= (int) $limit;
		return $this;
	}
	
	public function orderBy(array $orderColumns) {
		$this->orderColumns = array_merge($this->orderColumns, $orderColumns);
		return $this;
	}
	
	public function query(array $columns, $distinct = false) {
		$query = 'SELECT ';
		if ($distinct) {
			$query .= 'DISTINCT ';
		}
		
		$query .= implode(', ', $columns) . ' FROM ' . $this->table;
		
		$query .= $this->joins;
		
		if (!empty($this->selection)) {
			$query .= ' WHERE ' . $this->selection;
		}
		
		if (count($this->orderColumns) > 0) {
			$query .= ' ORDER BY ' . implode(', ', $this->orderColumns); 
		}
		
		if (!empty($this->limit)) {
			$query .= $this->limit;
		}
		
		$sth = Registry::getInstance()->db->prepare($query);
		$this->bindValues($sth, $this->selectionArgs);
		$sth->execute();
		return $sth;
	}
	
	public function delete() {
		$query = 'DELETE FROM ' . $this->table;
		$query .= $this->joins;
		
		if (!empty($this->selection)) {
			$query .= ' WHERE ' . $this->selection;
		}
		$sth = Registry::getInstance()->db->prepare($query);
		$this->bindValues($sth, $this->selectionArgs);
		if ($sth->execute()) {
			$sth->rowCount();
		} else {
			return false;
		}
	}
	
	public function generateUpdate(array $columns) {
		$query = 'UPDATE ' .$this->table . ' SET ';
		$first = true;
		foreach ($columns as $column) {
			if (!$first)
				$query .= ', ';
			else
				$first = false;
				
			$query .= $column . ' = :' . $column;
		}
		
		if (!empty($this->selection)) {
			$query .= ' WHERE ' . $this->selection;
		}
		
		return Registry::getInstance()->db->prepare($query);
	}
	
	public function update(array $contentValues) {
		$columns = array_keys($contentValues);
		$sth = $this->generateUpdate($columns);
		$this->bindValues($sth, array_merge($contentValues, $this->selectionArgs));
		if ($sth->execute()) {
			$sth->rowCount();
		} else {
			return false;
		}
	}
	
	public function generateInsert(array $columns) {
		$query = 'INSERT INTO ' . $this->table;
		$query .= ' (' . implode(', ', $columns) . ')';
		$query .= ' VALUES (:' . implode(', :', $columns) . ')';
		return Registry::getInstance()->db->prepare($query);
	} 
	
	public function insert(array $contentValues) {
		$columns = array_keys($contentValues);
		$sth = $this->generateInsert($columns);
		$this->bindValues($sth, $contentValues);
		
		$transaction = false;
		if (!Registry::getInstance()->db->inTransaction()) {
			Registry::getInstance()->db->beginTransaction();
			$transaction = true;
		}
		
		try {
			if ($sth->execute()) {
				$result = Registry::getInstance()->db->lastInsertId();				
				if ($transaction) {
					Registry::getInstance()->db->commit();
				}
				
				return $result;
			} else {
				if ($transaction) {
					Registry::getInstance()->db->rollBack();
				}
				return false;
			}
		} catch (\PDOException $e) {
			if ($transaction) {
				Registry::getInstance()->db->rollBack();
			}
			throw $e;
		}
	}
	
	protected function bindValues(\PDOStatement $sth, array $values) {
		foreach ($values as $parameter => $value) {
			//Parameters are 1-based
			if (is_int($parameter)) {
				$parameter++;
			} else {
				$parameter = ':' . $parameter;
			}
			
			if (is_array($value)) {
				$sth->bindValue($parameter, $value[0], $value[1]);
			} else {
				$sth->bindValue($parameter, $value);
			}
		}
	}
	
	public function isNull($first, $second) {
		return 'COALESCE(' . $first . ', ' . $second . ')';
	}
}

?>