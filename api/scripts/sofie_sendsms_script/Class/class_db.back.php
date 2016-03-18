<?php
/**
* Dbquery Class
*
* Customized sql functions (connection, database selection, query ). Allow to change the databases server 
*
* @package  maarch
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Nicolas Gualtieri
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

/**
* Class dbquery : Personnal Sql classes. Allow to change the databases server.
*
* @author  Nicolas Gualtieri
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package maarch
* @version 1.1
*/

class dbquery extends functions
{	
	/**
	* Debug mode activation
    * @access private
    * @var integer 1,0
    */
	private $debug;				// debug mode
	
	/**
	* Debug query (debug mode)
    * @access private
    * @var string
    */
	private $debug_query;		// request for the debug mode
	
	/**
	* SQL link identifier
    * @access private
    * @var integer
    */
	private $sql_link;			// sql link identifier
	
	/**
	* To know where the script was stopped
    * @access private
    * @var integer
    */
	private $what_sql_error;	// to know where the script was stopped
	
	/**
	* SQL query
    * @access private
    * @var string
    */
	private $query;				// query
	
	/**
	* Number of queries made with this identifier
    * @access private
    * @var integer
    */
	private $nb_query;			// number of queries made with this identifier
	
	/**
	* Sent query result
    * @access private
    * @var string
    */
	private $result;			// sent query result
	
	/*
	private function show_step($words)
	{
		// show the step where the error occurred
		if($this->debug == 1)
		{
			echo "- ".$words." ...<br />";
		}
	}
	*/
	
	/**
	* Database connection
	*
	*/
	public function connect()
	{
		// database connection
		$server = $_SESSION['config']['databaseserver'];
		$user = $_SESSION['config']['databaseuser'];
		$pass = $_SESSION['config']['databasepassword'];
		$this->base = $_SESSION['config']['databasename'];
		//echo "serveur : ".$_SESSION['config']['databaseserver']."<br>user : ".$user."<br>pass : ".$pass."<br>base : ".$this->base;
		//exit;
		$this->debug = 0;
		$this->nb_query = 0;
		
		$conn_string = "host=".$server." dbname=".$this->base." user=".$user." password=".$pass;
		
		//echo $conn_string;
		$this->sql_link = pg_connect($conn_string);
		
		if(!$this->sql_link)
		{
			$this->what_sql_error = 1;
			$this->error();
		}
		else
		{
			$this->select_db();
		}		
	}
	
	/**
	* Database selection
	*
	*/
	public function select_db()
	{
		// database selection
		//if(!@pg_select_db($this->base))
//		{
//			$this->what_sql_error = 2;
//			$this->error();
//		}
	}
	
	/**
	* Database query
	*
	* @param string $q_sql requete sql
	*/
	public function query($q_sql, $is_select = "yes")
	{
		// query
		$this->debug_query = $q_sql;
		$this->query = @pg_query($q_sql);
		$this->nb_query++;
		
			//if($is_select == "yes")
			//{
				/*if(!$this->query)
				{
					$this->what_sql_error = 3;
					$this->error();
				}*/
			//}
	}
	
	/**
	* Returns the query results in an object
	*
	*/
	public function fetch_object()
	{
		// return the query results in an object
		return @pg_fetch_object($this->query);
	}
	
	/**
	* Returns the query results in an array
	*
	*/
	public function fetch_array()
	{
		// return the query results in an array
		return @pg_fetch_array($this->query);
	}
	
		public function fetch_row()
	{
		// return the query results in an array
		return @pg_fetch_row($this->query);
	}
	/**
	* Returns the query number of results
	*
	*/
	public function nb_result()
	{
		// return the query number of results
		return @pg_num_rows($this->query);
	}
	
	/**
	* Database disconnection
	*
	*/
	public function deco()
	{
		// disconnection
		if(!pg_close($this->sql_link))
		{
			$this->what_sql_error = 4;
			$this->error();
		}
	}
	
	/**
	* Error management
	*
	*/
	private function error()
	{
		// errors gestion
		if($this->what_sql_error == 1)
		{
			echo "- <b>".$_SESSION['lang']['txt_error_connection']."</b> -<br /><br />";
		}
		
		if($this->what_sql_error == 2)
		{
			echo "- <b>".$_SESSION['lang']['txt_error_selection_base']."</b> -<br /><br />";
		}
		
		if($this->what_sql_error == 3)
		{
			echo "- <b>".$_SESSION['lang']['txt_error_requeste']."</b> -<br /><br />";
		
		}
		
		if($this->what_sql_error == 4)
		{
			echo "- <b>".$_SESSION['lang']['txt_error_closing_connection']."</b> -<br /><br />";
		}
		
		//echo $_SESSION['lang']['txt_error_num'].@pg_errno($this->sql_link)." ".$_SESSION['lang']['txt_has_just_occurred']." :<br />";
		//echo $_SESSION['lang']['txt_message']." : ". @pg_error($this->sql_link)."<br />";
		echo $_SESSION['lang']['txt_requeste']." : <textarea cols=\"70\" rows=\"10\">".$this->debug_query."</textarea>";
		exit;
	}
	
	/**
	* Shows the query for debug
	*
	*/
	public function show()
	{
		// show the query for debug
		echo "Query : <textarea cols=\"70\" rows=\"10\">".$this->debug_query."</textarea>";
	}
}
?>
