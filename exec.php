$query = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=fax-files/dest.tiff /var/www/html/test.pdf";
$res = exec($query, $output, $retval);


DELIMITER //

CREATE OR REPLACE PROCEDURE `sp_test`(
IN `marriageId` INT, 
IN `roleId` INT, 
IN `genderId` INT, 
IN `cityId` INT, 
IN `provinceId` INT, 
IN `startDate` longtext, 
IN `endDate` longtext) 
BEGIN 
DECLARE v longtext;
DECLARE q longtext; 
set v = "";
set q = "";
IF (marriageId IS NOT NULL) THEN SET v = CONCAT(v,',marriage');END IF;
IF(roleId IS NOT NULL) THEN SET v = CONCAT(v,',role');END IF;
IF(genderId IS NOT NULL) THEN SET v = CONCAT(v,',gender');END IF;
IF(cityId IS NOT NULL) THEN SET v = CONCAT(v,',city');END IF;
IF(provinceId IS NOT NULL) THEN SET v = CONCAT(v,',province');END IF;

set q = CONCAT("select count(*) as `count`",v," from users where signupdate >= '",startDate,"' and signupdate <= '",endDate,"'");
IF (v <> "") THEN SET q = CONCAT(q," group by ",SUBSTRING(v FROM 2));END IF;
PREPARE stmt FROM q;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

END //

DELIMITER ;


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function checkAndCreatedPath($path){
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}
$output=null;
$retval=null;
$url = isset($_GET['url']) ? trim($_GET['url']) : "";
if($url == ""){
    echo json_encode(['status' => false ,'massage' => 'please send {url => pdf,txt,tiff,png url}']); 
    die;
}
$extFile = explode('.',basename($url))[1];
if(!in_array($extFile,['pdf','txt','tiff','png'])) {
    echo json_encode(['status' => false ,'massage' => 'file extention incorrect. please send pdf,txt,tiff,png file url']); 
    die;
}
$string = generateRandomString(20);
$filename = $string.".".$extFile;
$file = file_put_contents($filename,file_get_contents($url));
$path = "/var/www/html/api/fax-files/";
$filepath = checkAndCreatedPath($path);
$resultFileName = "$path.$string.tiff";
$command = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=$resultFileName $filename";
exec($command, $output, $retval);
unlink($filename);
http_response_code(201);
echo json_encode(['status' => true ,'massage' => 'File Was Created' , 'data' => $resultFileName]);
die;
        
        
        
        function sendFax($from, $to, $data)
        {
            $faxhost = escapeshellarg("$faxexten@127.0.0.1");
            $destine = escapeshellarg($destine);
            $data = escapeshellarg($data);
            $output = $retval = NULL;
            exec("sendfax -D -h $faxhost -n -d $destine $data 2>&1", $output, $retval);
            $regs = NULL;
            if ($retval != 0 || !preg_match('/request id is (\d+)/', implode('', $output), $regs)) {
                $this->errMsg = implode('<br/>', $output);
                return NULL;
            }
            return $regs[1];
        }
        
        
        $ruta_temp = tempnam('/tmp', 'data_');
        file_put_contents($ruta_temp, iconv('UTF-8', 'ISO-8859-15//TRANSLIT', $data_content));
        $ruta_archivo = tempnam('/tmp', 'data_');
        $output = $retval = NULL;
        exec('/usr/sbin/textfmt -B -f Courier-Bold -Ml=0.4in -p11 < '.
            escapeshellarg($ruta_temp).' > '.escapeshellarg($ruta_archivo),
            $output, $retval);
        unlink($ruta_temp);

<?php

namespace NovinTarhPars;
use Dotenv\Dotenv;

class AsteriskAmi
{
    public $username;
    public $password;
    public $server;
    public $port;
    public $timeout = 1;

    public function __construct()
    {
        (Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : __DIR__."/../"))->load();
        $this->server = $_ENV['ASTERISK_SERVER_DOMAIN'];
        $this->port = $_ENV['ASTERISK_SERVER_AMI_PORT'];
        $this->username = $_ENV['ASTERISK_AMI_USER'];
        $this->password = $_ENV['ASTERISK_AMI_PASSWORD'];
        
    }
    public function login(){
        $socket = fsockopen($this->server,$this->port, $errno, $errstr, $this->timeout);
        fputs($socket, "Action: Login\r\n");
        fputs($socket, "UserName: $this->username\r\n");
        fputs($socket, "Secret: $this->password\r\n\r\n");
        fputs($socket, "Events: off\r\n\r\n");
        return $socket;
    }
    public function run($from, $to, $url,$trunk)
    {
       
        $apiUrl = "https://192.168.1.160/api/sendFax.php";
        $body = [
            'url' => $url,
            'to' => $to,
            'from' => $from,
            'trunk' => $trunk
        ];

        $options = array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
                'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($body)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        
        return json_decode($result);
    }
    public function send_fax(int $ext, int $num, string $context, array $variables = []){
        $socket = $this->login();
        fputs($socket, "Action: Originate\r\n" );
        fputs($socket, "Channel: SIP/$ext\r\n" );
        fputs($socket, "Exten: $num\r\n" );
        fputs($socket, "Context: $context\r\n" );
        fputs($socket, "Priority: 1\r\n" );
        fputs($socket, "Async: yes\r\n\r\n" );
        fputs($socket, "Action: Logoff\r\n\r\n");
        return fgets($socket,4096);
        // return $wrets;
    }
    public function originate(int $ext, int $num, string $context, array $variables = []){
        $socket = $this->login();
        fputs($socket, "Action: Originate\r\n" );
        fputs($socket, "Channel: SIP/$ext\r\n" );
        fputs($socket, "Exten: $num\r\n" );
        fputs($socket, "Context: $context\r\n" );
        fputs($socket, "Priority: 1\r\n" );
        fputs($socket, "Async: yes\r\n\r\n" );
        fputs($socket, "Action: Logoff\r\n\r\n");
        return fgets($socket,4096);
        // return $wrets;
    }
    public function uploadFax($url){       
        $apiUrl = "https://192.168.1.160/api/upload.php?url=$url";
        $options = array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
                'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);
        return json_decode($result,true);
    }
}
        
        
        
        
        
        <?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function checkAndCreatedPath($path){
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}
function sendFax($from,$to,$data,$trunk){
	$host = $faxHost = "$from@127.0.0.1";
	$output = $retval = NULL;
	exec("sendfax -D -h $host -n -d $to $data 2>&1", $output, $retval);
	$regs = NULL;
	if ($retval != 0 || !preg_match('/request id is (\d+)/', implode('', $output), $regs)) {
		$errMsg = implode('<br/>', $output);
		return NULL;
	}
	return $regs[1];
}
//set variables
$url = isset($_POST['url']) ? trim($_POST['url']) : "";
$to = isset($_POST['to']) ? trim($_POST['to']) : "";
$from = isset($_POST['from']) ? trim($_POST['from']) : "";
$trunk = isset($_POST['trunk']) ? trim($_POST['trunk']) : "";


if($url == "" || $to = "" || $from = "" || $trunk = ""){
    echo json_encode([
		'status' => false ,
		'massage' => "please send \n
			1. url => pdf,txt,tiff,png url \n
			2. to => fax extension  - string \n
			3. from => fax recieve - string \n
			4. trunk => fax trunk - string \n
		"
	]); 
    die;
}

$output=null;
$retval=null;
$url = isset($_POST['url']) ? trim($_POST['url']) : "";
if($url == ""){
    echo json_encode(['status' => false ,'massage' => 'please send {url => pdf,txt,tiff,png url}']); 
    die;
}
$extFile = explode('.',basename($url))[1];
if(!in_array($extFile,['pdf','txt','tiff','png'])) {
    echo json_encode(['status' => false ,'massage' => 'file extention incorrect. please send pdf,txt,tiff,png file url']); 
    die;
}
$string = generateRandomString(20);
$filename = $string.".".$extFile;
$file = file_put_contents($filename,file_get_contents($url));
$path = "/var/www/html/api/fax-files/";
$filepath = checkAndCreatedPath($path);
$resultFileName = "$path.$string.tiff";
$command = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=$resultFileName $filename";
exec($command, $output, $retval);
unlink($filename);
// http_response_code(201);
// echo json_encode(['status' => true ,'massage' => 'File Was Created' , 'data' => $resultFileName]);
$data = file_get_contents($resultFileName);
$result = sendFax($from,$to,$data,$trunk);

http_response_code(201);
echo (json_encode(['status' => true ,'massage' => 'File Was Created' , 'data' => $result]));
die;
