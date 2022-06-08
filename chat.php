<?php 
  session_start();
  include_once "php/config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<?php include_once "header.php"; ?>
<?php include_once "insert_chat.php"; ?>
<body>
  <div class="wrapper">
    <section class="chat-area">
      <header>
        <?php 
          $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
          $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");
          if(mysqli_num_rows($sql) > 0){
            unset($_SESSION['p']);
            unset($_SESSION['g']);
            unset($_SESSION["private_key"]);
            unset($_SESSION["secrect_key"]);

            $sql1 = mysqli_query($conn, "SELECT msg FROM messages WHERE incoming_msg_id = '{$user_id}' and outgoing_msg_id = '{$_SESSION["unique_id"]}' ");
            $sql2 = mysqli_query($conn, "SELECT msg FROM messages WHERE incoming_msg_id = '{$_SESSION["unique_id"]}' and outgoing_msg_id = '{$user_id}' ");
            if(mysqli_num_rows($sql1) > 0){
               if(mysqli_num_rows($sql2) == 0){
                  mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$_SESSION['unique_id']}, {$user_id}, 'shakehands')") or die();
               }
               mysqli_query($conn, "UPDATE messages set msg='shakehands' where incoming_msg_id = '{$user_id}' ");
            }else{
               mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$_SESSION['unique_id']}, {$user_id}, 'hello')") or die();
            }
            $row = mysqli_fetch_assoc($sql);


           
          }else{
            header("location: users.php");
          }
        ?>
        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img src="php/images/<?php echo $row['img']; ?>" alt="">
        <div class="details">
          <span><?php echo $row['fname']. " " . $row['lname'] ?></span>
          <p><?php echo $row['status']; ?></p>
        </div>
      </header>
      <div class="chat-box">

      </div>
      <form action="#" class="typing-area">
        <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
        <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </div>

  <script src="static/chat.js"></script>

</body>
</html>
