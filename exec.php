$query = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=fax-files/dest.tiff /var/www/html/test.pdf";
$res = exec($query, $output, $retval);

require_once './vendor/autoload.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $output=null;
    $retval=null;
    $url = $_GET['url'];
    $extFile = explode('.',basename($url))[1];
    $string = generateRandomString(20);
    $filename = $string.".".$extFile;
    $file = file_put_contents($filename,file_get_contents($url));
    $path = "fax-files/";
    $filepath = checkAndCreatedPath($path);
    $command = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=$path/$string.tiff $filename";
    exec($command, $output, $retval);
    unlink($filename);
    http_response_code(201);
    echo json_encode(['status' => true ,'massage' => 'File Was Created']);
    die;
}else{
    http_response_code(405);
    echo json_encode(['status' => false , 'massage' => 'Method Get Not Allowed']); 
    die;
}


if(!file_exists('checkAndCreatedPath')){
    function checkAndCreatedPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
}
