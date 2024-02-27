<?php
try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    class ChatRooms
    {

        private $chat_id;
        private $from_uid;
        private $from_tid;
        private $message;
        private $created_at;
        private $subject_id;
        private $group_id;
        protected $dbh;

        public function setChatId($chat_id)
        {
            $this->chat_id = $chat_id;
        }

        function getChatId()
        {
            return $this->chat_id;
        }

        function setUserId($from_uid)
        {
            $this->from_uid = $from_uid;
        }

        function getUserId()
        {
            return $this->from_uid;
        }

        function getTeacherId()
        {
            return $this->from_tid;
        }

        function setTeacherId($from_tid)
        {
            $this->from_tid = $from_tid;
        }

        function setMessage($message)
        {
            $this->message = $message;
        }

        function getMessage()
        {
            return $this->message;
        }
        function setSubject($subject_id)
        {
            $this->subject_id = $subject_id;
        }

        function getSubject()
        {
            return $this->subject_id;
        }
        function setGroupID($group_id)
        {
            $this->group_id = $group_id;
        }
        function getGroupID()
        {
            return $this->group_id;
        }
        function setCreatedOn($created_at)
        {
            $this->created_at = $created_at;
        }


        function getCreatedOn()
        {
            return $this->created_at;
        }

        public function __construct()
        {
            require_once("dbconnection.php");


            $this->dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            // Set the PDO error mode to exception
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        function save_chat()
        {
            $query = "
		INSERT INTO chats 
			(from_uid, from_tid, message,subject_id, group_id, created_at) 
			VALUES (:from_uid, :from_tid, :message,:subject_id, :group_id, :created_at)
		";

            $statement = $this->dbh->prepare($query);

            $statement->bindParam(':from_uid', $this->from_uid);

            $statement->bindParam(':from_tid', $this->from_tid);

            $statement->bindParam(':message', $this->message);

            $statement->bindParam(':subject_id', $this->subject_id);

            $statement->bindParam(':group_id', $this->group_id);

            $statement->bindParam(':created_at', $this->created_at);

            $result = $statement->execute();

            if (!$result) {
                $errorInfo = $statement->errorInfo();
                echo "SQL error: " . $errorInfo[2];
            }
            echo "SQL query: $query"; // Add this line
            $statement = $this->dbh->prepare($query);
        }

        function get_all_chat_data()
        {

            $testSubject = '148';
            $testGroup = '173';
            $_SESSION['user_id'] = '78';

            $query = "
            SELECT DISTINCT 
            chats.chat_id,
            chats.from_tid,
            chats.message,
            chats.from_uid,
            chats.subject_id,
            chats.group_id,
            chats.created_at,
            tblteacher.FirstName,
            tblteacher.LastName,
            tbluser.*
        FROM chats
        INNER JOIN tblsubject ON tblsubject.ID = chats.subject_id
        INNER JOIN tblgroup ON tblgroup.group_id = chats.group_id
        LEFT JOIN tbluser ON tbluser.ID = chats.from_uid
        LEFT JOIN tblgroup_member ON tblgroup_member.group_id = tblgroup.group_id
        LEFT JOIN tblclass ON tblclass.ID = tblgroup.class_id
        LEFT JOIN tbl_classdetail ON tbl_classdetail.classID = tblclass.ID
        LEFT JOIN tblteacher ON tblteacher.ID = chats.from_tid 
        WHERE 
            ( 
                (chats.from_uid IS NOT NULL AND tblgroup_member.user_id = :testUser) OR 
                (chats.from_tid IS NOT NULL AND tbl_classdetail.teacherID = tblteacher.ID)
            )
            AND tblgroup.group_id = :testGroup 
            AND tblsubject.ID = :testSubject
        ORDER BY chats.chat_id ASC";


            $statement = $this->dbh->prepare($query);
            $statement->bindParam(':testGroup', $testGroup);
            $statement->bindParam(':testSubject', $testSubject);
            $statement->bindParam(':testUser', $_SESSION['user_id']);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
