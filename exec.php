$query = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=fax-files/dest.tiff /var/www/html/test.pdf";
$res = exec($query, $output, $retval);

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
$resultFileName = "$path/$string.tiff";
$command = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=$resultFileName $filename";
exec($command, $output, $retval);
unlink($filename);
http_response_code(201);
echo json_encode(['status' => true ,'massage' => 'File Was Created' , 'data' => $resultFileName]);
die;
