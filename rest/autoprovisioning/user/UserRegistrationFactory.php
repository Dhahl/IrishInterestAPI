<?php

include_once(__DIR__ . '/../../../../classes/access_user/db_config.php');
include_once(__DIR__ . '/../../../../classes/access_user/email_message.php');

class UserRegistrationFactory
{
    //USER VARIABLES
    private $user;
    private $user_full_name;
    private $user_email;
    private $id;
    private $user_pw;


    //DATABASE AND REGISTRATION REQUIREMENTS
    private $table_name = USER_TABLE;
    private $message;
    private $isError = false;
    private $auto_activation = true;
    var $webmaster_mail = WEBMASTER_MAIL;
    var $webmaster_name = WEBMASTER_NAME;
    var $admin_mail = ADMIN_MAIL;
    var $admin_name = ADMIN_NAME;

    private $error;

    private $db;

    /**
     * UserRegistrationFactory constructor.
     */
    public function __construct($database)
    {
        $this->db = $database;
        chmod(__DIR__ . '/../../../../API/', 0777);
    }

    function check_email($mail_address)
    {
        if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mail_address)) {
            return true;
        } else {
            return false;
        }
    }

    function ins_string($value, $type = "")
    {
        $value = (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
        switch ($type) {
            case "int":
                $value = ($value != "") ? intval($value) : NULL;
                break;
            default:
                $value = ($value != "") ? "'" . $value . "'" : "''";
        }
        return $value;
    }

    function register_user($first_login, $first_password, $confirm_password, $first_name, $first_info, $first_email, $publisher, $lastname, $telephone, $position, $userType)
    {
        if (strlen($first_login) >= 0) {
            if ($this->check_email($first_email)) {
                $this->user_email = $first_email;
                $this->user = $first_login;
                $this->user_full_name = $first_login . " " . $lastname;
                $this->user_pw = $first_password;

                $accessLevel = "4";
                if ($userType == 'publisher') $accessLevel = "2";
                if ($userType == 'promoter') $accessLevel = "3";

                    $sql = sprintf("INSERT INTO %s (id, login, pw, real_name, extra_info, email, access_level, active, lastname, publisher, telephone, position) 
							VALUES (NULL, %s, %s, %s, %s, %s, %d,'n', %s, %s, %s, %s)",
                    $this->table_name,
                    $this->ins_string($first_login),
                    $this->ins_string(md5($first_password)),
                    $this->ins_string($first_name),
                    $this->ins_string($first_info),
                    $this->ins_string($this->user_email),
                    $accessLevel,
                    $this->ins_string($lastname),
                    $this->ins_string($publisher),
                    $this->ins_string($telephone),
                    $this->ins_string($position));
                //TODO ---- NEED TO GET THE CREATED USERS ID........
                try {

                    $this->db->query($sql);

                } catch (Exception $e) {
                    throw $e;
                }

                try {

                $this->db->query('SELECT * FROM users WHERE email = ' . "'" . $this->user_email . "'");
                $this->id = $this->db->loadObjectList()[0]->id;

                } catch (Exception $e) {
                    throw $e;
                }

                $sendErr = $this->send_mail($this->user_email);
                if ($sendErr) {
                    $this->db->query(sprintf("DELETE FROM %s WHERE id = %s", $this->table_name, $this->id));
                    $this->db->loadObjectList();
                    $this->message = $sendErr;
                    $this->isError = true;
                }
                //$ins_res = mysql_query($sql);
                //if ($ins_res) {
                ////    $this->user_pw = $first_password;
                //    $sendErr = $this->send_mail($this->user_email);
                ////        $this->msgNo = 1;
                 //   } else {
                 //       mysql_query(sprintf("DELETE FROM %s WHERE id = %s", $this->table_name, $this->id));
                //        $this->message = $sendErr;
                 //       $this->isError = true;
                //    }
                //}
            }
        }

    }

    private function send_mail($mail_address, $num = 29)
    {
        $host = "http://" . $_SERVER['HTTP_HOST'];

        $from_address = "administrator@irishinterest.ie";
        $from_name = "Irish Interest";

        $reply_name = $from_name;
        $reply_address = $from_address;
        $reply_address = $from_address;
        $error_delivery_name = $from_name;
        $error_delivery_address = $from_address;

        $to_name = $this->user;
        $to_address = $mail_address;


        if (!$this->auto_activation) {
            //echo "if (!$this->auto_activation)";
            if ($num == 35) {    //forgot password
                $subject = "An Irish Interest Publisher has Forgotten their Password ";
                $body = "Please issue User " . $this->login . " with a new Password.";
            } else {

                $subject = "New user request...";
                $body = "New user registration requested by " . $_SESSION['username'] . ' on ' . date("Y-m-d") . ":\r\n\r\nClick here to enter the admin page:\r\n\r\n" . "http://" . $_SERVER['HTTP_HOST'] . '/login';
            }
        } else {
            // echo "body = $this->messages($num)<br>";
            $subject = "Irish Interest activation link for " . $this->user_full_name;// $this->messages($num);
            $body = " to activate your request click the following link:<br />" . $host . $this->class_path . '/login' . "?ident=" . $this->id . "&activate=" . md5($this->user_pw) . "&language=" . "en";
        }
        /* end set mail info  */
        $email_message = new email_message_class();
        $email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
        $email_message->SetEncodedEmailHeader("Cc", $this->admin_mail, "Irish Interest Administrator");
        $email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
        $email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
        $email_message->SetHeader("Sender", $from_address);
        if (defined("PHP_OS")
            && strcmp(substr(PHP_OS, 0, 3), "WIN"))
            $email_message->SetHeader("Return-Path", $error_delivery_address);

        $email_message->SetEncodedHeader("Subject", $subject);
        /**/
        $image = array(
            "FileName" => __DIR__ . '/../../../../emailbackground.png',
            "Content-Type" => "automatic/name",
            "Disposition" => "inline",
        );
        $email_message->CreateFilePart($image, $image_part);

        $image_content_id = $email_message->GetPartContentID($image_part);

        $image = array(
            "FileName" => __DIR__ . '/../../../../emailbackground.png',
            "Content-Type" => "automatic/name",
            "Disposition" => "inline",
        );

        $email_message->CreateFilePart($image, $background_image_part);

        $background_image_content_id = "cid:" . $email_message->GetPartContentID($background_image_part);

        $html_message = "<html>
                       <head>
                            <title>$subject</title>
                       </head>
                       <body  background=\"cid:" . $image_content_id . "\"style=\"width:100%; height:100%;\" >
                            <table background=\"cid:" . $image_content_id . "\"style=\"width:100%; height:100%;background-repeat:no-repeat;\" >
                                <tr style=\"width:100%;\"><td style=\"width:15%;height:450px\"></td>
                                    <td style=\"width:100%;\"><h1 style=\"color:black\">$subject</h1>
                                    <p style=\"color:black\">Hello " . strtok($to_name, " ") . "," . $body . "</p><p style=\"color:black\">" . // .  // second image not required (LE)
            "Thank you,<br><b>$from_name</b></p>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>";

        $email_message->CreateQuotedPrintableHTMLPart($html_message, "", $html_part);
        $email_message->CreateQuotedPrintableTextPart($email_message->WrapText($body), "", $text_part);

        $alternative_parts = array(
            $text_part,
            $html_part
        );
        $email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
        $related_parts = array(
            $alternative_part,
            $image_part,

        );
        $email_message->AddRelatedMultipart($related_parts);

        $attachment = array(
            "Data" => $body,
            "Name" => "attachment.txt",
            "Content-Type" => "automatic/name",
            "Disposition" => "attachment",
        );
        $email_message->AddFilePart($attachment);
        $this->error = $email_message->Send();
        return ($this->error);

    }
}