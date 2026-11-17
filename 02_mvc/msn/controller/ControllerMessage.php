<?php 
    require_once "model/Member.php";
    require_once "model/Message.php";
    require_once "framework/View.php";
    require_once "framework/Controller.php";
    
    class ControllerMessage extends Controller {
        public function index(): void {
            // Grabbing user in session
            $user = Member::get_by_id($this->get_user_id_or_redirect());
        
            // Grabbing recipient (either user in session OR param1)
            $recipient = isset($_GET["param1"]) ?
                Member::get_member_by_pseudo($_GET["param1"])
                : $user; 
            
            // Doublecheck on $recipient (get_member_by_pseudo method might return false)
            $recipient = $recipient === false ?
                $user
                : $recipient;

            // Initiating variables body, private to structure new Message object && errors if models returns any
            $body = $_POST["body"] ?? null;
            $private = false;
            $errors = [];
            
            if ($body !== null) {
                $private = isset($_POST["private"]);
                $message = new Message($user, $recipient, $body, $private);
                $errors = $message->validate();
                if ($errors === []) {
                    $errors = $user->write_message($message);
                }
                if ($errors === []) {
                    $this->redirect("message", "index", $recipient->get_pseudo());
                }
            }

            (new View("messages"))->show([  "user" => $user,
                                            "recipient" => $recipient,
                                            "body" => $body,
                                            "private" => $private,
                                            "errors" => $errors
                                        ]
            );
        }

        public function delete(): void {  
            $user = Member::get_by_id($this->get_user_id_or_redirect());  
            $post_id = filter_var($_POST["id_message"] ?? "", FILTER_VALIDATE_INT);
            if ($post_id === false) {
                Tools::abort("Wrong/missing param or action no permitted");
            } 
            $message = Message::get_message($post_id);
            if ($message === false || !$message->can_be_deleted_by($user)) {
                Tools::abort("Wrong/missing param or action no permitted");
            }
            $message->delete($user);
            $this->redirect("message", "index", $message->get_recipient()->get_pseudo()); 
        }
    }
?>