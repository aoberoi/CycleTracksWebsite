<?php

require_once( 'Util.php' );

abstract class DatabaseConnection extends mysqli
{
	const USER     = 'XXXXXXXXX';
	const PASSWORD = 'XXXXXXXXX';
	const DATABASE = 'bike_model';

	public function __construct( $host, $user, $password, $database, $port )
	{
		parent::__construct( $host, $user, $password, $database, $port );

		if ( mysqli_connect_error() )
			throw new DatabaseConnectionException();
	}

	public function query( $query )
	{
		if ( !($result = parent::query( $query ) ) )
			Util::log( __METHOD__ . "() ERROR {$this->errno}: {$this->error}: \"{$query}\"" );
		
		return $result;
	}
}

class LocalDatabaseConnection extends DatabaseConnection 
{
	const HOST     = 'localhost';

	public function __construct()
        {
          $services_json = json_decode(getenv("VCAP_SERVICES"),true);
          $mysql_config = $services_json["mysql-5.1"][0]["credentials"];
          $username = $mysql_config["username"];
          $password = $mysql_config["password"];
          $hostname = $mysql_config["hostname"];
          $port = $mysql_config["port"];
          $db = $mysql_config["name"];
          //$link = mysql_connect("$hostname:$port", $username, $password);
          //$db_selected = mysql_select_db($db, $link);
          parent::__construct( $hostname , $username, $password, $db, $port );
        }
}

class DatabaseConnectionFactory 
{
	static protected $connection = null;

	public static function getConnection()
	{
		if ( self::$connection )
			return self::$connection;
		else
			return self::$connection = new LocalDatabaseConnection();
	}
}

class DatabaseException extends Exception
{
	public function __construct( $message, $code )
	{
		parent::__construct( $message, $code );
	}
}

class DatabaseConnectionException extends DatabaseException
{
	public function __construct( $message=null, $code=null )
	{
		if ( !$message )
			mysqli_connect_error();

		if ( !$code )
			mysqli_connect_errno();

		parent::__construct( mysqli_connect_error(), mysqli_connect_errno() );
	}
}

