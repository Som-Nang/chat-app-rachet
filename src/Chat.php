<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require dirname(__DIR__) . "/data/ChatRooms.php";
require dirname(__DIR__) . "/data/ChatUsers.php";
class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo 'Server Started';
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        echo 'Server Started';
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf(
            'Connection %d sending message "%s" to %d other connection%s' . "\n",
            $from->resourceId,
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );

        $data = json_decode($msg, true);

        $chat_object = new \ChatRooms;

        $chat_object->setUserId($data['userId']);

        $chat_object->setTeacherId($data['teacherID']);

        $chat_object->setMessage($data['msg']);

        $chat_object->setSubject($data['subject_id']);

        $chat_object->setGroupID($data['group_id']);

        $chat_object->setCreatedOn(date("Y-m-d h:i:s"));

        $chat_object->save_chat();

        $user_object = new \ChatUser;
        if (isset($data['userId']) && $data['userId'] != '') {
            $user_object->setUserId($data['userId']);
            $user_data = $user_object->get_user_data_by_id();
            $user_name = $user_data['FullName'];
        } elseif (isset($data['teacherID']) && $data['teacherID'] != '') {
            $user_object->setTeacherId($data['teacherID']);
            $user_data = $user_object->get_teacher_data_by_id();
            $user_name = $user_data['FirstName'];
        }
        $data['dt'] = date("d-m-Y h:i:s");


        foreach ($this->clients as $client) {
            /*if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }*/

            if ($from == $client) {
                $data['from'] = 'Me';
            } else {
                $data['from'] = $user_name;
            }

            $client->send(json_encode($data));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
