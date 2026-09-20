(function(){
  "use strict";

  var params = new URLSearchParams(window.location.search);
  var requested = params.get("article");
  var articleId = requested === "2" ? "2" : "1";
  var assetPrefix = document.body.classList.contains("mobile-site") ? "../" : "";

  /*
   * The current static source contains two news records with the same title,
   * lead and body copy. Keep those shared values explicit here while allowing
   * each record to retain its own date, cover and previous/next navigation.
   * When distinct article copy is supplied later, it can be added here
   * without duplicating PC/Mobile page scripts.
   */
  var sharedTitle = "为什么大家都青睐合成标签？选金亚包装合成标签";
  var sharedLead = "印刷色彩高度还原，成品质感细腻高级。搭配可移胶，轻松揭除、不留残胶，不伤产品表面。使用便捷，材质柔韧耐用，防水抗撕，可适配多种产品贴标场景。";

  var articles = {
    "1": {
      date: "2026-07-24",
      cover: "news-1.jpg",
      coverAlt: "合成标签产品展示",
      prevHref: "news.html",
      prevText: "返回查看更多新闻",
      nextHref: "news-detail.html?article=2",
      nextText: "为什么大家都青睐合成标签？"
    },
    "2": {
      date: "2026-06-29",
      cover: "news-2.jpg",
      coverAlt: "合成标签产品展示",
      prevHref: "news-detail.html?article=1",
      prevText: "为什么大家都青睐合成标签？",
      nextHref: "news.html",
      nextText: "返回查看更多新闻"
    }
  };

  var data = articles[articleId];
  var title = document.getElementById("article-title");
  var date = document.getElementById("article-date");
  var lead = document.querySelector(".news-detail-lead");
  var cover = document.getElementById("article-cover");
  var prev = document.getElementById("article-prev");
  var next = document.getElementById("article-next");

  if(title) title.textContent = sharedTitle;
  if(lead) lead.textContent = sharedLead;

  if(date){
    date.textContent = data.date;
    date.setAttribute("datetime", data.date);
  }

  if(cover){
    cover.src = assetPrefix + "assets/img/" + data.cover;
    cover.alt = data.coverAlt;
  }

  if(prev){
    prev.href = data.prevHref;
    var prevStrong = prev.querySelector("strong");
    if(prevStrong) prevStrong.textContent = data.prevText;
  }

  if(next){
    next.href = data.nextHref;
    var nextStrong = next.querySelector("strong");
    if(nextStrong) nextStrong.textContent = data.nextText;
  }

  document.title = sharedTitle + " - 金亚包装";
})();
