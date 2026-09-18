(function(){
  "use strict";

  // Keep every internal page on the same published HTML/CSS release. This
  // avoids a cached older page being mixed with the latest banner styles.
  const releaseVersion = '40';
  var pageLinkPattern = /^(?:index|labels|bags|boxes|about|news|contact)\.html(?:[?#].*)?$/;
  document.querySelectorAll('a[href]').forEach(function(link){
    var rawHref = link.getAttribute('href');
    if(!rawHref || !pageLinkPattern.test(rawHref)) return;
    var target = new URL(rawHref, window.location.href);
    target.searchParams.set('v', releaseVersion);
    link.setAttribute('href', target.pathname.split('/').pop() + target.search + target.hash);
  });

  // Homepage header: transparent over the banner at the very top;
  // use the normal white header treatment as soon as the page has scrolled.
  var homeHeader = document.querySelector(".site-header--home");
  if(homeHeader){
    function syncHomeHeaderState(){
      homeHeader.classList.toggle("is-scrolled", window.scrollY > 0);
    }
    syncHomeHeaderState();
    window.addEventListener("scroll", syncHomeHeaderState, { passive:true });
    window.addEventListener("pageshow", syncHomeHeaderState);
  }

  // Mobile nav toggle
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.querySelector(".main-nav");
  if(toggle && nav){
    toggle.addEventListener("click", function(){
      nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", nav.classList.contains("open") ? "true" : "false");
    });
    nav.querySelectorAll("a").forEach(function(a){
      a.addEventListener("click", function(){ nav.classList.remove("open"); });
    });
  }

  // Reusable image/video swiper. Each instance controls only its own slides,
  // so the hero, product gallery and sample gallery can behave independently.
  document.querySelectorAll("[data-swiper]").forEach(function(swiper){
    var viewport = swiper.querySelector(".swiper-viewport");
    var track = swiper.querySelector(".swiper-track");
    var slides = Array.prototype.slice.call(swiper.querySelectorAll(".swiper-slide"));
    var prev = swiper.querySelector(".swiper-prev");
    var next = swiper.querySelector(".swiper-next");
    var dots = swiper.querySelector(".swiper-dots");
    if(!viewport || !track || slides.length < 2) return;

    var index = 0;
    var perView = 1;
    var timer = null;
    var autoplay = parseInt(swiper.getAttribute("data-autoplay") || "0", 10);
    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var pointerStart = null;

    swiper.setAttribute("tabindex", "0");
    swiper.setAttribute("role", "region");
    swiper.setAttribute("aria-roledescription", "carousel");

    function getPerView(){
      var desktop = parseInt(swiper.getAttribute("data-per-view") || "1", 10);
      var tablet = parseInt(swiper.getAttribute("data-tablet-per-view") || Math.min(desktop, 2), 10);
      var mobile = parseInt(swiper.getAttribute("data-mobile-per-view") || "1", 10);
      if(window.innerWidth <= 640) return mobile;
      if(window.innerWidth <= 900) return tablet;
      return desktop;
    }

    function maxIndex(){ return Math.max(0, slides.length - perView); }

    function buildDots(){
      if(!dots) return;
      dots.innerHTML = "";
      for(var i = 0; i <= maxIndex(); i++){
        (function(target){
          var dot = document.createElement("button");
          dot.type = "button";
          dot.className = "swiper-dot";
          dot.setAttribute("aria-label", "转到第" + (target + 1) + "组");
          dot.addEventListener("click", function(){ go(target, true); });
          dots.appendChild(dot);
        })(i);
      }
    }

    function syncMedia(){
      slides.forEach(function(slide, slideIndex){
        var active = slideIndex >= index && slideIndex < index + perView;
        slide.setAttribute("aria-hidden", active ? "false" : "true");
        slide.querySelectorAll("video").forEach(function(video){
          if(active){
            if(video.muted || video.hasAttribute("muted")) video.play().catch(function(){});
          }else{
            video.pause();
            video.currentTime = 0;
          }
        });
      });
    }

    function render(){
      var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || "0");
      var width = (viewport.clientWidth - gap * (perView - 1)) / perView;
      slides.forEach(function(slide){ slide.style.flexBasis = width + "px"; });
      track.style.transform = "translate3d(" + (-(width + gap) * index) + "px,0,0)";
      if(prev) prev.disabled = index === 0;
      if(next) next.disabled = index === maxIndex();
      if(dots){
        Array.prototype.forEach.call(dots.children, function(dot, i){
          dot.classList.toggle("active", i === index);
          dot.setAttribute("aria-current", i === index ? "true" : "false");
        });
      }
      syncMedia();
      var heroHost = swiper.closest(".hero--home");
      if(heroHost) heroHost.setAttribute("data-active-index", String(index));
    }

    function restart(){
      window.clearInterval(timer);
      if(autoplay > 0 && !reduceMotion){
        timer = window.setInterval(function(){ go(index >= maxIndex() ? 0 : index + 1, false); }, autoplay);
      }
    }

    function go(target, userAction){
      index = Math.max(0, Math.min(target, maxIndex()));
      render();
      if(userAction) restart();
    }

    function layout(){
      perView = Math.max(1, Math.min(getPerView(), slides.length));
      index = Math.min(index, maxIndex());
      swiper.classList.toggle("is-static", maxIndex() === 0);
      buildDots();
      render();
    }

    if(prev) prev.addEventListener("click", function(){ go(index - 1, true); });
    if(next) next.addEventListener("click", function(){ go(index + 1, true); });
    swiper.addEventListener("keydown", function(event){
      if(event.key === "ArrowLeft"){ event.preventDefault(); go(index - 1, true); }
      if(event.key === "ArrowRight"){ event.preventDefault(); go(index + 1, true); }
    });
    viewport.addEventListener("pointerdown", function(event){ pointerStart = event.clientX; });
    viewport.addEventListener("pointerup", function(event){
      if(pointerStart === null) return;
      var distance = event.clientX - pointerStart;
      pointerStart = null;
      if(Math.abs(distance) > 42) go(index + (distance < 0 ? 1 : -1), true);
    });
    viewport.addEventListener("pointercancel", function(){ pointerStart = null; });
    swiper.addEventListener("mouseenter", function(){ window.clearInterval(timer); });
    swiper.addEventListener("mouseleave", restart);
    swiper.addEventListener("focusin", function(){ window.clearInterval(timer); });
    swiper.addEventListener("focusout", restart);
    slides.forEach(function(slide){
      slide.querySelectorAll("video").forEach(function(video){
        video.addEventListener("ended", function(){ go(index >= maxIndex() ? 0 : index + 1, false); });
      });
    });
    window.addEventListener("resize", layout);
    layout();
    restart();
  });

  // Generic tabs: any [data-tabs] wrapper with [data-tab] buttons controlling [data-panel] targets
  document.querySelectorAll("[data-tabs]").forEach(function(group){
    var buttons = group.querySelectorAll("[data-tab]");
    var scope = group.closest("[data-tab-scope]") || document;
    buttons.forEach(function(btn){
      btn.addEventListener("click", function(){
        var target = btn.getAttribute("data-tab");
        buttons.forEach(function(b){ b.classList.remove("active"); });
        btn.classList.add("active");
        scope.querySelectorAll("[data-panel]").forEach(function(p){
          p.classList.toggle("active", p.getAttribute("data-panel") === target);
        });
      });
    });
  });

  // Back to top
  var topBtn = document.querySelector(".to-top");
  if(topBtn){
    topBtn.addEventListener("click", function(){
      window.scrollTo({ top:0, behavior:"smooth" });
    });
  }

  // Animated stat counters
  var counters = document.querySelectorAll(".stat .n[data-count]");
  if(counters.length && "IntersectionObserver" in window){
    var seen = new WeakSet();
    var obs = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting && !seen.has(entry.target)){
          seen.add(entry.target);
          animateCount(entry.target);
        }
      });
    }, { threshold:0.4 });
    counters.forEach(function(c){ obs.observe(c); });
  }
  function animateCount(el){
    var target = parseFloat(el.getAttribute("data-count"));
    var suffix = el.getAttribute("data-suffix") || "";
    var dur = 1200, start = null;
    function step(ts){
      if(start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      var val = target * eased;
      el.textContent = (target % 1 === 0 ? Math.round(val) : val.toFixed(1)) + suffix;
      if(p < 1) requestAnimationFrame(step);
      else el.textContent = (target % 1 === 0 ? target : target.toFixed(1)) + suffix;
    }
    requestAnimationFrame(step);
  }

  // Reveal-on-scroll for cards/sections (progressive enhancement — content is
  // visible by default in CSS; only arm the fade-in if we can reliably observe it)
  var revealEls = document.querySelectorAll(".reveal");
  if(revealEls.length && "IntersectionObserver" in window){
    var revealObs = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add("in");
          revealObs.unobserve(entry.target);
        }
      });
    }, { threshold:0.15 });
    revealEls.forEach(function(el){
      el.classList.add("will-animate");
      revealObs.observe(el);
    });
  }
})();
