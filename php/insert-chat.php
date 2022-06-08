<?php 
    session_start();
    if(isset($_SESSION['unique_id'])){
        include_once "config.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        
        if(!empty($message)){
            $data=$message;
            $ciphering="aes-256-cbc";
            $encryption_key = $_SESSION["secrect_key"];
            $option=0;
            $encryption_iv=substr($incoming_id+$outgoing_id, 0,16);
            $encryption_iv=str_pad($encryption_iv, 16,'0',STR_PAD_BOTH);

            $enc=openssl_encrypt($data, $ciphering, $encryption_key, $option,$encryption_iv);
          
            $sql = mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$incoming_id}, {$outgoing_id}, '{$enc}')") or die();
        }
    }else{
        header("location: ../login.php");
    }


?>