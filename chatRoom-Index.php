<?php
try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    // include('dbconnection.php');
    require('ChatRooms.php');
    $_SESSION['user_data'] = '78';
    $userID = $_SESSION['user_data'];

    $chat_object = new ChatRooms;

    $chat_data = $chat_object->get_all_chat_data();


?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Chat application </title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/src/parsley.min.css">
        <style type="text/css">
            html,
            body {
                height: 100%;
                width: 100%;
                margin: 0;
            }

            #wrapper {
                display: flex;
                flex-flow: column;
                height: 100%;
            }

            #remaining {
                flex-grow: 1;
            }

            #messages {
                height: 200px;
                background: whitesmoke;
                overflow: auto;
            }

            #chat-room-frm {
                margin-top: 10px;
            }

            #user_list {
                height: 450px;
                overflow-y: auto;
            }

            #messages_area {
                height: 650px;
                overflow-y: auto;
                background-color: #e6e6e6;
            }
        </style>
    </head>

    <body>
        <div class="container">

            <div class="row">

                <div class="col-lg-8">


                    <div class="card">
                        <div class="card-header">
                            <h3>Chat Room</h3>
                        </div>
                        <div class="card-body" id="messages_area">
                            <?php

                            foreach ($chat_data as $chat) {
                                if (isset($_SESSION['user_data'][$chat['from_uid']])) {
                                    $from = 'Me';
                                    $row_class = 'row justify-content-start';
                                    $background_class = 'text-dark alert-light';
                                } else {
                                    $from = $chat['FullName'];
                                    $row_class = 'row justify-content-end';
                                    $background_class = 'alert-success';
                                }

                                echo '
						<div class="' . $row_class . '">
							<div class="col-sm-10">
								<div class="shadow-sm alert ' . $background_class . '">
									<b>' . $from . ' - </b>' . $chat["message"] . '
									<br />
									<div class="text-right">
										<small><i>' . $chat["created_at"] . '</i></small>
									</div>
								</div>
							</div>
						</div>
						';
                            }
                            ?>
                        </div>


                        <form method="post" id="chat_form" data-parsley-errors-container="#validation_error">
                            <div class="input-group mb-3">
                                <textarea class="form-control" id="chat_message" name="chat_message" placeholder="Type Message Here" data-parsley-maxlength="1000" data-parsley-pattern="/^[a-zA-Z0-9\s]+$/" required></textarea>
                                <div class="input-group-append">
                                    <button type="submit" name="send" id="send" class="btn btn-primary">Send</button>
                                </div>
                            </div>
                            <div id="validation_error"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </body>



    <script type="text/javascript">
        $(document).ready(function() {

            var conn = new WebSocket('ws://localhost:8080');
            conn.onopen = function(e) {
                console.log("Connection established!");
            };
            conn.onmessage = function(e) {
                console.log(e.data);

                var data = JSON.parse(e.data);

                var row_class = '';

                var background_class = '';

                if (data.from == 'Me') {
                    row_class = 'row justify-content-start';
                    background_class = 'text-dark alert-light';
                } else {
                    row_class = 'row justify-content-end';
                    background_class = 'alert-success';
                }

                var html_data = "<div class='" + row_class + "'><div class='col-sm-10'><div class='shadow-sm alert " + background_class + "'><b>" + data.from + " - </b>" + data.msg + "<br /><div class='text-right'><small><i>" + data.dt + "</i></small></div></div></div></div>";

                $('#messages_area').append(html_data);

                $("#chat_message").val("");
            };
            $('#chat_form').parsley();

            $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

            $('#chat_form').on('submit', function(event) {

                event.preventDefault();

                if ($('#chat_form').parsley().isValid()) {

                    var user_id = '<?php echo $userID ?>';
                    var message = $('#chat_message').val();

                    var data = {
                        userId: user_id,
                        msg: message,
                        subject_id: 143,
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