<?php
/* 
this function is used to add word frequency data from a list of links
to a database.
*/
function import($link, $database)
{
    $string = "";
    $data = mb_convert_encoding(
        file_get_contents($link),
        "HTML-ENTITIES",
        "ASCII"
    );

    $non_alpha_numeric = ",.?!';-=+#()\\\"";
    // Grab only the body of the HTML

    // parse through the data, this currently takes all information from <p></p>
    $file = find($data, "<td class='core' id='match_qtip_48186253_' style='padding:0'>", "</td>", "array");
    
    $num = count($file);

    for ($i = 0; $i < $num; $i++)
    {
        $names = find($file[$i], "<span", "</span>", "array");
        $top_name = $names[0];
        $bottom_name = $names[1];
        $top_score = find($file[$i], "top_score", "</div>", "string");
        $bottom_score = find($file[$i], "bottom_score", "</div>", "string");
        $file[$i] = array($top_name, $top_score, $bottom_name, $bottom_score);
    }

    file_put_contents($database, json_encode(root_word($word_frequency)));
    print("success");
}

/* 
This function hard-codes out common words which I do not wish to appear as key words
This is only necessary because our database uses professionally written articles.
These articles have low percentages of some otherwise commonly used words such as "our".
Thus if the user is on an article written at a more amateur level, our algorithm would otherwise
bring these words to the forefront of keywords.
*/
function hard_code_yahoo($array)
{

    $array = array_flip($array);
    $common_words = array("guidelines","report","abuse","thumbs","up","down","submit","question","answer","cancel","terms","details","upload","photo","video","smaller","larger","hours","ago","comment","just","violates");
    $num = count($common_words);
    for($i = 0; $i < $num; $i++)
    {
        if (array_key_exists($common_words[$i],$array))
        {
            unset($array["$common_words[$i]"]);
        }
    }
    $array = array_flip($array);
    $array = array_values($array);
    return $array;
}
function hard_code($array)
{
    $array = array_flip($array);
    $common_words = array("only","should","mr","ms","mrs","such","also","our","most","very","say","both","the","of","and","a","to","in","is","you","that","it","he","was","for","on","are","as","with","his","they","I","at","be","this","have","from","or","one","had","by","word","but","not","what","all","were","we","when","your","can","said","there","use","an","each","which","she","do","how","their","if","will","up","other","about","out","many","then","them","these","so","some","her","would","make","like","him","into","time","has","look","two","more","write","go","see","number","no","way","could","people","my","than","first","water","been","call","who","oil","its","now","find","long","down","day","did","get","come","made","may","part", "i");
    $num = count($common_words);
    for($i = 0; $i < $num; $i++)
    {
        if (array_key_exists($common_words[$i],$array))
        {
            unset($array["$common_words[$i]"]);
        }
    }
    $array = array_flip($array);
    $array = array_values($array);
    return $array;
}

/*
This is basically built as a catch-safe, it deletes any blank values
that may have entered an array by mistake. 
*/
function delete_blanks($array)
{
    $num = count($array);
    $array = array_values($array);
    for($i = 0; $i < $num; $i++)
    {
        if ($array[$i] == "")
        {
            unset($array[$i]);
        }
    }
    return array_values($array);
}

/*
Depending on the input, this function either simply trims words from 
an array which are variations on another word in the array, or 
it takes an associative array and repeats the process with the keys
but adds the values to the root word of those it deletes.
*/
function root_word($array, $type)
{
    $trimmings = array("s","es","ed","ing");
    if ($type == "words")
    {
        $array = array_flip($array);
    }
    foreach ($array as $key => $value)
    {
        for($i = 0; $i < count($trimmings); $i++)
        {
            if (!(strstr($key, "$trimmings[$i]") === false))
            {
                if(($root = rtrim($key, $trimmings[$i])) !== $key)
                {
                    if(strlen($root) !== 0)
                    {
                        $root_2 = rtrim($root, $root[strlen($root) - 1]);
                        $root_3 = $root."e";
                        if (isset($array["$root"]))
                        {
                            if ($type == "stats")
                            {
                                $array["$root"] += $value;
                            }
                            unset($array["$key"]);
                        }   
                        elseif(isset($array["$root_2"]))
                        {
                            if ($type == "stats")
                            {
                                $array["$root_2"] += $value;
                            }
                            unset($array["$key"]);
                        }
                        elseif(isset($array["$root_3"]))
                        {
                            if ($type == "stats")
                            {
                                $array["$root_3"] += $value;
                            }
                            unset($array["$key"]);
                        }
                    }
                }

            }
        }
    }
    if ($type == "stats")
    {
        return $array;
    }
    if ($type == "words")
    {
        return array_values(array_flip($array));
    }
}

/*
This function finds the index of the smallest value in the array passed in
and returns that value, if there are multiple values that are the same and 
the lowest, it will return the first such index. 
*/
function index_min($array)
{
    //finds minimum of array and returns which element of the array it is
    $num = count($array);
    for ($j = 0; $j < $num; $j++)
    {
        if ($j == ($num - 1))
        {
            return $j;
        }
        $counter = 0;
        for ($i = $j + 1; $i < $num; $i++)
        {
            if($array[$j] <= $array[$i])
            {
                $counter++;
            }
        }
        if($counter == ($num - ($j + 1)))
        {
            return $j;
        }
    }
}
/*
This function will remove from an array any instances of the characters
entered in $string. 
*/
function removechar($array, $string)
{
    //removes any number of characters from an array and returns it.
    for ($i = 0; $i < strlen($string); $i++)
    {
        $array = str_replace($string[$i], "", $array);
    }
    return $array;
}
/*
This function removes periods from initials and surnames,
it is necessary for our phrase algorithm.
*/
function removeperiods($string)
{
    $surnames = array("Mr.", "Ms.", "Mrs.", "Mz.","a.m.","p.m.","U.S.","U.S.A");
    $surnames_2 = array("Mr", "Ms", "Mrs", "Mz","a,m","p,m","U,S","U,S,A");
    for ($i = 0; $i < count($surnames); $i++)
    {
        $string = str_replace($surnames[$i], $surnames_2[$i], $string);
    }
    return $string;
}
/*
Reverses the previous function.
*/
function addperiods($string)
{
    $surnames_2 = array("Mr.", "Ms.", "Mrs.", "Mz.","a.m.","p.m.","U.S.","U.S.A");
    $surnames = array("Mr", "Ms", "Mrs", "Mz","a,m","p,m","U,S","U,S,A");
    for ($i = 0; $i < count($surnames); $i++)
    {
        $string = str_replace($surnames[$i], $surnames_2[$i], $string);
    }
    return $string;
}

/*
This function parses through a string, returning the substring between certain tags.
*/
function find($data, $par_1, $par_2, $type)
{
    if ($type == "string")
    {
        $offset = stripos($data, "$par_1");
        if ($offset === false)
        {
            return false;
        }
        $offset += strlen("$par_1");
        $data = substr($data, $offset);
        $offset = stripos($data, ">");
        $offset += strlen(">");
        $data = substr($data, $offset);
        $data = strstr($data, "$par_2", true);
        return $data;
    }
    if ($type == "array")
    {
        $file = [];
        while (!(($offset = stripos($data, $par_1))  === false))
        {
            $offset += strlen("$par_1");
            $data = substr($data, $offset);
            $offset = stripos($data, ">");
            $offset += strlen(">");
            $data = substr($data, $offset);
            $temp = strstr($data, "$par_2", true);
            array_push($file, $temp);
        }
        if (count($file) == 0)
        {
            return false;
        }
        else
        {
            return $file;
        }
    }
}

/*
Simply strips tags from all elements of an array
*/
function strip_all_tags($file)
{
    $num = count($file);
    for ($i = 0; $i < $num; $i++)
    {
        $file[$i] = strip_tags($file[$i]);
    }
    return $file;
}
/*
This function deletes anything between two enetered tags .
This is done because source code will often have javascript 
still in tact and our parser would read it as text if it were not deleted.
This function leaves the tags in, they must be removed manually after.
*/
function delete_tags($string, $par_1, $par_2)
{
    $result = $string;
    $scripts = find($string, $par_1, $par_2, "array");
    $num = count($scripts);
    for($i = 0; $i < $num; $i++)
    {
        $result = str_replace($scripts[$i], "", $result);
    }
    return $result;
    
}
/*
Finally our main function, our crowning achievement--the parser.
This parser contains algorithms to determine key words and key phrases
as well as background code to determine statistics
*/
function htmlparser($data)
{
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
    // search for title by looking for <title>
    $title = find($data, "<title", "</title>", "string");

    if ($title === false)
    {
       $title = find($data, "<h1", "</h1>", "string");
       $title = strip_tags($title);
    }
    if ($title === false)
    {
        $title = "No Title Available";
    }
    
    /*
    This code was written in case headers were important, currently we have
    decided to pull this code from the program, but it is reasonable to leave 
    in in case we or someone else wishes to include it later
    
    $headers_2 = find($body, "<h2", "</h2>", "array");
    $headers_2 = strip_all_tags($headers_2);
    $headers_3 = find($body, "<h3", "</h3>", "array");
    $headers_3 = strip_all_tags($headers_3);
    $headers_4 = find($body, "<h4", "</h4>", "array");
    $headers_4 = strip_all_tags($headers_4);
    $headers_5 = find($body, "<h5", "</h5>", "array");
    $headers_5 = strip_all_tags($headers_5);
    array_push($headers, $headers_2);
    array_push($headers, $headers_3);
    array_push($headers, $headers_4);
    array_push($headers, $headers_5);
    */
    
    // create length statistics for paragraphs
    $num = count($file);
    for ($i = 0; $i < $num; $i++)
    {
        array_push($stats, strlen($file[$i]));
        $total_length += strlen($file[$i]);
    }

    if ($total_length - .05*strlen(strip_tags($body)) < 0)
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
    
    //safe-catch for common or non alphanumeric words or those of length 1 which made it past the algorithm
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
    
    $all_key_words = $key_words;
    for ($i = 0; $i < count($key_words); $i++)
    {
        array_push($keyword_stats, $word_frequency["$key_words[$i]"]);
    }
    
    // cut out all but KEYCOUNT of these words
    $num = count($keyword_stats);

    $KEYCOUNT = (int) (log($key_count, $base) + $num_words/2750);
    for ($i = 0; $i < ($num - $KEYCOUNT); $i++)
    {
        $min = index_min($keyword_stats);
        unset($keyword_stats[$min]);
        unset($key_words[$min]);
        $keyword_stats = array_values($keyword_stats);
        $key_words = array_values($key_words);
    }
    
    // KP Algorithm 1: Returns PHRASECOUNT phrases with the highest percentage of keywords
    $key_phrases = explode(".”", $article);
    for ($i = 0; $i < count($key_phrases); $i++)
    {
        $temp = $key_phrases[$i];
        $count = substr_count($temp, "“");
        while($count > 0)
        {
            $temp = strstr($temp, "“");
            $temp_2 = strstr($temp,"”", true);
            if ($temp_2 === false)
            {
                $temp_2 = $temp;
            }
            $temp_3 = str_replace(".", "temporaryperiod", $temp_2);
            $key_phrases[$i] = str_replace($temp_2, $temp_3, $key_phrases[$i]);
            $offset = strlen($temp_3);
            $temp = substr($temp, $offset + strlen("”"));
            $count--;
        }
    }
    $temp = [];
    for ($i = 0; $i < count($key_phrases); $i++)
    {
        $key_phrases[$i] = preg_split("/[.!?]/", $key_phrases[$i]);
        for($j = 0; $j < count($key_phrases[$i]); $j++)
        {
            if ($j == count($key_phrases[$i]) - 1)
            {
                array_push($temp, $key_phrases[$i][$j]."”");
            }
            else
            {
                array_push($temp, $key_phrases[$i][$j]);
            }
        }
    }
    $key_phrases = $temp;
    for($i = 0; $i < count($key_phrases); $i++)
    {
        $key_phrases[$i] = str_replace("temporaryperiod", ".", $key_phrases[$i]);
    }
    $key_phrases = array_unique($key_phrases);
    $key_phrases = delete_blanks($key_phrases);
    $key_phrases = array_values($key_phrases);
    
    //yahoo clause: This is necessary when we skip the add-blocking steps.
    if ($type == "body")
    {
        $test = array("I think this question violates the Terms of Service","Photo should be smaller than",
        "Video should be smaller than","You can only upload","Report Abuse");
        
        for($i = 0; $i < count($key_phrases); $i++)
        {
            for($j = 0; $j < count($test); $j++)
            {
                if (!(strstr($key_phrases[$i], $test[$j]) === false))
                {
                    unset($key_phrases[$i]);
                    break;
                }
            }
        }
        $key_phrases = array_values($key_phrases);
    }
    
    for($k = 0; $k < count($key_phrases); $k++)
    {
        $split_key_phrases[$k] = removechar($key_phrases[$k], $non_alpha_numeric);
        $split_key_phrases[$k] = preg_split("/[\s]+/", $split_key_phrases[$k]);
    }

    // counts key words in each phrase, using frequency definition of key words
    for($i = 0; $i < count($split_key_phrases); $i++)
    {
        $keyphrase_stats[$i] = 0;
        for($j = 0; $j < count($split_key_phrases[$i]); $j++)
        {
            $key_words_keys = array_flip($all_key_words);
            $input = (string) $split_key_phrases[$i][$j];
            if (isset($key_words_keys["$input"]))
            {
                
                //note that this is now weighted by the use of the keyword, not all keywords come equal.
                $keyphrase_stats[$i] += sqrt($word_frequency["$input"]/strlen($key_phrases[$i]));
            }
        }
        if($keyphrase_stats[$i] == 0)
        {
            unset($key_phrases[$i]);
            unset($keyphrase_stats[$i]);
        }
    }
    $keyphrase_stats = array_values($keyphrase_stats);
    $key_phrases = array_unique($key_phrases);
    $key_phrases = array_values($key_phrases);

    $num = count($keyphrase_stats);
    // algorithm for number of phrases depending on the size of the article.
    $PHRASE_CONST = 100 + 25*((int) ($num_words/500));
    
    if($PHRASE_CONST > $num_words)
    {
        $PHRASECOUNT = 1;
    }
    else
    {
        $PHRASECOUNT = $num_words/$PHRASE_CONST;
    }
    for ($i = 0; $i < ($num - $PHRASECOUNT); $i++)
    {
        $min = index_min($keyphrase_stats);
        unset($keyphrase_stats[$min]);
        unset($key_phrases[$min]);
        $keyphrase_stats = array_values($keyphrase_stats);
        $key_phrases = array_values($key_phrases);
    }
    for ($i = 0; $i < count($key_phrases); $i++)
    {
        while(!(strstr($key_phrases[$i], "[") === false))
        {
            $temp = strstr($key_phrases[$i],"[");
            $temp = strstr($temp, "]", true);
            $temp = $temp."]";
            $key_phrases[$i] = str_replace($temp, "", $key_phrases[$i]);
        }
    }
    // catch-safe, delete any empty or repeated key words or phrases
    $key_words = delete_blanks($key_words);
    $key_words = array_unique($key_words);
    $key_phrases = delete_blanks($key_phrases);
    $key_phrases = array_unique($key_phrases);
    
    for ($i = 0; $i < count($key_phrases); $i++)
    {
        if (strstr($key_phrases[$i], "Advertisement") !== false)
        {
            $key_phrases[$i] = str_replace("Advertisement", "", $key_phrases[$i]);
        }
    }
    
    // capitalize any proper nouns, names, or acronyms.
    for ($i = 0; $i < count($key_words); $i++)
    {
        for($j = 0; $j < strlen($key_words[$i]); $j++)
        {
            if (!isset($names["$key_words[$i]"]))
            {
                $key_words[$i][$j] = strtoupper($key_words[$i][$j]);
            }
        }
    }
    
    
    // Create the html outputs for Key words, phrases, title, and summary
    
    /*
    $headers_html = "<div align=right> <h4>Topics or Headers:</h4><ul>";
    for($i = 0; $i < count($key_words); $i++)
    {
        for($j = 0; $j < count($headers[$i]); $j++)
        {
            $headers_html = $headers_html."<li>".htmlentities($headers[$i][$j])."</li>";
        }
    }
    $headers_html = $headers_html."</ul></div>";
    */
    
    $key_words_html="<div> <h4>Key Words:</h4> <ul>";
    for($i = 0; $i < count($key_words); $i++)
    {
        $key_words_html = $key_words_html."<li>".htmlentities($key_words[$i])."</li>";
    }
    $key_words_html = $key_words_html."</ul></div>";
    
    $key_phrases_html="<div align=center> <h2> Summary: </h2> <p>";
    for($i = 0; $i < count($key_phrases); $i++)
    {
        $key_phrases_html = $key_phrases_html." ".htmlentities($key_phrases[$i]).".";
    }
    $key_phrases_html = $key_phrases_html."</p></div>";
    $title = "<div align=center><h1>".htmlentities($title)."</h1></div>";
    if ($type == "body")
    {
        return $title."<div align=center> <h5> (Summary for this site is in Beta testing) </h5> </div>"."splithere".$key_words_html."splithere".addperiods($key_phrases_html);
    }
    return $title."splithere".$key_words_html."splithere".addperiods($key_phrases_html);
}
?>