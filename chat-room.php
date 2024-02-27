<?php
try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    // include('dbconnection.php');
    require('data/ChatRooms.php');

    $_SESSION['user_data'] = '';
    $_SESSION['teacher_data'] = '10';
    $userID = $_SESSION['user_data'];
    $teacherID = $_SESSION['teacher_data'];


    $chat_object = new ChatRooms;

    $chat_data = $chat_object->get_all_chat_data();


?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="style.css">
        <title>Chat application </title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/src/parsley.min.css">
    </head>

    <body>
        <section class="msger">
            <main class="msger-chat" id="messages_area">
                <?php

                foreach ($chat_data as $chat) {
                    if (isset($_SESSION['user_data']) && !empty($_SESSION['user_data'])) {
                        if ($_SESSION['user_data'] == $chat['from_uid']) {
                            $from = 'Me';
                            $row_class = 'msg right-msg';
                        } else {
                            $from = ($chat['FullName']  ? $chat['FullName'] : $chat['FirstName']);
                            $row_class = 'msg left-msg';
                        }
                    } else if (isset($_SESSION['teacher_data']) && !empty($_SESSION['teacher_data'])) {
                        if ($_SESSION['teacher_data'] == $chat['from_tid']) {
                            $from = 'Me';
                            $row_class = 'msg right-msg';
                        } else {
                            $from = ($chat['FullName']);
                            $row_class = 'msg left-msg';
                        }
                    }



                    echo '
                    <div class="' . $row_class . '">
                    <div class="msg-img" style="background-image: url(https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTMEyWfVRJkU8eqaIPA3wjJF2X-XEMephpq_w&usqp=CAU)"></div>
                        <div class="msg-bubble">
                            <div class="msg-info">
                                <div class="msg-info-name">' . $from . '</div>
                                <div class="msg-info-time">' . $chat["created_at"] . '</div>
                            </div>
                            <div class="msg-text">
                            ' . $chat["message"] . '
                            </div>
                        </div>
                    </div>
                    ';
                }
                ?>

            </main>
            <form method="post" id="chat_form" class="msger-inputarea" data-parsley-errors-container="#validation_error">
                <input type="text" class="msger-input form-control" id="chat_message" name="chat_message" placeholder="Enter your message..." data-parsley-maxlength="1000" data-parsley-pattern="/^[a-zA-Z0-9\s]+$/" required />
                <button type="submit" name="send" id="send" class="msger-send-btn">Send</button>
            </form>
            <div id="validation_error"></div>
        </section>
    </body>
    <script type="text/javascript">
        const msgerForm = document.querySelector(".msger-inputarea");
        const msgerInput = document.querySelector(".msger-input");
        const msgerChat = document.querySelector(".msger-chat");

        $(document).ready(function() {

            var conn = new WebSocket('ws://localhost:8080');
            conn.onopen = function(e) {
                console.log("Connection established!");
            };
            conn.onmessage = function(e) {
                console.log(e.data);

                var data = JSON.parse(e.data);

                var row_class = '';
                if (data.from == 'Me') {
                    row_class = 'msg right-msg';

                } else {
                    row_class = 'msg left-msg';

                }
                var html_data = " <div class='" + row_class + "'><div class='msg-img' style='background-image: url(https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTMEyWfVRJkU8eqaIPA3wjJF2X-XEMephpq_w&usqp=CAU)'></div><div class='msg-bubble'><div class='msg-info'><div class='msg-info-name'>" + data.from + "</div><div class='msg-info-time'>" + data.dt + "</div></div><div class='msg-text'>" + data.msg + "</div></div></div>";

                $('#messages_area').append(html_data);
                msgerChat.scrollTop = msgerChat.scrollHeight;
                $("#chat_message").val("");

            };
            $('#chat_form').parsley();

            $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

            $('#chat_form').on('submit', function(event) {

                event.preventDefault();

                if ($('#chat_form').parsley().isValid()) {
                    var message = $('#chat_message').val();
                    var user_id = null;
                    var teacherID = null;
                    <?php if (!empty($teacherID)) : ?>
                        teacherID = <?php echo json_encode($teacherID); ?>;
                    <?php endif; ?>
                    <?php if (!empty($userID)) : ?>
                        user_id = <?php echo json_encode($userID); ?>;
                    <?php endif; ?>


                    var data = {
                        userId: user_id,
                        teacherID: teacherID,
                        msg: message,
                        subject_id: 148,
                        group_id: 173,
                    };

                    conn.send(JSON.stringify(data));
                    $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);
                    console.log("console data " + data);

                }

            });

        });
    </script>

    </html>
<?php
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>