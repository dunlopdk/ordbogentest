<?php
require_once ( $_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php" );

class myDB {
	private $uid;
	private $pw;
	private $host;
	private $db;
	private $mysqli;
	private $charset;
	private $procedures;

	function __construct( $charset = false ) {
		$this->uid = DBUSER;
		$this->pw = DBPW;
		$this->host = DBHOST;
		$this->db = DB;
		$this->charset = $charset !== false ? $charset : 'utf8mb4';
		$this->mysqli = new mysqli( $this->host, $this->uid, $this->pw, $this->db );
		$this->setCharSet( );
		$this->procedures = array( );
	}

	public function setCharSet( $charset = false ) {
		if( $charset !== false ) {
			$this->charset = $charset;
		}
		$this->mysqli->set_charset( $this->charset );
	}

	function __destruct( ) {
		$this->uid = $this->pw = $this->host = $this->db = $this->mysqli = null;
	}

	public function getAll( $sql ) {
		$array = array ( );
		$result = $this->query( $sql );
		if( is_object( $result ) ) {
			if( $result->num_rows ) {
				while( ( $row = $result->fetch_assoc( ) ) == true ) {
					$array[] = $row;
				}
			}
		}
		return $array;
	}

	public function getOne( $sql ) {
		$result = $this->query( $sql );
		if( is_object( $result ) && $result->num_rows ) {
			$row = $result->fetch_row( );
			return $row[0];
		}
		return false;
	}

	public function getRow( $sql ) {
		$result = $this->query( $sql );
		if( $result->num_rows ) {
			return $result->fetch_assoc( );
		}
		return array( );
	}

	public function getLastInsertId ( $table ) {
		$sql = "SELECT max(id) FROM " . $table;
		$id = $this->getOne( $sql );
		return $id ? $id : 1;
	}

	public function query( $sql, $returnid = false ) {
		if( $returnid === true ) {
			$this->mysqli->query( $sql );
			return $this->mysqli->insert_id;
		} else {
			return $this->mysqli->query( $sql );
		}
	}

	public function insert ( $table, array $fieldsandvalues, $sql = "", $returnid = true, $debug = false ) {
		if( ( $table == "" || count ( $fieldsandvalues ) == 0 ) && $sql == "" ) { return false; }
		if( $sql !== "" ) {
			return $this->query( $sql, $returnid );
		}

		$sql .= "INSERT INTO " . $table . "(";
		foreach ( $fieldsandvalues as $field => $value ) {
			$sql .= $field . ", ";
		}
		$sql = substr( $sql, 0, strlen( $sql ) - 2 );
		$sql .= ") VALUES (";
		foreach ( $fieldsandvalues as $field => $value ) {
			if( is_int( $value ) ) {
				$sql .= $value . ", ";
			} else {
				$sql .= "'" . $this->mysqli->real_escape_string( $value ) . "', ";
			}
		}
		$sql = substr( $sql, 0, strlen( $sql ) - 2 ) . ");";
		if( $debug === true ) {
			print( "sql = " . $sql );
		}
		return $this->query( $sql, $returnid );
	}

	public function update( $table, array $fieldsandvalues, array $wherevalues, $sql = "" ) {
		if( ( $table == "" || count ( $fieldsandvalues ) == 0 ) && $sql == "" ) {
			return false;
		}
		if( $sql !== "" ) {
			return $this->query( $sql );
		}
		
		$sql .= "UPDATE " . $table . " SET ";
		foreach ( $fieldsandvalues as $field => $value ) {
			$sql .= $this->mysqli->real_escape_string( $field ) . " = ";
			if( is_int( $value ) ) {
				$sql .= $value . ", ";
			} else {
				$sql .= "'" . $this->mysqli->real_escape_string( $value ) . "', ";
			}
		}
		$sql = mb_substr( $sql, 0, mb_strlen( $sql ) - 2 );
		$sql .= " WHERE ";
		$sql .= $this->returnConditionString( $wherevalues );

		return $this->query( $sql );
	}

	/* input array containing one or more arrays consisting of one or more of the following
	 * left: What keyword to use, to the left of the string
	 * right: What keyword to use, to the right of the string
	 * encapsulate: If is set, the following condition(s) will be encapsulated
	 * field: name of field
	 * operator: =, !=, <, > etc.
	 * value: the value of the field
	 * array: one or more array(s) like the input array
	 * ex: ( a = 1 AND ( b=2 OR b=3 ) ) OR a = 2
	 * array(
	 *	array( "encapsulate" => "true", "right" => "OR", "array" =>
	 *		array(
	 *			array( "field" => "a", "operator" => "=", "value" => 1, "right" => "AND" ),
	 *				array( "encapsulate" => true, "right" => "", "array" => array(
	 *					array( "field" => "b", "operator" => "=", "value" => 2, "right" => "OR" ),
	 *					array( "field" => "b", "operator" => "=", "value" => 3, "right" => "" )
	 *				) ),
	 *			)
	 *		),
	 *		array( "field" => "a", "operator" => "=", "value" => 2, "right" => "" )
	 * )
	 * ex: a=2
	 * array( array( "field" => a, "operator" => "=", "value" => 2 ) )
	 */
	public function returnConditionString( array $wherevalues ) {
		if( count( $wherevalues ) == 0 ) { return " 1 "; }
		$sql = "";
		foreach( $wherevalues as $wherevalue ) {
			if( isset( $wherevalue['left'] ) && trim( $wherevalue['left'] ) != "" ) { $sql .= " " . $this->mysqli->real_escape_string( $wherevalue['left'] ) . " "; }

			if( isset( $wherevalue['encapsulate'] ) ) { $sql .= " ( "; }

			if( isset( $wherevalue['field'] ) && isset( $wherevalue['operator'] ) && isset( $wherevalue['value'] ) ) {
				$sql .= $this->mysqli->real_escape_string( $wherevalue['field'] ) . " ";
				if( $wherevalue['operator'] == "LIKE" ) {
					$sql .= $wherevalue['operator'] . " '";
					if( !isset( $wherevalue['isfirst'] ) ) {
						$sql .=  "%";
					}
					$sql .= $this->mysqli->real_escape_string( $wherevalue['value'] ) . "%' ";
				} else {
					$sql .= $wherevalue['operator'] . " ";
					if( isset( $wherevalue['valueisfield'] ) && $wherevalue['valueisfield'] == true ) {
						$sql .= $wherevalue['value'];
					} else {
						if( is_int( $wherevalue['value'] ) ) {
							$sql .= $wherevalue['value'];
						} else {
							$sql .= "'" . $this->mysqli->real_escape_string( $wherevalue['value'] ) . "'";
						}
					}
				}
			}
			if( isset( $wherevalue['array'] ) && is_array( $wherevalue['array'] ) ) {
				$sql .= self::returnConditionString( $wherevalue['array'] );
			}

			if( isset( $wherevalue['encapsulate'] ) ) { $sql .= " ) "; }

			if( isset( $wherevalue['right'] ) && trim( $wherevalue['right'] ) != "" ) { $sql .= " " . $this->mysqli->real_escape_string( $wherevalue['right'] ) . " "; }
			
		}
		return $sql;
	}

	/* input array containing one or more arrays consisting of
	 * jointype: LEFT JOIN, RIGHT JOIN, INNER JOIN
	 * tablename incl. alias: the tablename to be joined
	 * on: same requirements as returnConditionString
	 */
	public function returnJoinString( array $joins ) {
		$returnstring = " ";
		foreach( $joins as $join ) {
			$returnstring .= $join['jointype'] . " ";
			$returnstring .= $join['tablename'] . " ON ";
			$returnstring .= $this->returnConditionString( $join['on'] ) . " ";
		}
		return $returnstring;
	}

	/* input array containing one or more arrays consisting of
	 * field: the name of the field to use for sorting
	 */
	public function returnOrderString( array $orders ) {
		$returnstring = "";
		if( count( $orders ) ) {
			$returnstring .= " ORDER BY ";
			foreach( $orders as $order ) {
				$returnstring .= $this->mysqli->real_escape_string( $order['field'] ) . ", ";
			}
			$returnstring = mb_substr( $returnstring, 0, mb_strlen( $returnstring ) - 2 );
		}
		return $returnstring;
	}

	/* input array containing one or more arrays consisting of
	 * start: the startvalue, if no stop is provided, this is also the max
	 * stop: the stopvalue, stop - start is the number of returned rows.
	 */
	public function returnLimitString( array $limit ) {
		$returnstring = "";
		if( count( $limit ) ) {
			$returnstring .= " LIMIT ";
			$returnstring .= (int)$limit['start'];
			if( isset( $limit['stop'] ) && (int)$limit['stop'] > 0 ) {
				$returnstring .= ", " . (int)$limit['stop'];
			}
		}
		return $returnstring;
	}

	public function select( array $values, $table, array $conditions, array $joins, array $orders, array $limit, $debug = false ) {
		if( count ( $values ) == 0 || $table == "" ) { return array ( ); }

		$sql = "SELECT ";
		foreach( $values as $value ) {
			$sql .= $this->mysqli->real_escape_string( $value ) . ', ';
		}
		$sql = mb_substr( $sql, 0, mb_strlen( $sql ) - 2 );
		$sql .= ' FROM ' . $table;
		$sql .= $this->returnJoinString( $joins );
		$sql .= ' WHERE ';
		$sql .= $this->returnConditionString( $conditions );
		$sql .= $this->returnOrderString( $orders );
		$sql .= $this->returnLimitString( $limit );
		if( $debug === true ) {
			print( "SQL = " . $sql );
		}
		return $this->getAll( $sql );	
	}

	public function procedureExists( $procedurename = "category_hier" ) {
		if( count( $this->procedures ) == 0 ) {
			$this->procedures = $this->getAll( "SHOW PROCEDURE STATUS" );
		}
		foreach( $this->procedures as $procedure ) {
			if( $procedure['Name'] == $procedurename ) { return true; }
		}
		return false;
	}
}
?>
