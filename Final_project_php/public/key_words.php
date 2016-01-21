<?php
//NOTE: THIS ENTIRE FILE IS SIMPLY A COPIED PART OF HTMLPARSER AND PARSE
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
$data = html_entity_decode($html);

// upfront decleration of variables and constants
$title = "";
$article = "";
$summary = "";
$key_words = [];
$key_phrases = [];
$headers = [];
$key_words_html = "";
$key_phrases_html = "";
$split_key_phrases = [];
$non_alpha_numeric = ",.?!';-=+#()*/";
$file = [];
$string = "";
$sarray = [];
$stats = [];
$keyword_stats = [];
$keyphrase_stats = [];
$database = [];
$word_frequency = [];
$keyword_frequency = [];
$type = "";
$BLOCK = 4;
$AD_BLOCK = .05;
$AD_BLOCK2 = .01;
$FREQ_DIF = 0;
$FREQ_CONSTANT = 50;
$BODY_COUNT = 10;
$SENT_LEN = 10;
$base = 4;
$base_2 = 6;

// databse variables
$PHRASE_CONST = 150;
$total_length = 0;
$NUM_LINKS = 49;
$DATABASE_SIZE = 206102;
$json_1 = file_get_contents("http://ide50-nhopkins.cs50.io/database.json");
$json_2 = file_get_contents("http://ide50-nhopkins.cs50.io/database.1.json");
$json_3 = file_get_contents("http://ide50-nhopkins.cs50.io/database.2.json");
$json_4 = file_get_contents("http://ide50-nhopkins.cs50.io/database.3.json");
$json_5 = file_get_contents("http://ide50-nhopkins.cs50.io/database.4.json");
$database_1 = json_decode($json_1, true);
$database_2 = json_decode($json_2, true);
$database_3 = json_decode($json_3, true);
$database_4 = json_decode($json_3, true);
$database_5 = json_decode($json_3, true);

// Grab only the body of the HTML
$body = find($data, "<body", "</body>", "string");
// strip body of scripts
$body = delete_tags($body, "<script", "</script>");

// parse through the data, this currently takes all information from <p></p>
$file = find($body, "<p", "</p>", "array");
$file = strip_all_tags($file);

// create length statistics for paragraphs
$num = count($file);
for ($i = 0; $i < $num; $i++)
{
    array_push($stats, strlen($file[$i]));
    $total_length += strlen($file[$i]);
}

if ($total_length < .05*strlen(strip_tags($body)))
{
    $type = "body";
    $string = delete_tags($body, "<p", "</p>");
    $string = delete_tags($string, "<style", "</style>");
    $string = strip_tags($string);
    $string = str_replace("Answers", "", $string);
    $string = str_replace("Relevance", "", $string);
    $string = str_replace("Rating", "", $string);
    $string = str_replace("Newest", "", $string);
    $string = str_replace("Oldest", "", $string);
    
}
else
{
    $type = "paragraph";
    // ADBLOCK Algorithm 1: removes the smallest n% blocks.
    for ($i = 0; $i < ($AD_BLOCK * $num);$i++)
    {
        $min = index_min($stats);
        unset($stats[$min]);
        unset($file[$min]);
        $stats = array_values($stats);
        $file = array_values($file);
    }
    $num = count($file);

    // ADBLOCK Algorithm 2: removes groups of small blocks
    for ($i = 0; $i < $num; $i++)
    {
        $counter = 0;
        while ($stats[$i] < $num/(5*$total_length))
        {
            if ($i == $num - 1)
            {
                break;
            }
            $i++;
            $counter++;
        }
        if ($counter > $BLOCK)
        {
            while ($counter >= 0)
            {
                unset($stats[$i - $counter]);
                unset($file[$i - $counter]);
                $counter--;
            }
        }
    }
    $stats = array_values($stats);
    $file = array_values($file);
    $num = count($file);

    for ($i = 0; $i < $num; $i++)
    {
        $string = $string." ".$file[$i];
    }
}

// format our string into words seperated by spaces
$article = $string;
$article = removeperiods($article);
$string = removechar($string, $non_alpha_numeric);
$names = array_flip(preg_split("/[\s]+/", $string));
$string = strtolower($string);
// split the string into an array with each word
$sarray = preg_split("/[\s]+/", $string);

// catch-safe in case regex fails
// $sarray = delete_blanks($sarray);
$num_words = count($sarray);

for ($i = 0; $i < $num_words; $i++)
{
    if (isset($word_frequency["$sarray[$i]"]))
    {
        $word_frequency["$sarray[$i]"] += 1/($num_words);
    }
    else
    {
        $word_frequency["$sarray[$i]"] = 1/($num_words);
    }
}
$word_frequency = root_word($word_frequency, "stats");
// KW Algorthim 1: returns KEYCOUNT or fewer key words
$key_words = array_unique($sarray);
$key_words = root_word($key_words, "words");

// create personal database for article and calculate scaling factor
$key_count = count($key_words);

$scalar = 0;

for ($i = 0; $i < $key_count; $i++)
{
    if (isset($database_1["$key_words[$i]"]))
    {
        $database["$key_words[$i]"] = $database_1["$key_words[$i]"];
    }
    else
    {
        $database["$key_words[$i]"] = 0;
    }
    if (isset($database_2["$key_words[$i]"]))
    {
        $database["$key_words[$i]"] += $database_2["$key_words[$i]"];
    }
    else
    {
        $database["$key_words[$i]"] += 0;
    }
    if (isset($database_3["$key_words[$i]"]))
    {
        $database["$key_words[$i]"] += $database_3["$key_words[$i]"];
    }
    else
    {
        $database["$key_words[$i]"] += 0;
    }
    if (isset($database_4["$key_words[$i]"]))
    {
        $database["$key_words[$i]"] += $database_4["$key_words[$i]"];
    }
    else
    {
        $database["$key_words[$i]"] += 0;
    }
    if (isset($database_5["$key_words[$i]"]))
    {
        $database["$key_words[$i]"] += $database_5["$key_words[$i]"];
    }
    else
    {
        $database["$key_words[$i]"] += 0;
    }
    $scalar += $database["$key_words[$i]"];
}
if ($scalar == 0)
{
    return "<div align=center> <h1> We're Sorry: This site has nothing to be summarized </h1> </div>";
}

// Picks keywords by frequency they differ from database
for ($i = 0; $i < $key_count; $i++)
{
    if (($word_frequency["$key_words[$i]"] - ($database["$key_words[$i]"]/$scalar)) 
    < log($num_words, $base_2)/$num_words)
    {
        unset($key_words[$i]);
    }
}

$key_words = array_values($key_words);

//safe-catch for common or non alphanumeric words which made it past the algorithm
$key_words = hard_code($key_words);
if ($type == "body")
{
    $key_words = hard_code_yahoo($key_words);
}
$key_words = array_values($key_words);

$num = count($key_words);
for ($i = 0; $i < $num; $i++)
{
    if (!ctype_alnum($key_words[$i]))
    {
        unset($key_words[$i]);
    }
}
$key_words = array_values($key_words);

$num = count($key_words);
for ($i = 0; $i < $num; $i++)
{
    if (strlen($key_words[$i]) == 1)
    {
        unset($key_words[$i]);
    }
}

$key_words = array_values($key_words);

$key_words_string = null;
for ($i = 0; $i < count($key_words); $i++)
{
    $key_words_string = $key_words_string." ".htmlentities($key_words[$i]);
}
print($key_words_string);
?>