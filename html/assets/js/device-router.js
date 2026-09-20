(function(){
  "use strict";

  var BREAKPOINT=768;
  var MOBILE_DIR="/mobile/";
  var allowed={
    "index.html":1,
    "labels.html":1,
    "bags.html":1,
    "boxes.html":1,
    "about.html":1,
    "news.html":1,
    "news-detail.html":1,
    "contact.html":1
  };

  function viewportIsMobile(){
    return window.matchMedia ?
      window.matchMedia("(max-width:"+BREAKPOINT+"px)").matches :
      window.innerWidth<=BREAKPOINT;
  }

  function currentInfo(){
    var path=window.location.pathname;
    var inMobile=path.indexOf(MOBILE_DIR)!==-1;
    var clean=path.replace(/\/+$/,"");
    var file=clean.substring(clean.lastIndexOf("/")+1) || "index.html";
    if(!/\.html$/i.test(file)) file="index.html";
    if(!allowed[file]) return null;
    return {path:path,inMobile:inMobile,file:file};
  }

  function targetFor(info,toMobile){
    if(toMobile){
      var slash=info.path.lastIndexOf("/");
      var dir=info.path.substring(0,slash+1);
      return dir+"mobile/"+info.file;
    }
    return info.path.replace(MOBILE_DIR,"/");
  }

  function syncMode(){
    var info=currentInfo();
    if(!info) return;

    var mobile=viewportIsMobile();
    if(mobile && !info.inMobile){
      window.location.replace(targetFor(info,true)+window.location.search+window.location.hash);
      return;
    }
    if(!mobile && info.inMobile){
      window.location.replace(targetFor(info,false)+window.location.search+window.location.hash);
    }
  }

  syncMode();

  if(window.matchMedia){
    var mq=window.matchMedia("(max-width:"+BREAKPOINT+"px)");
    if(mq.addEventListener) mq.addEventListener("change",syncMode);
    else if(mq.addListener) mq.addListener(syncMode);
  }
})();
