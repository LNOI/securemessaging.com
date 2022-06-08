<?php

class DiffieHellman
{	

	public  $p; // prime modulus
	public  $g; // generator
	public $a; // Alice's private number
	public $b; // Bob's private number
	

	public function pg(){
		$is_prime = false;
		$range = $this->decimal_range( 256 );
		while($is_prime != true) {
			$p = gmp_random_range( $range['min'], $range['max'] );
			
			if(gmp_prob_prime ( $p, 100 )) {
				$is_prime = true;
			} else {
				unset($p);
			}
		}

		$is_primitive_root = 0;
		
		while(!$is_primitive_root) {
			$g = gmp_strval(gmp_random_range(2, gmp_sub($p, 1)));
			$n_to_pm1 = gmp_powm(gmp_strval($g), gmp_sub($p, 1), $p);
			
			if( $n_to_pm1 == 1) {
				$is_primitive_root = 1;
			}
		}
		return $p.'-'.$g;
		
	}
	public function prkey($p){
		$a =  gmp_strval(gmp_random_range(1, gmp_sub($p, 1)));
		return $a;
	}
	public function pbvalue($g,$a,$p){
		$value = gmp_powm($g, $a, $p);
		return  $value;
	}
	public function secrectkey($pbkey, $a, $p){
		$value = gmp_powm($pbkey, $a, $p);
		return $value;
		
	}
	public function decimal_range( $bits ) {
		$min = gmp_init(str_pad(1, $bits, 0, STR_PAD_RIGHT), 2);   
		$max = gmp_init(str_pad(1, $bits, 1, STR_PAD_RIGHT), 2);
		return array('min'=> $min, 'max'=> $max, 'range'=>"{$min}-{$max}");
	}
	
}
	$dh = new DiffieHellman();

	
	$sqlcheck=mysqli_query($conn, "SELECT * FROM messages WHERE incoming_msg_id = '{$_SESSION["unique_id"]}'");
	if(mysqli_num_rows($sqlcheck) > 0){
	 	$row = mysqli_fetch_assoc($sqlcheck);
	 	if($row["msg"]=="shakehands"){
	 		
	 		$sqlcheck1=mysqli_query($conn, "SELECT * FROM messages WHERE outgoing_msg_id = '{$_SESSION["unique_id"]}'");
	 		$row1=mysqli_fetch_assoc($sqlcheck1);
	 		if(strpos($row1["msg"],'-')){
	 			
	 			mysqli_query($conn, "UPDATE messages set msg='{$row1["msg"]}' where incoming_msg_id = '{$_SESSION["unique_id"]}'");
	 		}elseif ($row1["msg"]=="shakehands"){
	 			$pg=$dh->pg();
	 			mysqli_query($conn, "UPDATE messages set msg='{$pg}' where incoming_msg_id = '{$_SESSION["unique_id"]}'");
	 		}
	 	}
	 	elseif (strpos($row["msg"],'-'))
	 	{
	 			$ss=explode("-", $row["msg"]);
				$_SESSION['p']=$ss[0];
	 			$_SESSION['g']=$ss[1];
	 	}
	 	
	 	
	}

	if(isset($_SESSION["p"])&&isset($_SESSION["g"])){
		if(!isset($_SESSION["private_key"])){
			$_SESSION["private_key"]=$dh->prkey($_SESSION["p"]);
			$pb=$dh->pbvalue($_SESSION['g'],$_SESSION["private_key"],$_SESSION["p"]);
			$sqlgettarget=mysqli_query($conn, "SELECT * FROM messages WHERE incoming_msg_id = '{$_SESSION["unique_id"]}'");
			$rowt=mysqli_fetch_assoc($sqlgettarget);
			mysqli_query($conn, "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                                        VALUES ({$_SESSION['unique_id']}, {$rowt['outgoing_msg_id']}, {$pb})");
		}
	}

	if(!isset($_SESSION["secrect_key"])){
		$sqlsecret=mysqli_query($conn, "SELECT * FROM messages WHERE outgoing_msg_id = '{$_SESSION["unique_id"]}'");
		if(mysqli_num_rows($sqlsecret) > 0){
			while($row=mysqli_fetch_assoc($sqlsecret)){
				if(strlen($row["msg"])<=256)
				{
					$_SESSION["secrect_key"]=$dh->secrectkey($row["msg"],$_SESSION["private_key"],$_SESSION["p"]);

					
				}
			}	
		}
	}
	


?>