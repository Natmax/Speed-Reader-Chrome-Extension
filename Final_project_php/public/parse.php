<?php
require("../includes/config.php");

// Grab the html file
//from http://stackoverflow.com/questions/19762612/php-file-get-contents-returning-enable-cookies
$_curl = curl_init();
curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($_curl, CURLOPT_COOKIEFILE, './cookiePath.txt');
curl_setopt($_curl, CURLOPT_COOKIEJAR, './cookiePath.txt');
curl_setopt($_curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; InfoPath.1)');
curl_setopt($_curl, CURLOPT_FOLLOWLOCATION, true); //new added
curl_setopt($_curl, CURLOPT_URL, $_GET["url"]);
$html = curl_exec( $_curl );
$html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
$html = html_entity_decode($html);
// Print the parsed html file
print(htmlparser($html));
?>