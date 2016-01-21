/*
 * Finds and highlights a list of key-words in the specified webpage.
 */

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

function stripos(f_haystack, f_needle, f_offset) {
  //  discuss at: http://phpjs.org/functions/stripos/
  // original by: Martijn Wieringa
  //  revised by: Onno Marsman
  //   example 1: stripos('ABC', 'a');
  //   returns 1: 0

  var haystack = (f_haystack + '')
    .toLowerCase();
  var needle = (f_needle + '')
    .toLowerCase();
  var index = 0;

  if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
    return index;
  }
  return false;
}

function substr(str, start, len) {
  //  discuss at: http://phpjs.org/functions/substr/
  //     version: 909.322
  // original by: Martijn Wieringa
  // bugfixed by: T.Wild
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Theriault
  //        note: Handles rare Unicode characters if 'unicode.semantics' ini (PHP6) is set to 'on'
  //   example 1: substr('abcdef', 0, -1);
  //   returns 1: 'abcde'
  //   example 2: substr(2, 0, -6);
  //   returns 2: false
  //   example 3: ini_set('unicode.semantics',  'on');
  //   example 3: substr('a\uD801\uDC00', 0, -1);
  //   returns 3: 'a'
  //   example 4: ini_set('unicode.semantics',  'on');
  //   example 4: substr('a\uD801\uDC00', 0, 2);
  //   returns 4: 'a\uD801\uDC00'
  //   example 5: ini_set('unicode.semantics',  'on');
  //   example 5: substr('a\uD801\uDC00', -1, 1);
  //   returns 5: '\uD801\uDC00'
  //   example 6: ini_set('unicode.semantics',  'on');
  //   example 6: substr('a\uD801\uDC00z\uD801\uDC00', -3, 2);
  //   returns 6: '\uD801\uDC00z'
  //   example 7: ini_set('unicode.semantics',  'on');
  //   example 7: substr('a\uD801\uDC00z\uD801\uDC00', -3, -1)
  //   returns 7: '\uD801\uDC00z'

  var i = 0,
    allBMP = true,
    es = 0,
    el = 0,
    se = 0,
    ret = '';
  str += '';
  var end = str.length;

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  this.php_js.ini = this.php_js.ini || {};
  // END REDUNDANT
  switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
    case 'on':
      // Full-blown Unicode including non-Basic-Multilingual-Plane characters
      // strlen()
      for (i = 0; i < str.length; i++) {
        if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
          allBMP = false;
          break;
        }
      }

      if (!allBMP) {
        if (start < 0) {
          for (i = end - 1, es = (start += end); i >= es; i--) {
            if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
              start--;
              es--;
            }
          }
        } else {
          var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
          while ((surrogatePairs.exec(str)) != null) {
            var li = surrogatePairs.lastIndex;
            if (li - 2 < start) {
              start++;
            } else {
              break;
            }
          }
        }

        if (start >= end || start < 0) {
          return false;
        }
        if (len < 0) {
          for (i = end - 1, el = (end += len); i >= el; i--) {
            if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
              end--;
              el--;
            }
          }
          if (start > end) {
            return false;
          }
          return str.slice(start, end);
        } else {
          se = start + len;
          for (i = start; i < se; i++) {
            ret += str.charAt(i);
            if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
              se++; // Go one further, since one of the "characters" is part of a surrogate pair
            }
          }
          return ret;
        }
        break;
      }
      // Fall-through
    case 'off':
      // assumes there are no non-BMP characters;
      //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
    default:
      if (start < 0) {
        start += end;
      }
      end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
      // PHP returns false if start does not fall within the string.
      // PHP returns false if the calculated end comes before the calculated start.
      // PHP returns an empty string if start and end are the same.
      // Otherwise, PHP returns the portion of the string from start to end.
      return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
  }
  return undefined; // Please Netbeans
}

// Debugged and editted function from http://stackoverflow.com/questions/20795142/highlight-all-words-in-chrome-extension
// @author Rob W <http://stackoverflow.com/users/938089/rob-w>
// Demo: var serialized_html = DOMtoString(document);

// Takes the DOM and converts it to a string in order to manipulate it.
function DOMtoString(document_root) {
    var html = '',
        node = document_root.firstChild;
    while (node) {
        switch (node.nodeType) {
        case Node.ELEMENT_NODE:
            html += node.outerHTML;
            break;
        case Node.TEXT_NODE:
            html += node.nodeValue;
            break;
        case Node.CDATA_SECTION_NODE:
            html += '<![CDATA[' + node.nodeValue + ']]>';
            break;
        case Node.COMMENT_NODE:
            html += '<!--' + node.nodeValue + '-->';
            break;
        case Node.DOCUMENT_TYPE_NODE:
            // (X)HTML documents are identified by public identifiers
            html += "<!DOCTYPE " + node.name + (node.publicId ? ' PUBLIC "' + node.publicId + '"' : '') + (!node.publicId && node.systemId ? ' SYSTEM' : '') + (node.systemId ? ' "' + node.systemId + '"' : '') + '>\n';
            break;
        }
        node = node.nextSibling;
    }
    return html;
}

/*
This function parses through a string, returning the substring between certain tags.
*/
function find(data, par_1, par_2, type)
{
    var offset = 0;
    if (type == "string")
    {
        offset = stripos(data, par_1);
        if (offset === false)
        {
            return false;
        }
        offset += par_1.length;
        data = substr(data, offset);
        offset = stripos(data, ">");
        offset += ">".length;
        data = substr(data, offset);
        data = strstr(data, par_2, true);
        return data;
    }
    if (type == "array")
    {
        var file = [];
        while (!((offset = stripos(data, par_1))  === false))
        {
            offset += par_1.length;
            data = substr(data, offset);
            offset = stripos(data, ">");
            offset += ">".length;
            data = substr(data, offset);
            var temp = strstr(data, par_2, true);
            file.push(temp);
        }
        if (file.length == 0)
        {
            return false;
        }
        else
        {
            return file;
        }
    }
}

// the portion of the script that actually runs the highlighting
chrome.runtime.onMessage.addListener(
  function(request, sender, sendResponse) {
        if (request.greeting == "hello")
        {
          // performs an ajax request to return the necessary key-word data
          var url = document.location.href;
          var parameters = "https://ide50-nhopkins.cs50.io/key_words.php?url=" + url;
          var xhr = new XMLHttpRequest();
          xhr.open("GET", parameters, true);
          xhr.onreadystatechange = function() 
          {
              if (xhr.readyState == 4) 
              {
          
                  // searches for paragraph tags in the DOM and sets the 
                  // necessary variables to manipulate the data between the tags
                  var str = xhr.responseText;
                  var key_words = str.split(" ");
                  var text = DOMtoString(document.body);
                  var file = find(text, "<p", "</p>", "array");
                  var file_replaced = file;
                  var number_key_words = key_words.length;
                  var number_paragraphs = file.length;
                  
                  // loops through paragraphs and creates an array of edited paragraphs
                  // with mark tags around key words
                  for (var i = 0; i < number_paragraphs; i++)
                  {
                      for (var j = 1; j < number_key_words; j++)
                      {
                        var re = new RegExp(" " + key_words[j] + " ", 'gi');
                        file_replaced[i] = file_replaced[i].replace(re, " <mark>" + key_words[j] + "</mark> ");
                      }
                  }
                  
                  // replaces paragraph data
                  file = find(text, "<p", "</p>", "array");
                  for (var i = 0; i < number_paragraphs; i++)
                  {
                    text = text.replace(file[i], file_replaced[i]);
                  }
                  
                  // sets html body to altered paragraphs
                  document.body.innerHTML = text;
              }
          };
          xhr.send();
        }
});