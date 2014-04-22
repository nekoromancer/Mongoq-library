<?php
/*
 * MongoDB Quick Query Library - Mongoq ver 0.42
 * www.nekoromancer.kr
 *
 * Author : Nekoromancer
 * Email : nekonitrate@gmail.com
 *
 * Released under the MIT license
 *
 * Date: 2014-04-23
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Mongoq{
	static private $is_init = false;

	private $CI;
	private $config_file = "mongo.php";
	
	/*
	 * MongoDB config values from $config_file
	 */

	private $connection_string;
	private $username;
	private $password;
	private $hostname;
	private $port;
	private $dbname;
	private $persist;
	private $replica;

	/*
	 * MongoDB objects
	 * 
	 * $connection : object created by new Mongo();
	 * $db : database object
	 */

	private $connection;
	private $db;

	private $collection = null;
	private $projection = array();
	private $wheres = array();
	private $limit = null;
	private $skip = 0;
	private $sort = array();
	private $updates = array();
	private $woptions = array();

	private $aggregate_options = array();

	public function __construct()
	{
		if ( !class_exists('Mongo') )
        {
            show_error("The MongoDB PECL extension has not been installed or enabled", 500);
        }

		$this->CI = get_instance();
		$this->setConfig();
		$this->connect();
	}

	/*
	 * Select collection.
	 */

	public function collection( $collection = null )
	{
		$this->collection = $collection;

		return ( $this );
	}

	/*
	 * It's same functino to collection();
	 */

	public function from( $collection = null )
	{
		$this->collection = $collection;

		return ( $this );
	}

	/*
	 * db.createCollection();
	 */

	public function createCollection( $options = null )
	{
		if( isset( $options['name'] ) )
		{
			$name = $options['name'];
			unset( $options['name'] );
		}
		else
		{
			show_error( "createCollection() : collection name is must be required", 500 );
		}

		try
		{
			$result = $this->db->createCollection( $name, $options );
		}
		catch( MongoException $e )
		{
			show_error( "createCollection(), unknown error : ".$e->getMessage() );
		}
	}

	/*
	 * db.collection.remove()
	 */

	public function remove( $justOne = false )
	{
		$this->isCollection( 'remove()' );

		$collection = &$this->collection;

		if( $justOne )
		{
			$this->woptions['justOne'] = true;
		}

		$result = $this->db->{$collection}
				   		         ->remove( $this->wheres, $this->woptions );

		$this->_clear();

		return $result;
	}

	/*
	 * Drop the current or specific collection.
	 */

	public function dropCollection( $collection = null )
	{
		if( !$collection && !$this->collection )
		{
			show_error( "Select collection name to drop." );
		}
		else if( !$collection )
		{
			$collection = &$this->collection;
			$result = $this->db->{$collection}->drop();
		}
		else
		{
			$result = $this->db->{$collection}->drop();
		}

		if( $result[ "ok" ] == 1 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Drop the current database.
	 */

	public function dropCurrentDatabase()
	{
		$result = $this->db->drop();

		if( $result[ "ok" ] == 1 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Switch database to use
	 */

	public function switchDB( $databasename = null )
	{
		if( !$databasename )
		{
			show_error( "Database name is must be reqired to switching database.", 500 );
		}

		$this->dbname = $databasename;
		$this->connect();
	}

	/*
	 * db.collection.ensureIndex()
	 * Creates an index on the collection and the specified fields.
	 */

	public function ensureIndex( $fields = null, $is_unique = false )
	{
		$this->isCollection( 'ensureIndex' );

		$collection = &$this->collection;
		$indexing = array();

		if( !$fields )
		{
			show_error( "ensureIndex() : Field name is must be required to create indexes.", 500 );
		}
		else
		{
			foreach( $fields as $field => $order )
			{
				$order = strtolower( $order );

				if( $order == 'asc' )
				{
					$order = 1;
				}
				else if( $order == 'desc' )
				{
					$order = -1;
				}
				else
				{
					show_error( 'ensureIndex() : Wrong parameter.', 500 );
				}

				array_push( $indexing, array( $field => $order ) );
			}

			$this->db->{$collection}
					 ->ensureIndex( $indexing, array( 'unique' => $is_unique ) );
		}
	}

	/*
	 * db.collection.find()
	 * $collection : target collection name
	 * $return_as_array : default false. Setting true, this function return array not MongoDB cursor object. 
	 *
	 * return MongoDB Cursor Object $documents. 
	 */

	public function find( $return_as_array = false )
	{
		$this->isCollection( 'find()' );

		$collection = &$this->collection;

		if( $this->limit )
		{
			$documents = $this->db->{$collection}
														->find( $this->wheres, $this->projection )
														->skip( $this->skip )
														->limit( $this->limit )
														->sort( $this->sort );
		}
		else
		{
			$documents = $this->db->{$collection}
														->find( $this->wheres, $this->projection )
														->skip( $this->skip )
														->sort( $this->sort );
		}

		if( $return_as_array )
		{
			$documents = $this->toArray( $documents );
		}

		$this->_clear();
		return $documents;
	}

	/*
	 * It's same function to find();
	 */

	public function get( $return_as_array = false )
	{
		return $this->find( $return_as_array );
	}

	/*
	 * db.collection.distinct();
	 *
	 * Finds the distinct values for a specified field across a single collection and returns the results in an array.
	 */

	public function distinct( $field = null )
	{
		$this->isCollection( 'distinct()' );

		$collection = &$this->collection;

		if( !$field )
		{
			show_error( "distinct() : Field name, the first parameter, is must be required", 500 );
		}
		else 
		{
			$result = $this->db->{$collection}
				 	  						 ->distinct( $field, $this->wheres )
				  	 						 ->sort( $this->sort );

			$this->_clear();

			return $result;
		}
	}

	/*
	 * Set the projection to querying
	 * 
	 * if $except(default false) is true, returns all fields except $projection
	 */

	public function select( $projection = array(), $except = false )
	{
		if( $except )
		{
			$value = 0;
		}
		else
		{
			$value = 1;
		}

		foreach( $projection as $item )
		{
			$this->projection[ $item ] = $value;
		}

		unset( $item );

		return ( $this );
	}

	/*
	 * Use the limit() method on a cursor to specify the maximum number of documents the cursor will return.
	 */

	public function limit()
	{
		$option = func_get_args();

		if( count( $option ) === 1 )
		{
			$limit = array_shift( $option );
		}
		else if( count( $option ) === 2 )
		{
			$offset = array_shift( $option );
			$limit = array_shift( $option );
		}
		else 
		{
			show_error( "limit() : wrong parameter.", 500 );
		}

		if( $limit && gettype( $limit ) === 'integer' )
		{
			$this->limit = $limit;

			if( isset( $offset ) )
			{
				$this->skip( $offset );
			}
		}
		else
		{
			show_error( "limit() : value is null or incorrect type.", 500 );
		}

		return ( $this );
	}

	/*
	 * Call the cursor.skip() method on a cursor to control where MongoDB begins returning results.
	 */

	public function skip( $skip = null )
	{
		if( $skip && gettype( $skip ) === 'integer' )
		{
			$this->skip = $skip;
		}
		else
		{
			show_error( "skip() : value is null or incorrect type.", 500 );
		}

		return ( $this );
	}

	/*
	 * It's same function to skip();
	 */

	public function offset( $offset = null )
	{
		$this->skip( $offset );

		return ( $this );
	}

	/* 
	 * Controls the order that the query returns matching documents.
	 *
	 * 		systax : $this->$mongoq->...->sort( array( 'field' => value ))
	 *		value : 1, -1, asc or desc
	 */

	public function sort( $sort = array(), $by = null )
	{
		if( empty( $sort ) )
		{
			return ( $this );
		}

		if( !is_array( $sort ) )
		{
			$field = $sort[0];

      if( $by == 1 || $by == -1 )
      {
          $by = $by;
      }
      else if( gettype( $by ) == 'string' && strtolower( $by ) == 'asc' )
      {
          $by = 1;
      }
      else if( gettype( $by ) == 'string' && strtolower( $by ) == 'desc' )
      {
          $by = -1;
      }
      else
      {
          show_error( "sort() : incorrect parameter", 500 );
      }
		
			$this->sort[ $field ] = $by;

			return ( $this );
		}
		else
		{
			foreach( $sort as $item => $by )
			{
				if( $by == 1 || $by == -1 )
				{
					$by = $by;
				}
				else if( gettype( $by ) == 'string' && strtolower( $by ) == 'asc'  )
				{
					$by = 1;
				}
				else if( gettype( $by ) == 'string' && strtolower( $by ) == 'desc'  )
				{
					$by = -1;
				}
				else
				{
					show_error( "sort() : incorrect parameter", 500 );
				}

				$this->sort[ $item ] = $by;
			}
			unset( $item );
			unset( $by );

			return ( $this );
		}
	}

	/*
	 * Set options for write and remove opertaion.
	 *
	 * j : Forces the insert to be synced to the journal before returning success.
	 * w : MongoDB provides several different ways of selecting how durable a write to the database should be. 
	 *	   These ways are called Write Concerns and span everything from completely ignoring all errors, to specifically targetting which servers are required to confirm the write before returning the operation.
	 * wtimeout : How long to wait for WriteConcern acknowledgement. The default value for MongoClient is 10000 milliseconds.
	 * timeout :  If acknowledged writes are used, this sets how long (in milliseconds) for the client to wait for a database response.
	 */

	public function setWoptions( $opt = array() )
	{
		$this->woptions = $opt;

		return ( $this );
	}

	/*
	 * db.collection.insert()
	 */

	public function insert( $documents = array() )
	{
		$this->isCollection( 'insert()' );

		$collection = &$this->collection;

		if( !$documents || empty( $documents ) || !is_array( $documents ) )
		{
			show_error( "Insert Error.  Please Check Your Syntax of Query", 500 );
		}

		try 
		{
			$this->db->{$collection}->insert( $documents, $this->woptions );
		} 
		catch( MongoException $e ) 
		{
			show_error( "DB insert error : ".$e->getMessage() );
		}

		$this->_clear();
	}

	/*
	 * db.collection.save()
	 */

	public function save( $documents = array() )
	{
		$collection = &$this->collection;

		if( !$collection )
		{
			show_error( "Collections name is must be required", 500 );
		}
		else if( !$documents || empty( $documents ) || !is_array( $documents ) )
		{
			show_error( "Insert Error.  Please Check Your Syntax of Query", 500 );
		}
		
		$option = array();

		try 
		{
			$this->db->{$collection}->save( $documents, $this->woptions );
		} 
		catch( MongoException $e ) 
		{
			show_error( "DB insert error : ".$e->getMessage() );
		}

		$this->_clear();
	}

	/*
	 * db.collection.update()
	 */

	private function setUpdateOptions( $opt, $args )
	{
		if( !isset( $this->updates[$opt] ) )
		{
			$this->update[$opt] = array();
		}
		
		if( $opt == '$unset')
		{
			foreach( $values as $each_item )
			{
				$this->updates[ $opt ][ $each_item ] = '';
			}
		}
		else
		{
			if( !is_array( $args[0] ) )
			{
				$key = array_shift( $args );
				$value = array_shift( $value );
				$this->updates[ $opt ][ $key ] = $value;	
			}
			else
			{
				$values = &$args[0];
				foreach( $values as $key => $value )
				{
					$this->updates[ $opt ][ $key ] = $value;
				}
			}
		}
	}

	public function set()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$set', $args );
		}
		return ( $this );
	}

	public function inc()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$inc', $args );
		}
		return ( $this );
	}

	public function unsetField()
	{
		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$unset', $args );
		}
		return ( $this );
	}

	public function setOnInsert()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$setOnInsert', $args );
		}
		return ( $this );
	}

	public function max()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$max', $args );
		}
		return ( $this );
	}

	public function min()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$min', $args );
		}
		return ( $this );
	}

	public function mul()
	{
		$args = func_get_args();

		if( empty( $args ) )
		{
			return( $this );
		}
		else
		{
			$this->setUpdateOptions( '$mul', $args );
		}
		return ( $this );
	}

	public function rename()
	{
		$args = func_get_args();
		
		if( is_array( $args[0] ) )
		{
			$renames = array_shift( $args );
			$this->updates['$rename'] = array();
			array_push( $this->updates['$rename'], $renames );
		}
		else
		{
			$oldName = array_shift( $args );
			$newName = array_shift( $args );

			$this->updates['$rename'] = array( $oldName => $newName );
		}

		return( $this );
	}

	public function now()
	{
		$args = func_get_args();

		if( is_array( $args[0] ) )
		{
			$dates = array_shift( $args );
			$this->updates['$currentDate'] = array();
			array_push( $this->updates['$currentDate'], $dates );
		}
		else
		{
			$field = array_shift( $args );
			$value = array_shift( $args );
			if( !$value )
			{
				$value = true;
			}
			else if( strtolower( $value ) === 'ts' || strtolower( $value ) === 'timestamp' )
			{
				$value = array( '$type' => 'timestamp' );
			}

			$this->updates['$currentDate'] = array();
			array_push( $this->updates['$currentDate'], $value );
		}

		return( $this );
	}

	public function update( $upsert = false, $multi = false )
	{
		$collection = &$this->collection;

		if( !$collection )
		{
			show_error( "Collections name is must be required", 500 );
		}

		$query = $this->wheres;

		if( empty( $this->updates ) )
		{
			show_error( 'update() : update option is empty', 500 );
		}
		else
		{
			if( isset( $this->updates['$setOnInsert'] ) && 
				  !empty( $this->updates['$setOnInsert'] ) )
			{
				$upsert = true;
			}

			$this->options['upsert'] = $upsert;
			$this->options['multiple'] = $multi;

			try 
			{
				$this->db->{$collection}->update( $query, 
																				  $this->updates, 
																				  $this->woptions );

				$this->_clear();
			} 
			catch( MongoException $e ) 
			{
				show_error( 'DB update error : '.$e->getMessage() );
			}
		}
	}

	/*
	 * db.collection.count();
	 */

	public function count()
	{
		$collection = &$this->collection;

		if( !$collection )
		{
			show_error( "Collections name is must be required", 500 );
		}
		else
		{
			try 
			{
				$result = $this->db->{$collection}->count( $this->wheres );
				$this->_clear();

				return $result;
			} 
			catch( MongoException $e ) 
			{
				show_error( 'count() : unknown error : '.$e->getMessage() );
			}
		}
	}

	/*
	 * db.collection.group();
	 */

	public function group( $options = array() )
	{
		$cond = &$this->wheres;
		$collection = &$this->collection;

		if( isset($options['keyf']) )
		{
			$keyf = new MongoCode( $options['keyf'] );
		}
		else
		{
			$keyf = $this->projection;
		}

		if( isset($options['reduce']) )
		{
			$reduce = new MongoCode( $options['reduce'] );
		}
		else
		{
			$reduce = new MongoCode('function( curr, result ) {}');
		}

		if( isset($options['finalize']) )
		{
			$finalize = new MongoCode( $options['finalize'] );
		}
		else
		{
			$finalize = new MongoCode( '' );
		}

		if( isset($options['initial']) )
		{
			$initial = &$options['initial'];
		}
		else
		{
			$initial = array();
		}
		
		try 
		{
			$result = $this->db->{$collection}->group( $keyf,
																							   $initial,
																							   $reduce,
																							   array( 'condition' => $cond,
																							   		    'finalize' => $finalize ) );
			$this->_clear();

			if( $result[ 'ok' ] == 1 )
			{
				return $result[ 'retval' ];
			}
			else
			{
				return false;
			}

		} 
		catch( MongoException $e ) 
		{
			show_error( 'group() : unknown error : '.$e->getMessage() );
		}
	}

	/*
	 * db.collection.aggreate();
	 * 
	 * add pipeline options.
	 */

	public function aggregateAddopt( $pipeline = null, $option = null )
	{
		if( !$pipeline )
		{
			show_error( "aggregateAddopt() : missing parameter", 500 );
		}

		if( substr( $pipeline, 0, 1 ) != '$' )
		{
			$pipeline = '$'.$pipeline;
		}

		switch( $pipeline )
		{
			case '$match' :
				array_push( $this->aggregate_options, array( '$match' => $this->wheres ) );
				break;

			case '$project' :
				array_push( $this->aggregate_options, array( '$project' => $this->projection ) );
				break;

			case '$group' :
				if( !$option ) 
				{
					show_error( "aggregateAddopt() : $group pipeline must required second parameter", 500);
				}

				array_push( $this->aggregate_options, array( '$group' => $option ) );
				break;

			case '$sort' :
				array_push( $this->aggregate_options, array( '$sort' => $this->sort ) );
				break;

			case '$limit' :
				if ( !$option )
				{
					$limit = $this->limit;
				}
				else
				{
					$limit = &$option;
				}

				array_push( $this->aggregate_options, array( '$limit' => $limit ) );
				break;

			case '$skip' :
				if ( !$option )
				{
					$skip = $this->skip;
				}
				else
				{
					$skip = &$option;
				}

				array_push( $this->aggregate_options, array( '$match' => $skip ) );
				break;

			case '$unwind' :
				array_push( $this->aggreate_options, array( '$unwind' => $option) );

			default :
				break;			
		}
	}

	/*
	 * db.collection.aggreate();
	 * 
	 * Get result of aggregation operation.
	 */

	public function getAggregation()
	{
		$this->isCollection( 'getAggregation()' );

		$collection = &$this->collection;

		$result = $this->db->{$collection}->aggregate( $this->aggregate_options );
		$this->_clear();

		if( !$result["ok"] || $result["ok"] == 0 )
		{
			return false;
		}
		else
		{
			return $result["result"];
		}
	}

	/*
	 * where() performs a logical AND operation on an array of two or more expressions.
	 * if it has an exepression,
	 * this function have three parameter :
	 *		$field : field name
	 *		$operator : comparison query operators
	 * 			'=' is equal
	 *      '>' is greater than( $gt )
	 *			'>=' is greater than or equal to( $gte )
	 *			'<' is less than( $lt )
	 *			'<=' is less than or equal to( $lte )
	 *			'in' is exist in( $in )
	 *			'not in' is not exist in( $nin )
	 *		  '<>' or '!=' is not eqaul( $ne )
	 *		$value : value
	 *
	 * if you want input more expression, use array like this
	 * 		ex) $where = array( array( 'foo_1', '>', 'bar_1' ),
	 *						           	array( 'foo_2', '<', 'bar_2' ),
	 *									                	...                  );
	 *			$result = $this->mongoq->where( $where )-> ... ; 
	 */

	public function where( $where = array(), $operator = null, $value = null )
	{
		if( is_array( $where ) && !empty( $where ) )
		{
			$this->inputExpression( '$and', $where );
		}
		else
		{
			$expression = $this->createExpression( $where, $operator, $value );
			
			if( !isset( $this->wheres[ '$and' ] ) )
			{
				$this->wheres[ '$and' ] = array();
			}

			array_push( $this->wheres[ '$and' ], $expression );
		}

		return ( $this );
	}
	
	/*
	 * orWhere() performs a logical OR operation on an array of tow or more expressions.
	 * 		systax : $or_where = array( array ( $field , $operator, $value ), ... ); 
	 *				 $this->mongoq->orWhere( $or_where )-> ... ->find( $collection );
	 *
	 *		$field : field name
	 *		$operator : comparison query operators
	 *		$value : value
	 *
	 * 		ex) $or_where = array( array( 'foo_1', '>', 'bar_1' ),
	 *						        array( 'foo_2', '<', 'bar_2' ),
	 *									              	...                 );
	 *			$result = $this->mongoq->orWhere( $or_where )-> ... find( 'collection_name' ); 
	 */

	public function orWhere( $where = array() )
	{
		if( is_array( $where ) )
		{
			$this->inputExpression( '$or', $where );
		}

		return ( $this );
	}

	/*
	 * notWhere() performs a logical NOT operation on an array of tow or more expressions.
	 * 		systax : $not_where = array( array ( $field , $operator, $value ), ... ); 
	 *				 $this->mongoq->notWhere( $not_where )-> ... ->find( $collection );
	 *
	 *		$field : field name
	 *		$operator : comparison query operators
	 *		$value : value
	 *
	 * 		ex) $not_where = array( array( 'foo_1', '>', 'bar_1' ),
	 *						                array( 'foo_2', '<', 'bar_2' ),
	 *									                    	...                );
	 *			  $result = $this->mongoq->notWhere( $nor_where )-> ... find( 'collection_name' ); 
	 */
	
	public function notWhere( $where = array(), $operator = null, $value = null )
	{
		if( is_array( $where ) && !empty( $where ) )
		{
			$this->inputExpression( '$not', $where );
		}
		else
		{
			$expression = $this->createExpression( $where, $operator, $value );

			if( !isset( $this->wheres[ '$not' ] ) )
			{
				$this->wheres[ '$not' ] = array();
			}

			array_push( $this->wheres[ '$not' ], $expression );
		}

		return ( $this );
	}
	
	/*
	 * norWhere() performs a logical NOR operation on an array of tow or more expressions.
	 * 		systax : $nor_where = array( array ( $field , $operator, $value ), ... ); 
	 *				     $this->mongoq->norWhere( $nor_where )-> ... ->find( $collection );
	 *
	 *		$field : field name
	 *		$operator : comparison query operators
	 *		$value : value
	 *
	 * 		ex) $nor_where = array( array( 'foo_1', '>', 'bar_1' ),
	 *						                array( 'foo_2', '<', 'bar_2' ),
	 *										                   ...                  );
	 *			  $result = $this->mongoq->norWhere( $nor_where )-> ... find( 'collection_name' ); 
	 */
	
	public function norWhere( $where = array() )
	{
		if( is_array( $where ) )
		{
			$this->inputExpression( '$nor', $where );
		}
		
		return ( $this );
	}

	public function id( $mongoId = null )
	{
		$_id = '';

		try 
		{
			$_id = new MongoId( $mongoId );
		} 
		catch( MongoException $e ) 
		{
			show_error( 'Error. Invalid Mongo ID / '.$e->getMessage(), 500 );
		}

		return $_id;
	}

	/*
	 * Initializing MongoDB connection
	 */

	private function connect()
	{
		$option = array();

		if( phpversion('mongo') >= 1.3 )
		{
			$option["replicaSet"] = $this->replica;

			try 
			{
			$this->connection = new MongoClient( $this->connection_string, $option );
			$this->db = $this->connection->{$this->dbname};

			return ( $this );

			} 
			catch( MongoConnectionException $e ) 
			{
				die( $e->getMessage() );
			}
		}
		else
		{
			$option["replicaSet"] = $this->replica_set;
			$option["persist"] = $this->persist;

			try {
				$this->connection = new Mongo( $this->connection_string, $option );
				$this->db = $this->connection->{$this->dbname};

				return ( $this );

			} 
			catch( MongoConnectionException $e ) 
			{
				die( $e->getMessage() );
			}
		}
	}
	
	/*
	 * Load config setting from config file
	 */

	private function setConfig()
	{
		$this->CI->config->load( $this->config_file );

		$this->username = $this->CI->config->item("username");
		$this->password = $this->CI->config->item("password");
		$this->hostname = $this->CI->config->item("hostname");
		$this->port = $this->CI->config->item("port");
		$this->replica = $this->CI->config->item("replica_set");

		$use_persist = $this->CI->config->item("persist");
		if( phpversion('mongo') < 1.3 && $use_persist ) 
		{
			$this->persist = $this->CI->config->item("persist_key");
		}
		
		if( !self::$is_init )
		{
			$this->dbname = $this->CI->config->item("dbname");
			self::$is_init = true;
		}

		$this->connectionString();
	}

	/*
	 * Creat connection string from config setting
	 */

	private function connectionString()
	{
		$connection_string = "mongodb://".
												 $this->username.":".
												 $this->password."@".
												 $this->hostname.":".
												 $this->port."/".
  											 $this->dbname;

		$this->connection_string = $connection_string;
	}
	
	/*
	 * Convert to arrays from MongoDB cursor object
	 *
	 * cursor object $object
	 */
	
	public function toArray( $object = null )
	{
		if( !$object )
		{
			show_error( "Missing parameter : function result_to_array()" );
		}
		else if( !is_object( $object ) )
		{
			show_error( "Type error : Parameter type is must object." );
		}

		$result = array();

		while( $object->hasNext() )
		{
			array_push( $result, $object->getNext() );
		}

		return $result;
	}

	/*
	 * Sortng Array
	 */

	public function sortResultArray()
	{
		$args = func_get_args();
		$data = array_shift( $args );

		foreach( $args as $n => $field )
		{
			if( is_string( $field ) )
			{
				$tmp = array();

				foreach( $data as $key => $row )
				{
					$tmp[ $key ] = $row[ $field ];
				}

				$args[ $n ] = $tmp;
			}
		}

		$args[] = &$data;
		call_user_func_array('array_multisort', $args );

		return array_pop( $args );
	}
	
	/*
	 * function : Clear data in '$this->wheres'
	 */


	private function _clear()
	{
		$this->wheres = array();
		$this->collection = null;
		$this->projection = array();
		$this->limit = null;
		$this->skip = 0;
		$this->sort = array();
		$this->updates = array();
		$this->woptinos = array();
		$this->aggregate_options = array();
	}
	
	/*
	 * Input expressions and logical operation type into '$this->wheres'
	 */
	
	private function inputExpression( $logical_operator = '$and', $where = array() )
	{
		if( !isset( $this->wheres[ $logical_operator ] ) )
		{
			$this->wheres[ $logical_operator ] = array();
		}

		foreach( $where as $items )
		{
			$field = $items[0];
			$operator = $items[1];
			$value = $items[2];

			$expression = $this->createExpression( $field, $operator, $value );

			array_push( $this->wheres[ $logical_operator ], $expression );
		}

		unset( $field );
		unset( $values );
	}

	/*
	 * Create expression string
	 */

	private function createExpression( $field = null, $operator = null, $value = null )
	{
		if( $field === null || !$operator === null || $value === null )
		{
			echo $field.$operator.$value;
			show_error( "Check your systax of critera", 500 );
		}
		else
		{
			$operator = strtolower( $operator );
			$operator = $this->_setOperator( $operator );

			if( !$operator )
			{
				echo $field.$operator.$value;
				show_error( "createExpression() : Wrong operator" );
			}
			else if( $operator === 'eq' )
			{
				if( is_array( $value ) )
				{
					$value = $this->createExpression( $value[0], $value[1], $value[2] );
				}

				$expression = array( $field => $value );
				return $expression;
			}
			else if( $operator === '$regex' )
			{
				$expression = array( $field => array( $operator => $value, '$options' => 'i' ) ) ;

				return $expression;
			}
			else
			{
				$expression = array( $field => array( $operator => $value) );
				return $expression;
			}
		}
	}

	/*
	 * Create logical operation string
	 */

	private function _setOperator( $operator = null )
	{
		switch( $operator ) {
			case "=" :
				$result = 'eq';
				break;
			case ">" :
				$result = '$gt';
				break;
			case ">=":
				$result = '$gte';
				break;
			case "<" :
				$result = '$lt';
				break;
			case "<=" :
				$result = '$lte';
				break;
			case "in" :
				$result = '$in';
				break;
			case "not in" :
				$result = '$nin';
				break;
			case "<>" :
				$result = '$ne';
				break;
			case "!=" :
				$result = '$ne';
				break;
			case "like":
				$result = '$regex';
				break;
			default :
				$result = false;
				break;
		}

		return $result;
	}

	/*
	 * Check the collection is defined 
	 */

	private function isCollection( $from = "" )
	{
		if( !$this->collection )
		{
			show_error( $from." : Collection name is must be required", 500 );
		}
	}
}
