$query = "gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -sPAPERSIZE=letter -sOutputFile=fax-files/dest.tiff /var/www/html/test.pdf";
$res = exec($query, $output, $retval);
