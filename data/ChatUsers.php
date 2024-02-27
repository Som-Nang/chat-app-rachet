<?php
//ChatUser.php
class ChatUser
{
    private $user_id;
    public $dbh;

    public function __construct()
    {
        require_once("dbconnection.php");


        $this->dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        // Set the PDO error mode to exception
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    function getUseruser_id()
    {
        return $this->user_id;
    }

    function get_user_data_by_email()
    {
        $query = "
		SELECT * FROM chat_user_table 
		WHERE user_email = :user_email
		";

        $statement = $this->connect->prepare($query);

        $statement->bindParam(':user_email', $this->user_email);

        if ($statement->execute()) {
            $user_data = $statement->fetch(PDO::FETCH_ASSOC);
        }
        return $user_data;
    }

    function get_user_data_by_id()
    {
        $query = "
		SELECT * FROM tbluser 
		WHERE ID = :user_id";

        $statement = $this->dbh->prepare($query);

        $statement->bindParam(':user_id', $this->user_id);

        try {
            if ($statement->execute()) {
                $user_data = $statement->fetch(PDO::FETCH_ASSOC);
            } else {
                $user_data = array();
            }
        } catch (Exception $error) {
            echo $error->getMessage();
        }
        return $user_data;
    }

    function get_user_all_data()
    {
        $query = "
		SELECT * FROM tbluser 
		";

        $statement = $this->dbh->prepare($query);

        $statement->execute();

        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
