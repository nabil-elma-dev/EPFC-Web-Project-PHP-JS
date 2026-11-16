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

            // 
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

        public function delete(): void{
            // Phase 1 : verifying $_POST["id_message"]
            // Phase 2 : exception (if not valid) OR delete method 
            // Phase 3 : redirect towards recipient messages after successfull delete 
        }

        /*
        
            
        */
    }
?>