/*
 * Uses chrome API to create listeners and communicate with highlight.js and 
 * inject.js so that they know when to run and do so in the correct order.
 */

// port action taken from http://stackoverflow.com/questions/18313669/message-passing-doesnt-works
/* global chrome */
var url;
chrome.runtime.onConnect.addListener(function(port) {
    chrome.tabs.query({currentWindow: true, active: true}, function(tabs)
    {
        console.log("background: received connection request from content script on port " + port);
        port.onMessage.addListener(function(msg) {
            console.log("background: received message '" + msg.action + "'");
            switch (msg.action) {
                case 'init':
                    console.log("background script received init request from content script");
                    port.postMessage({website: url, action: 'init'});
                    break;
            }
       });
    });
});

chrome.browserAction.onClicked.addListener(function(activeTab)
{
    chrome.tabs.query({currentWindow: true, active: true}, function(tabs)
    {
        url= tabs[0].url;
        chrome.tabs.sendMessage(tabs[0].id, {greeting: "hello"}, function(response) {});

        chrome.tabs.create({ url: chrome.extension.getURL("background.html")});
        
        

        
        // chrome.tabs.create({ url: chrome.extension.getURL("background.html")}, function(tab) 
        // {
        //     alert(tab.id);
        //     alert(tab.url);
        //     var selfTabId = tab.id;
        //     chrome.tabs.onUpdated.addListener(function(tabId, changeInfo, tab) 
        //     {
        //         alert("on updated");
        //         if (changeInfo.status == "complete" && tabId == selfTabId)
        //         {
        //             alert("correct tab");
        //             chrome.tabs.sendMessage(tab.id, {greeting: "yo", website: url}, function(response) 
        //             {
        //                 alert("5");
        //                 alert(chrome.runtime.lastError.message);
        //             });    
        //         }
        //     });
        // });
    });
});

// // http://stackoverflow.com/questions/18045348/create-a-tab-and-inject-content-script-in-it-gives-permission-error
// chrome.browserAction.onClicked.addListener(function() {
//         chrome.tabs.query({currentWindow: true, active: true}, function(tabs)
//         {
//             var url= tabs[0].url;
//             chrome.tabs.sendMessage(tabs[0].id, {greeting: "hello"}, function(response) {
//             });
            
//         // create a new tab with an html page that resides in extension domain:
//         chrome.tabs.create({'url': chrome.extension.getURL("background.html"), 
//                             'active': false}, function(tab){
//             var selfTabId = tab.id;
//             chrome.tabs.onUpdated.addListener(function(tabId, changeInfo, tab) {
//                 if (changeInfo.status == "complete" && tabId == selfTabId){
//                     // send the data to the page's script:
//                     var tabs = chrome.extension.getViews({type: "tab"});
//                     chrome.tabs.sendMessage(tabs[0].id, {greeting: "yo", website: url}, function(response) {
//               });
//                 }
//             });
//         });
//     });
// });