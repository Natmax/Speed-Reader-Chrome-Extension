/*
 * Gets key-word data from php algorithm and inserts it in the proper place in 
 * the html file background.html
 */

/* global chrome */
function strstr(haystack, needle, bool) {
    // Finds first occurrence of a string within another
    //
    // version: 1103.1210
    // discuss at: http://phpjs.org/functions/strstr    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: strstr(‘Kevin van Zonneveld’, ‘van’);
    // *     returns 1: ‘van Zonneveld’    // *     example 2: strstr(‘Kevin van Zonneveld’, ‘van’, true);
    // *     returns 2: ‘Kevin ‘
    // *     example 3: strstr(‘name@example.com’, ‘@’);
    // *     returns 3: ‘@example.com’
    // *     example 4: strstr(‘name@example.com’, ‘@’, true);    // *     returns 4: ‘name’
    var pos = 0;

    haystack += "";
    pos = haystack.indexOf(needle); if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}



var port_to_bg = chrome.runtime.connect();
if (!(strstr(document.location.href, "chrome-extension://") === false))
{
    alert("Loading...");
}
port_to_bg.onMessage.addListener(function(msg) {
  switch (msg.action) {
    case 'init':
    var parameters = "https://ide50-nhopkins.cs50.io/parse.php?url=" + msg.website;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", parameters, true);
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4) {
        // innerText does not let the attacker inject HTML elements.
        var text = xhr.responseText;
        var array = text.split("splithere");
        var title = array[0];
        var key_words = array[1];
        var summary = array[2];
        $(document).ready(function() {
          document.getElementById("article_title").innerHTML = title;
          document.getElementById("key_words").innerHTML = key_words;
          document.getElementById("summary").innerHTML = summary;
        });    
      
      }
    };  
    xhr.send();
    console.log("connection established with background page!");
    break;
  }
}); 

port_to_bg.postMessage({action: 'init'});


      // alert("inject" + request.greeting);
      // if (request.greeting == "yo")
      // {
      //   var url = document.location.href;
      //   alert(url);
      //   alert(request.website);
      //   var parameters = "https://ide50-aehrenberg.cs50.io/parse.php?url=" + request.website;
      //   var xhr = new XMLHttpRequest();
      //   xhr.open("GET", parameters, true);
      //   xhr.onreadystatechange = function() {
      //     if (xhr.readyState == 4) {
      //       // innerText does not let the attacker inject HTML elements.
      //       var text = xhr.responseText;
      //       var array = text.split("splithere");
      //       var title = array[0];
      //       var key_words = array[1];
      //       var summary = array[2];
      //       document.getElementById("#article_title").innerHTML = title;
      //       document.getElementById("#key_words").innerHTML = key_words;
      //       document.getElementById("#summary").innerHTML = summary;
      //       alert("done");
      //     }
      //   };
        
      //   xhr.send();
      // }
