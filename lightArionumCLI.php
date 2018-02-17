#!/usr/bin/php
<?php
/* 
The MIT License (MIT)
Copyright (c) 2018 AroDev 

www.arionum.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
OR OTHER DEALINGS IN THE SOFTWARE.
*/

error_reporting(0);


if (!extension_loaded("openssl") && !defined("OPENSSL_KEYTYPE_EC")) die("Openssl php extension missing");
if(floatval(phpversion())<7.2) die("The minimum php version required is 7.2");


$arg1=trim($argv[1]);
$arg2=trim($argv[2]);
$arg3=trim($argv[3]);
$arg4=trim($argv[4]);
if((empty($arg1)&&file_exists("wallet.aro"))||$arg1=="help"||$arg1=="-h"||$arg1=="--help"){
die("./lightArionumCLI <command> <options>\n
Commands:\n
balance\t\t\t\tprints the balance of the wallet 
balance <address>\t\tprints the balance of the specified address
export\t\t\t\tprints the wallet data
block\t\t\t\tshow data about the current block
encrypt\t\t\t\tencrypts the wallet
decrypt\t\t\t\tdecrypts the wallet
transactions\t\t\tshow the latest transactions
transaction <id>\t\tshows data about a specific transaction
send <address> <value> <message>\tsends a transaction (message optional)
");

}

   //all credits for this base58 functions should go to tuupola / https://github.com/tuupola/base58/
    function baseConvert(array $source, $source_base, $target_base)
    {
        $result = [];
        while ($count = count($source)) {
            $quotient = [];
            $remainder = 0;
            for ($i = 0; $i !== $count; $i++) {
                $accumulator = $source[$i] + $remainder * $source_base;
                $digit = (integer) ($accumulator / $target_base);
                $remainder = $accumulator % $target_base;
                if (count($quotient) || $digit) {
                    array_push($quotient, $digit);
                };
            }
            array_unshift($result, $remainder);
            $source = $quotient;
        }
        return $result;
    }
    function base58_encode($data)
    {
        if (is_integer($data)) {
            $data = [$data];
        } else {
            $data = str_split($data);
            $data = array_map(function ($character) {
                return ord($character);
            }, $data);
        }


        $converted = baseConvert($data, 256, 58);

        return implode("", array_map(function ($index) {
                $chars="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
            return $chars[$index];
        }, $converted));
    }
     function base58_decode($data, $integer = false)
    {
        $data = str_split($data);
        $data = array_map(function ($character) {
                $chars="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
            return strpos($chars, $character);
        }, $data);
        /* Return as integer when requested. */
        if ($integer) {
            $converted = baseConvert($data, 58, 10);
            return (integer) implode("", $converted);
        }
        $converted = baseConvert($data, 58, 256);
        return implode("", array_map(function ($ascii) {
            return chr($ascii);
        }, $converted));
    }




function pem2coin ($data) {
    $data=str_replace("-----BEGIN PUBLIC KEY-----","",$data);
    $data=str_replace("-----END PUBLIC KEY-----","",$data);
    $data=str_replace("-----BEGIN EC PRIVATE KEY-----","",$data);
    $data=str_replace("-----END EC PRIVATE KEY-----","",$data);
    $data=str_replace("\n","",$data);
    $data=base64_decode($data);
    return base58_encode($data);
    
}

function get_address($hash){
	      for($i=0;$i<9;$i++) $hash=hash('sha512',$hash, true);	
			return base58_encode($hash);
     }


function coin2pem ($data, $is_private_key=false) {

    
    
       $data=base58_decode($data);
       $data=base64_encode($data);

        $dat=str_split($data,64);
        $data=implode("\n",$dat);

    if($is_private_key) return "-----BEGIN EC PRIVATE KEY-----\n".$data."\n-----END EC PRIVATE KEY-----\n";
    return "-----BEGIN PUBLIC KEY-----\n".$data."\n-----END PUBLIC KEY-----\n";
}
function ec_sign($data, $key){

    $private_key=coin2pem($key,true);
   
   
    $pkey=openssl_pkey_get_private($private_key);
  
    $k=openssl_pkey_get_details($pkey);


    openssl_sign($data,$signature,$pkey,OPENSSL_ALGO_SHA256);
  
    
    
    return base58_encode($signature);
    
}


function ec_verify($data, $signature, $key){

    

    $public_key=coin2pem($key);
   
    $signature=base58_decode($signature);
    
    $pkey=openssl_pkey_get_public($public_key);
  
    $res=openssl_verify($data,$signature,$pkey,OPENSSL_ALGO_SHA256);
  
 
    if($res===1) return true;
    return false;
}


function peer_post($url, $data=array()){

	$f=file("http://api.arionum.com/peers.txt");
    shuffle($f);
    
	foreach($f as $x){
		if(strlen(trim($x))>5){
			$peer=trim($x);
			break;
		}
	}
   
    if(empty($peer)) return false;
echo "Using node: $peer\n";
    $postdata = http_build_query(
        array(
            'data' => json_encode($data),
            "coin"=>" arionum"
            )
    );
    
    $opts = array('http' =>
        array(
            'timeout' => "300",
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    
    $context  = stream_context_create($opts);
    
    $result = file_get_contents($peer.$url, false, $context);
    $res=json_decode($result,true);
    return $res;


}

function readPasswordSilently(string $prompt = ''): string {
    if(checkSystemFunctionAvailability('shell_exec') && rtrim(shell_exec("/usr/bin/env bash -c 'echo OK'")) === 'OK') {
        $password = rtrim(
            shell_exec(
                "/usr/bin/env bash -c 'read -rs -p \""
                . addslashes($prompt)
                . "\" mypassword && echo \$mypassword'"
            )
        );
        echo PHP_EOL;
    } else {
        /**
         * Can't invoke bash or shell_exec is disabled, let's do it with a regular input instead.
         */
        $password = readline($prompt . ' ');
    }

    return $password;
}

function checkSystemFunctionAvailability(string $function_name): bool {
    return !in_array(
        $function_name,
        explode(',', ini_get('disable_functions'))
    );
}

function isAddressValid(string $address): bool {
    return preg_match('/^[a-z0-9]+$/i', $address);
}

if(!file_exists("wallet.aro")){
	echo "No ARO wallet found. Generating a new wallet!\n";
	$q=readline("Would you like to encrypt this wallet? (y/N) ");
	$encrypt=false;
	if(substr(strtolower(trim($q)),0,1)=="y"){
		do {
			$pass=readPasswordSilently("Password:");
			if(strlen($pass)<8) {
				echo "The password must be at least 8 characters long\n";
				continue;
		}
		$pass2=readPasswordSilently("Confirm Password:");
		if($pass==$pass2) break;
		else echo "The passwords did not match!\n";
		} while(1);
		$encrypt=true;
	}

		$args = array(
			"curve_name" => "secp256k1",
			"private_key_type" => OPENSSL_KEYTYPE_EC,
		);
		
		
		$key1 = openssl_pkey_new($args);
		
		openssl_pkey_export($key1, $pvkey);
		
		$private_key= pem2coin($pvkey);
	
		$pub = openssl_pkey_get_details($key1);
		
		$public_key= pem2coin($pub['key']);

		$wallet="arionum:".$private_key.":".$public_key;
		if(strlen($private_key)<20||strlen($public_key)<20) die("Could not generate the EC key pair. Please check the openssl binaries.");
		if($encrypt===true){
			$password = substr(hash('sha256', $pass, true), 0, 32);
            $iv=random_bytes(16);
			$wallet = base64_encode($iv.base64_encode(openssl_encrypt($wallet, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)));
		}

		$res=file_put_contents("wallet.aro",$wallet);
		if($res===false||$res<30) die("Could not write the wallet file! Please check the permissions on the current directory.\n");
        $address=get_address($public_key);
	        echo "Your Address is: ".$address."\n";
                echo "Your Public Key is: $public_key\n";
                echo "Your Private Key is: $private_key\n";
		

} else {

$wallet=trim(file_get_contents("wallet.aro"));
if(substr($wallet,0,7)!="arionum"){
	echo "This wallet is encrypted.\n";
	do {
		$pass=readPasswordSilently("Password:");

		$w=base64_decode($wallet);
        $iv=substr($w,0,16);
 

		$enc=substr($w,16);
		$password = substr(hash('sha256', $pass, true), 0, 32);
        $decrypted = openssl_decrypt(base64_decode($enc), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv);
        
		if(substr($decrypted,0,7)=="arionum") { $wallet=$decrypted; break; }
		echo "Invalid password!\n";
} while(1);


}
$a=explode(":",$wallet);
$public_key=trim($a[2]);
$private_key=trim($a[1]);


$address=get_address($public_key);
echo "Your address is: ".$address."\n\n";
}





if($arg1=="balance"){
    if (!empty($arg2)) {
        echo "Checking balance of the specified address: {$arg2}" . PHP_EOL;
        if (!isAddressValid($arg2)) {
            die("Error: invalid address format." . PHP_EOL);
        }
        $address = $arg2;
    }
    $res=peer_post("/api.php?q=getPendingBalance",array("account"=>$address));
    if($res['status']!="ok") die("ERROR: $res[data]\n");
    else echo "Balance: $res[data]\n";
}elseif($arg1=="export"){
    echo "Your Public Key is: $public_key\n";
    echo "Your Private Key is: $private_key\n";

} elseif($arg1=="transactions"){
    $res=peer_post("/api.php?q=getTransactions",array("account"=>$address));
    if($res['status']!="ok") die("ERROR: $res[data]\n");
    echo "ID\tTo\tType\tSum\n";
    foreach($res['data'] as $x){
        printf("%4s %4s %4s %4.f\n",$x['id'],$x['dst'],$x['type'],$x['val']);
    }
} elseif($arg1=="block"){
    $res=peer_post("/api.php?q=currentBlock");
    if($res['status']!="ok") die("ERROR: $res[data]\n");
    foreach($res['data'] as $x=>$l){
        echo "$x = $l\n";
    }
} elseif($arg1=="decrypt"){
    $wallet="arionum:$private_key:$public_key";
    $res=file_put_contents("wallet.aro",$wallet);
    if($res===false||$res<30){
        echo "Your Public Key is: $public_key\n";
        echo "Your Private Key is: $private_key\n";
        die("Could not write the wallet file! Please check the permissions on the current directory and save a backup of the above keys.\n");
    }
    echo "The wallet has been decrypted!\n";
}elseif($arg1=="encrypt"){
    do {
        $pass=readPasswordSilently("Password:");
        if(strlen($pass)<8) {
            echo "The password must be at least 8 characters long\n";
            continue;
    }
    $pass2=readPasswordSilently("Confirm Password:");
    if($pass==$pass2) break;
    else echo "The passwords did not match!\n";
    } while(1);
    $wallet="arionum:$private_key:$public_key";
    $password = substr(hash('sha256', $pass, true), 0, 32);
    $iv=random_bytes(16);
    $wallet = base64_encode($iv.base64_encode(openssl_encrypt($wallet, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, $iv)));
    $res=file_put_contents("wallet.aro",$wallet);
    if($res===false||$res<30){
        echo "Your Public Key is: $public_key\n";
        echo "Your Private Key is: $private_key\n";
        die("Could not write the wallet file! Please check the permissions on the current directory and save a backup of the above keys.\n");
    }
} elseif($arg1=="transaction"){
    $res=peer_post("/api.php?q=getTransaction",array("transaction"=>$arg2));
    if($res['status']!="ok") die("ERROR: $res[data]\n");
    foreach($res['data'] as $x=>$l){
        echo "$x = $l\n";
    }
} elseif($arg1=='send'){
    if(empty($arg2)) die("ERROR: Invalid destination address");
    if(empty($arg3)) die("ERROR: Invalid sum");

    $res=peer_post("/api.php?q=getPendingBalance",array("account"=>$address));
    if($res['status']!="ok") die("ERROR: $res[data]\n");
    $balance=$res['data'];
    $fee=$arg3*0.0025;
    if($fee<0.00000001) $fee=0.00000001;
    if($fee>10) $fee=10;
    $total=$arg3+$fee;
    
    $val=number_format($arg3,8,".","");
    $fee=number_format($fee,8,".","");
    if($balance<$total) die("ERROR: Not enough funds in balance\n");
    $date=time();
    $info=$val."-".$fee."-".$arg2."-".$arg4."-1-".$public_key."-".$date;
    $signature=ec_sign($info,$private_key);
    

$res=peer_post("/api.php?q=send",array("dst"=>$arg2,"val"=>$val, "signature"=>$signature, "public_key"=>$public_key, "version"=>1, "message"=>$arg4, "date"=>$date));

if($res['status']!="ok") die("ERROR: $res[data]\n");
else echo "Transaction sent! Transaction id: $res[data]\n";
} else {
    echo "Invalid command\n";
}



?>
