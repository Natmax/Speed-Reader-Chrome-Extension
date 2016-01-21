<?php
require("../includes/config.php");
$links = file_get_contents("https://ide50-nhopkins.cs50.io/links.4.txt");
$links = explode(" ", $links);
$num = count($links);
// Grab files
// revised from http://stackoverflow.com/questions/19762612/php-file-get-contents-returning-enable-cookies
for($i=0; $i < $num; $i++)
{
    $_curl = curl_init();
    curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_COOKIEFILE, './cookiePath.txt');
    curl_setopt($_curl, CURLOPT_COOKIEJAR, './cookiePath.txt');
    curl_setopt($_curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; InfoPath.1)');
    curl_setopt($_curl, CURLOPT_FOLLOWLOCATION, true); //new added
    curl_setopt($_curl, CURLOPT_URL, $links[$i]);
    $html = curl_exec( $_curl );
    // Print to data.txt
    file_put_contents("data_1.4.txt", $html, FILE_APPEND);
}
?>