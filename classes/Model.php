<?php

/**
 * Model Parent Class for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Model Class
 *
 * This is a parent class that all PICKLES data models should be extending.
 * When using the class as designed, objects will function as active record
 * pattern objects.
 */
class Model extends Object
{
	// {{{ Properties

	/**
	 * Database Object
	 *
	 * @access private
	 * @var    object
	 */
	private $db = null;

	/**
	 * SQL Array
	 *
	 * @access private
	 * @var    array
	 */
	private $sql = array();

	/**
	 * Input Parameters Array
	 *
	 * @access private
	 * @var    array
	 */
	private $input_parameters = array();

	/**
	 * Datasource
	 *
	 * @access protected
	 * @var    string
	 */
	protected $datasource;

	/**
	 * Delayed Insert
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $delayed = false;

	/**
	 * Field List
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $fields = '*'; // SELECT

	/**
	 * Table Name
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $table = false; // FROM

	/**
	 * Collection Name
	 *
	 * For compatibility with the naming conventions used by MongoDB, the
	 * collection name can be specified. If the collection name is set, it will
	 * set the table name value to it and proceed as normal.
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $collection = false;

	/**
	 * Joins
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $joins = false; // JOIN

	/**
	 * Conditions
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $conditions = false; // WHERE

	/**
	 * Group
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $group  = false; // GROUP BY

	/**
	 * Having
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $having = false; // HAVING

	/**
	 * Order
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $order = false; // ORDER BY

	/**
	 * Limit
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $limit = false; // LIMIT

	/**
	 * Offset
	 *
	 * @access protected
	 * @var    mixed (string or array)
	 */
	protected $offset = false; // OFFSET

	/**
	 * Query Results
	 *
	 * @access protected
	 * @var    array
	 */
	protected $results = null;

	/**
	 * Index
	 *
	 * @var integer
	 */
	private $index = null;

	/**
	 * Record
	 *
	 * @access private
	 * @var    array
	 */
	public $record = null;

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = null;

	/**
	 * Original Record
	 *
	 * @access private
	 * @var    array
	 */
	private $original = null;

	// }}}

	// {{{ Class Constructor

	/**
	 * Constructor
	 *
	 * Creates a new (empty) object or creates the record set from the passed
	 * arguments. The record and records arrays are populated as well as the
	 * count variable.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function __construct($type_or_parameters = null, $parameters = null)
	{
		// Runs the parent constructor so we have the config
		parent::__construct();

		// Gets an instance of the database
		$this->db = Database::getInstance($this->datasource != '' ? $this->datasource : null);

		// Builds out the query
		if ($type_or_parameters != null)
		{
			// Loads the parameters into the object
			if (is_array($type_or_parameters))
			{
				if (is_array($parameters))
				{
					throw new Exception('You cannot pass in 2 query parameter arrays');
				}

				$this->loadParameters($type_or_parameters);
			}
			elseif (is_array($parameters))
			{
				$this->loadParameters($parameters);
			}

			// Overwrites the table name with the available collection name
			if ($this->collection != false)
			{
				$this->table = $this->collection;
			}

			// If we're using an RDBMS (not Mongo) proceed with using SQL to pull the data 
			if ($this->db->getDriver() != 'mongo')
			{
				// Starts with a basic SELECT ... FROM
				$this->sql = array(
					'SELECT ' . (is_array($this->fields) ? implode(', ', $this->fields) : $this->fields),
					'FROM '   . $this->table,
				);

				// Pulls based on parameters
				if (is_array($type_or_parameters))
				{
					$this->generateQuery();
				}
				// Pulls by ID
				elseif (is_int($type_or_parameters))
				{
					$this->sql[] = 'WHERE id = :id LIMIT 1;';

					$this->input_parameters = array('id' => $parameters);
				}
				else
				{
					switch ($type_or_parameters)
					{
						// Updates query to use COUNT syntax
						case 'count':
							$this->sql[0] = 'SELECT COUNT(*) AS count';
							$this->generateQuery();
							break;

						// Adds the rest of the query
						case 'list':
							$this->generateQuery();
							break;

						// Leaves the query as is
						case 'all':
							break;

						// Throws an error
						default:
							throw new Exception('Unknown query type');
							break;
					}
				}

				$this->records = $this->db->fetch(implode(' ', $this->sql), (count($this->input_parameters) == 0 ? null : $this->input_parameters));
			}
			else
			{
				throw new Exception('Sorry, Mongo support in the PICKLES Model is not quite ready yet');

				/*
				switch ($type_or_parameters)
				{
					case 'count':
						break;

					case 'list':
						break;

					case 'all':
						$this->db->fetch($this->table, $this->input_parameters);
						break;

					// Throws an error
					default:
						throw new Exception('Unknown query type');
						break;
				}
				*/
			}

			$list_type = ($type_or_parameters == 'list');

			// Flattens the data into a list
			if ($list_type == true)
			{
				$list = array();

				foreach ($this->records as $record)
				{
					$list[array_shift($record)] = array_shift($record);
				}

				$this->records = $list;
			}

			// Sets up the current record
			if (isset($this->records[0]))
			{
				$this->record = $this->records[0];
			}
			else
			{
				if ($list_type == true)
				{
					$this->record[key($this->records)] = current($this->records);
				}
				else
				{
					$this->record = $this->records;
				}
			}

			$this->index    = 0;
			$this->original = $this->records;
		}

		return true;
	}

	// }}}

	// {{{ SQL Generation Methods

	/**
	 * Generate Query
	 *
	 * Goes through all of the object variables that correspond with parts of
	 * the query and adds them to the master SQL array.
	 *
	 * @return boolean true
	 */
	private function generateQuery()
	{
		// @todo Adds the JOIN syntax
		if ($this->joins != false)
		{
			// $sql[] = 'JOIN ...';
			throw new Exception('Joins parameter is not yet implemented, sorry');
		}

		// Adds the WHERE conditionals
		if ($this->conditions != false)
		{
			$this->sql[] = 'WHERE ' . (is_array($this->conditions) ? $this->generateConditions($this->conditions) : $this->conditions);
		}

		// Adds the GROUP BY syntax
		if ($this->group != false)
		{
			$this->sql[] = 'GROUP BY ' . (is_array($this->group) ? implode(', ', $this->group) : $this->group);
		}

		// Adds the HAVING conditions
		if ($this->having != false)
		{
			$this->sql[] = 'HAVING ' . (is_array($this->having) ? $this->generateConditions($this->having) : $this->having);
		}

		// Adds the ORDER BY syntax
		if ($this->order != false)
		{
			$this->sql[] = 'ORDER BY ' . (is_array($this->order) ? implode(', ', $this->order) : $this->order);
		}

		// Adds the LIMIT syntax
		if ($this->limit != false)
		{
			$this->sql[] = 'LIMIT ' . (is_array($this->limit) ? implode(', ', $this->limit) : $this->limit);
		}

		// Adds the OFFSET syntax
		if ($this->offset != false)
		{
			$this->sql[] = 'OFFSET ' . $this->offset;
		}

		return true;
	}

	/**
	 * Generate Conditions
	 *
	 * Generates the conditional blocks of SQL from the passed array of
	 * conditions. Supports as much as I could remember to implement. This
	 * method is utilized by both the WHERE and HAVING clauses.
	 *
	 * @param array $conditions array of potentially nested conditions
	 */
	private function generateConditions($conditions)
	{
		$sql = '';

		foreach ($conditions as $key => $value)
		{
			$key = trim($key);

			if (strtoupper($key) == 'NOT')
			{
				$key = 'AND NOT';
			}

			// Checks if conditional to start recursion
			if (preg_match('/(AND|&&|OR|\|\||XOR)( NOT)?/i', $key))
			{
				if (is_array($value))
				{
					// Determines if we need to include ( )
					$nested = (count($value) > 1);

					$sql .= ' ' . $key . ' ' . ($nested ? '(' : '') . $this->generateConditions($value) . ($nested ? ')' : '');
				}
				else
				{
					$sql .= ' ' . $key . ' ' . $value;
				}
			}
			else
			{
				$key = trim($key);

				// Checks for our keywords to control the flow
				$operator      = preg_match('/(<|<=|=|>=|>|!=|<>| LIKE)$/i', $key);
				$between       = preg_match('/ BETWEEN$/i', $key);
				$null_operator = preg_match('/( IS| IS NOT)$/i', $key);
				$null          = ($value === null);

				// Generates an IN statement
				if (is_array($value) && $between == false)
				{
					$sql .= $key . ' IN (' . implode(', ', array_fill(1, count($value), '?')) . ')';
					$this->input_parameters = array_merge($this->input_parameters, $value);
				}
				else
				{
					// Omits the operator as the operator is there
					if ($operator == true || $null_operator == true)
					{
						if ($null)
						{
							// Scrubs the operator if someone doesn't use IS / IS NOT
							if ($operator == true)
							{
								$key = preg_replace('/ ?(!=|<>)$/i', ' IS NOT', $key);
								$key = preg_replace('/ ?(<|<=|=|>=| LIKE)$/i', ' IS', $key);
							}

							$sql .= $key . ' NULL';
						}
						else
						{
							$sql .= $key . ' ?';
							$this->input_parameters[] = $value;
						}
					}
					// Generates a BETWEEN statement
					elseif ($between == true)
					{
						$sql .= $key . ' ? AND ?';

						if (is_array($value))
						{
							// Checks the number of values, BETWEEN expects 2
							if (count($value) != 2)
							{
								throw new Exception('Between expects 2 values');
							}
							else
							{
								$this->input_parameters = array_merge($this->input_parameters, $value);
							}
						}
						else
						{
							throw new Exception('Between usage expects values to be in an array');
						}
					}
					else
					{
						// Checks if we're working with NULL values
						if ($null)
						{
							$sql .= $key . ' IS NULL';
						}
						else
						{
							$sql .= $key . ' = ?';
							$this->input_parameters[] = $value;
						}
					}
				}
			}
		}

		return $sql;
	}

	// }}}

	// {{{ Record Interaction Methods

	/**
	 * Count Records
	 *
	 * Counts the records
	 */
	public function count()
	{
		return count($this->records);
	}

	/**
	 * Next Record
	 *
	 * Increment the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was next element
	 */
	public function next()
	{
		$return = (boolean)($this->record = next($this->records));

		if ($return == true)
		{
			$this->index++;
		}

		return $return;
	}

	/**
	 * Previous Record
	 *
	 * Decrement the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was previous element
	 */
	public function prev()
	{
		$return = (boolean)($this->record = prev($this->records));

		if ($return == true)
		{
			$this->index--;
		}

		return $return;
	}

	/**
	 * Reset Record
	 *
	 * Set the pointer to the first element of the record set.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function reset()
	{
		$return = (boolean)($this->record = reset($this->records));

		if ($return == true)
		{
			$this->index = 0;
		}

		return $return;
	}

	/**
	 * First Record
	 *
	 * Alias of reset(). "first" is more intuitive to me, but reset stays in
	 * line with the built in PHP functions.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function first()
	{
		return $this->reset();
	}

	/**
	 * End Record
	 *
	 * Set the pointer to the last element of the record set.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function end()
	{
		$return = (boolean)($this->record = end($this->records));

		if ($return == true)
		{
			$this->index = $this->count() - 1;
		}

		return $return;
	}

	/**
	 * Last Record
	 *
	 * Alias of end(). "last" is more intuitive to me, but end stays in line
	 * with the built in PHP functions.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function last()
	{
		return $this->end();
	}

	// }}}

	// {{{ Record Manipulation Methods

	/**
	 * Commit
	 *
	 * Inserts or updates a record in the database.
	 *
	 * @return boolean results of the query
	 */
	public function commit()
	{
		// Checks if the record is actually populated
		if (count($this->record) > 0)
		{
			// Determines if it's an UPDATE or INSERT
			$update = (isset($this->record['id']) && trim($this->record['id']) != '');

			// Establishes the query, optionally uses DELAYED INSERTS
			$sql = ($update === true ? 'UPDATE' : 'INSERT' . ($this->delayed == true ? ' DELAYED' : '') . ' INTO') . ' ' . $this->table . ' SET ';
			$input_parameters = null;

			// Limits the columns being updated
			$record = ($update === true ? array_diff($this->record, $this->original[$this->index]) : $this->record);

			// Loops through all the columns and assembles the query
			foreach ($record as $column => $value)
			{
				if ($column != 'id')
				{
					if ($input_parameters != null)
					{
						$sql .= ', ';
					}

					$sql .= $column . ' = :' . $column;
					$input_parameters[':' . $column] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
				}
			}

			// If it's an UPDATE tack on the ID
			if ($update === true)
			{
				$sql .= ' WHERE id = :id LIMIT 1;';
				$input_parameters[':id'] = $this->record['id'];
			}

			// Executes the query
			return $this->db->execute($sql, $input_parameters);
		}

		return false;
	}

	/**
	 * Delete Record
	 *
	 * Deletes the current record from the database
	 *
	 * @return boolean status of the query
	 */
	public function delete()
	{
		$sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id LIMIT 1;';
		$input_parameters[':id'] = $this->record['id'];

		return $this->db->execute($sql, $input_parameters);
	}

	// }}}

	// {{{ Utility Methods

	/**
	 * Load Parameters
	 *
	 * Loads the passed parameters back into the object.
	 *
	 * @access private
	 * @param  array $parameters key / value list
	 * @param  boolean whether or not the parameters were loaded
	 */
	private function loadParameters($parameters)
	{
		if (is_array($parameters))
		{
			// Adds the parameters to the object
			foreach ($parameters as $key => $value)
			{
				if (isset($this->$key))
				{
					$this->$key = $value;
				}
			}

			return true;
		}

		return false;
	}
	/**
	 * Unescape String
	 *
	 * Assuming magic quotes is turned on, strips slashes from the string
	 *
	 * @access protected
	 * @param  string $value string to be unescaped
	 * @return string unescaped string
	 */
	protected function unescape($value)
	{
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}

		return $value;
	}

	// }}}
}

?>
