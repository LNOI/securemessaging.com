<?php 
    

    session_start();
    if(isset($_SESSION['unique_id'])){

        include_once "config.php";
        include_once "key.php";
        $outgoing_id = $_SESSION['unique_id'];
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $output = "";
        $sql = "SELECT * FROM messages LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
                WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
                OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) ORDER BY msg_id";

        $query = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($query) > 0){
            $ciphering="aes-256-cbc";
            $option=0;
            $decryption_key = $_SESSION["secrect_key"];
            $encryption_iv=substr($incoming_id+$outgoing_id, 0,16);
            $encryption_iv=str_pad($encryption_iv, 16,'0',STR_PAD_BOTH);

            while($row = mysqli_fetch_assoc($query)){
                
                $enc=$row["msg"];
                
                $dec=openssl_decrypt($enc, $ciphering, $encryption_key, $option,$encryption_iv);
                if($dec){
                     if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div class="chat outgoing">
                                <div class="details">
                                    <p>'.$dec.'</p>
                                </div>
                                </div>';
                    }else{
                      
                        $output .= '<div class="chat incoming">
                                  
                                    <div class="details">
                                        <p>'. $dec.'</p>
                                    </div>
                                    </div>';
                    }
                }
               
               
            }
        }else{
            $output .= '<div class="text">No messages are available. Once you send message they will appear here.</div>';
        }

        echo $output;
    }else{
        header("location: ../login.php");
    }

?>