(function(){
  "use strict";

  // Global header scroll motion.
  // Header background still switches as soon as the page leaves scrollTop=0,
  // but hotline geometry is interpolated continuously through the first 120px
  // so width/font/icon changes never jump between two discrete CSS states.
  var siteHeader = document.querySelector(".site-header");
  if(siteHeader){
    var headerMotionTicking = false;
    var headerMotionRange = 120;

    function headerVarNumber(name, fallback){
      var raw = window.getComputedStyle(siteHeader).getPropertyValue(name);
      var value = parseFloat(raw);
      return Number.isFinite(value) ? value : fallback;
    }

    function mixHeaderValue(from, to, progress){
      return from + (to - from) * progress;
    }

    function syncHeaderScrollMotion(){
      var y = Math.max(0, window.scrollY || window.pageYOffset || 0);
      var rawProgress = Math.min(1, y / headerMotionRange);
      // Smoothstep eases both ends while remaining tied directly to scroll.
      var progress = rawProgress * rawProgress * (3 - 2 * rawProgress);
      var logoScale = 1 + progress * 0.10;

      var phoneTopWidth = headerVarNumber("--cms-hotline-top-width", 15);
      var phoneScrolledWidth = headerVarNumber("--cms-hotline-scrolled-width", phoneTopWidth);
      var phoneTopOffset = headerVarNumber("--cms-hotline-top-offset", 0);
      var phoneScrolledOffset = headerVarNumber("--cms-hotline-scrolled-offset", phoneTopOffset);
      var badgeTopSize = headerVarNumber("--cms-hotline-badge-top-size", 0.8);
      var badgeScrolledSize = headerVarNumber("--cms-hotline-badge-scrolled-size", badgeTopSize);
      var iconTopSize = headerVarNumber("--cms-hotline-icon-top-size", 2.45);
      var iconScrolledSize = headerVarNumber("--cms-hotline-icon-scrolled-size", iconTopSize);
      var numberTopSize = headerVarNumber("--cms-hotline-number-top-size", 1.7);
      var numberScrolledSize = headerVarNumber("--cms-hotline-number-scrolled-size", numberTopSize);

      siteHeader.style.setProperty("--logo-scroll-scale", logoScale.toFixed(4));
      siteHeader.style.setProperty("--phone-scroll-scale", "1");
      siteHeader.style.setProperty("--cms-hotline-current-width", mixHeaderValue(phoneTopWidth, phoneScrolledWidth, progress).toFixed(4) + "rem");
      siteHeader.style.setProperty("--cms-hotline-current-offset", mixHeaderValue(phoneTopOffset, phoneScrolledOffset, progress).toFixed(4) + "rem");
      siteHeader.style.setProperty("--cms-hotline-current-badge-size", mixHeaderValue(badgeTopSize, badgeScrolledSize, progress).toFixed(4) + "rem");
      siteHeader.style.setProperty("--cms-hotline-current-icon-size", mixHeaderValue(iconTopSize, iconScrolledSize, progress).toFixed(4) + "rem");
      siteHeader.style.setProperty("--cms-hotline-current-number-size", mixHeaderValue(numberTopSize, numberScrolledSize, progress).toFixed(4) + "rem");
      siteHeader.classList.toggle("is-scrolled", y > 0);
      siteHeader.classList.toggle("has-scroll-scale", rawProgress > 0);

      headerMotionTicking = false;
    }

    function requestHeaderScrollMotion(){
      if(headerMotionTicking) return;
      headerMotionTicking = true;
      window.requestAnimationFrame(syncHeaderScrollMotion);
    }

    syncHeaderScrollMotion();
    window.addEventListener("scroll", requestHeaderScrollMotion, { passive:true });
    window.addEventListener("resize", requestHeaderScrollMotion, { passive:true });
    window.addEventListener("pageshow", requestHeaderScrollMotion);
  }

  // Mobile navigation accessibility and state synchronization.
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.querySelector(".main-nav");
  if(toggle && nav){
    var mobileNavQuery = window.matchMedia ? window.matchMedia("(max-width:768px)") : null;

    function navIsMobile(){
      return mobileNavQuery ? mobileNavQuery.matches : window.innerWidth <= 768;
    }

    function syncNavState(){
      var mobile = navIsMobile();
      var open = mobile && nav.classList.contains("open");

      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.setAttribute("aria-label", open ? "关闭菜单" : "打开菜单");

      if(mobile){
        nav.setAttribute("aria-hidden", open ? "false" : "true");
        if("inert" in nav) nav.inert = !open;
      }else{
        nav.classList.remove("open");
        nav.removeAttribute("aria-hidden");
        if("inert" in nav) nav.inert = false;
      }
    }

    function closeNav(){
      nav.classList.remove("open");
      syncNavState();
    }

    toggle.addEventListener("click", function(){
      nav.classList.toggle("open");
      syncNavState();
    });

    nav.querySelectorAll("a").forEach(function(a){
      a.addEventListener("click", closeNav);
    });

    document.addEventListener("keydown", function(event){
      if(event.key === "Escape" && nav.classList.contains("open")){
        closeNav();
        toggle.focus();
      }
    });

    if(mobileNavQuery){
      if(mobileNavQuery.addEventListener) mobileNavQuery.addEventListener("change", syncNavState);
      else if(mobileNavQuery.addListener) mobileNavQuery.addListener(syncNavState);
    }else{
      window.addEventListener("resize", syncNavState, { passive:true });
    }

    syncNavState();
  }

  // Placeholder links must not unexpectedly jump the page to the top.
  // They automatically become active again once a real href replaces "#".
  document.querySelectorAll('a[href="#"]').forEach(function(link){
    link.setAttribute("aria-disabled", "true");
    link.setAttribute("tabindex", "-1");
    link.addEventListener("click", function(event){ event.preventDefault(); });
  });

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
      if(autoplay > 0 && !reduceMotion && !document.hidden){
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
    document.addEventListener("visibilitychange", function(){
      if(document.hidden) window.clearInterval(timer);
      else restart();
    });
    layout();
    restart();
  });

  // Application cases: keep one category in a responsive carousel.
  // Desktop shows three cards at once; arrows appear only when the active
  // category has more cards than the current viewport can display.
  document.querySelectorAll("[data-case-carousel]").forEach(function(carousel){
    var scope = carousel.closest("[data-tab-scope]");
    var tabs = scope ? Array.prototype.slice.call(scope.querySelectorAll("[data-tabs] button[data-tab]")) : [];
    var viewport = carousel.querySelector(".case-carousel-viewport");
    var track = carousel.querySelector(".case-carousel-track");
    var cards = Array.prototype.slice.call(carousel.querySelectorAll(".case-card[data-tab-groups]"));
    var prev = carousel.querySelector(".case-carousel-prev");
    var next = carousel.querySelector(".case-carousel-next");
    if(!viewport || !track || !cards.length) return;

    var index = 0;
    var perView = 3;
    var activeKey = "";
    var visibleCards = [];
    var pointerStart = null;

    function getPerView(){
      var desktop = parseInt(carousel.getAttribute("data-desktop-per-view") || "3", 10);
      var tablet = parseInt(carousel.getAttribute("data-tablet-per-view") || "2", 10);
      var mobile = parseInt(carousel.getAttribute("data-mobile-per-view") || "1", 10);
      if(window.innerWidth <= 640) return mobile;
      if(window.innerWidth <= 900) return tablet;
      return desktop;
    }

    function cardMatches(card, key){
      if(!key || key === "all") return true;
      var raw = card.getAttribute("data-tab-groups") || "";
      var groups = raw.split(/[\s,;|]+/).filter(Boolean);
      return groups.length === 0 || groups.indexOf(key) !== -1;
    }

    function maxIndex(){
      return Math.max(0, visibleCards.length - perView);
    }

    function render(){
      var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || "0");
      var width = perView > 0 ? (viewport.clientWidth - gap * (perView - 1)) / perView : viewport.clientWidth;

      visibleCards.forEach(function(card){
        card.style.flexBasis = Math.max(0, width) + "px";
      });

      index = Math.max(0, Math.min(index, maxIndex()));
      track.style.transform = "translate3d(" + (-(width + gap) * index) + "px,0,0)";

      var canScroll = visibleCards.length > perView;
      if(prev){
        prev.hidden = !canScroll;
        prev.disabled = !canScroll || index === 0;
      }
      if(next){
        next.hidden = !canScroll;
        next.disabled = !canScroll || index === maxIndex();
      }

      carousel.classList.toggle("is-static", !canScroll);
    }

    function layout(){
      perView = Math.max(1, Math.min(getPerView(), Math.max(1, visibleCards.length)));
      render();
    }

    function applyCategory(key){
      activeKey = key || "";
      index = 0;
      visibleCards = [];

      cards.forEach(function(card){
        var visible = cardMatches(card, activeKey);
        card.hidden = !visible;
        card.setAttribute("aria-hidden", visible ? "false" : "true");
        if(visible) visibleCards.push(card);
      });

      tabs.forEach(function(tab){
        var selected = (tab.getAttribute("data-tab") || "") === activeKey;
        tab.classList.toggle("active", selected);
        tab.setAttribute("aria-selected", selected ? "true" : "false");
      });

      layout();
    }

    function go(target){
      index = Math.max(0, Math.min(target, maxIndex()));
      render();
    }

    tabs.forEach(function(tab){
      tab.setAttribute("role", "tab");
      tab.addEventListener("click", function(){
        applyCategory(tab.getAttribute("data-tab") || "");
      });
    });

    if(prev) prev.addEventListener("click", function(){ go(index - 1); });
    if(next) next.addEventListener("click", function(){ go(index + 1); });

    carousel.addEventListener("keydown", function(event){
      if(event.key === "ArrowLeft"){ event.preventDefault(); go(index - 1); }
      if(event.key === "ArrowRight"){ event.preventDefault(); go(index + 1); }
    });

    viewport.addEventListener("pointerdown", function(event){
      pointerStart = event.clientX;
    });
    viewport.addEventListener("pointerup", function(event){
      if(pointerStart === null) return;
      var distance = event.clientX - pointerStart;
      pointerStart = null;
      if(Math.abs(distance) > 42) go(index + (distance < 0 ? 1 : -1));
    });
    viewport.addEventListener("pointercancel", function(){ pointerStart = null; });

    var initialTab = tabs.filter(function(tab){ return tab.classList.contains("active"); })[0] || tabs[0];
    carousel.setAttribute("tabindex", "0");
    carousel.setAttribute("role", "region");
    carousel.setAttribute("aria-label", "应用案例");
    carousel.classList.add("is-ready");
    applyCategory(initialTab ? (initialTab.getAttribute("data-tab") || "") : "");
    window.addEventListener("resize", layout, { passive:true });
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

  // Homepage product center: reference-style scroll motion.
  // Motion is armed at page load, but each group starts only when its own
  // product row actually enters the viewport.
  var productSection = document.querySelector(".product-section");
  if(productSection && "IntersectionObserver" in window){
    var productReduceMotion = window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if(!productReduceMotion){
      productSection.classList.add("product-motion-enabled");

      function armProductMotion(el, motion, delay){
        if(!el) return null;
        el.classList.add("product-motion-target");
        String(motion || "").split(/\s+/).forEach(function(cls){
          if(cls) el.classList.add(cls);
        });
        el.style.setProperty("--product-motion-delay", (delay || 0) + "ms");
        return el;
      }

      function revealTargets(targets){
        targets.forEach(function(el){
          if(el) el.classList.add("is-visible");
        });
      }

      function observeGroup(anchor, targets, options){
        if(!anchor || !targets.length) return;
        var groupObs = new IntersectionObserver(function(entries){
          entries.forEach(function(entry){
            if(entry.isIntersecting){
              revealTargets(targets);
              groupObs.disconnect();
            }
          });
        }, options || {
          threshold:0.12,
          rootMargin:"0px 0px -10% 0px"
        });
        groupObs.observe(anchor);
      }

      var titleTargets=[
        armProductMotion(productSection.querySelector(".section-head"), "product-motion-up", 0)
      ].filter(Boolean);

      var firstTargets=[];
      var pm01=productSection.querySelector(".pm-01");
      if(pm01){
        firstTargets.push(
          armProductMotion(pm01.querySelector(".num"), "product-motion-left", 0),
          armProductMotion(pm01.querySelector("h3"), "product-motion-left", 150),
          armProductMotion(pm01.querySelector("p"), "product-motion-left", 190),
          armProductMotion(pm01.querySelector(".foot"), "product-motion-left", 285),
          armProductMotion(pm01.querySelector(".ph"), "product-motion-media", 1110)
        );
      }

      var pm02=productSection.querySelector(".pm-02");
      if(pm02){
        firstTargets.push(
          armProductMotion(pm02.querySelector(".text"), "product-motion-left", 110),
          armProductMotion(pm02.querySelector(".ph"), "product-motion-right product-motion-media", 210)
        );
      }
      firstTargets=firstTargets.filter(Boolean);

      var thirdTargets=[];
      var pm03=productSection.querySelector(".pm-03");
      if(pm03){
        thirdTargets.push(
          armProductMotion(pm03.querySelector(".text"), "product-motion-left", 20),
          armProductMotion(pm03.querySelector(".ph"), "product-motion-right product-motion-media", 150)
        );
      }
      thirdTargets=thirdTargets.filter(Boolean);

      var fourthTargets=[];
      var pm04=productSection.querySelector(".pm-04");
      if(pm04){
        fourthTargets.push(
          armProductMotion(pm04.querySelector(".text"), "product-motion-left", 20)
        );
        Array.prototype.forEach.call(pm04.querySelectorAll(".swiper-slide"), function(slide, i){
          fourthTargets.push(
            armProductMotion(slide, "product-motion-up product-motion-media", 140 + i * 120)
          );
        });
      }
      fourthTargets=fourthTargets.filter(Boolean);

      var serviceTargets=[];
      var serviceCta=productSection.querySelector(".service-cta");
      if(serviceCta){
        serviceTargets.push(
          armProductMotion(serviceCta.querySelector(".service-cta-copy"), "product-motion-left", 0)
        );
        Array.prototype.forEach.call(serviceCta.querySelectorAll(".service-cta-feature"), function(item, i){
          serviceTargets.push(
            armProductMotion(item, "product-motion-up", 80 + i * 150)
          );
        });
      }
      serviceTargets=serviceTargets.filter(Boolean);

      observeGroup(productSection.querySelector(".section-head"), titleTargets, {
        threshold:0.2,
        rootMargin:"0px 0px -12% 0px"
      });

      observeGroup(productSection.querySelector(".product-masonry"), firstTargets, {
        threshold:0.06,
        rootMargin:"0px 0px -8% 0px"
      });

      observeGroup(pm03, thirdTargets, {
        threshold:0.18,
        rootMargin:"0px 0px -10% 0px"
      });

      observeGroup(pm04, fourthTargets, {
        threshold:0.14,
        rootMargin:"0px 0px -10% 0px"
      });

      observeGroup(serviceCta, serviceTargets, {
        threshold:0.16,
        rootMargin:"0px 0px -8% 0px"
      });
    }
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

  // Product detail gallery, media preview and anchored section navigation.
  var productDetail = document.querySelector("[data-product-detail]");
  if(productDetail){
    var detailMain = productDetail.querySelector("[data-jpd-main]");
    var detailThumbs = Array.prototype.slice.call(productDetail.querySelectorAll("[data-jpd-thumb]"));
    var preview = productDetail.querySelector("[data-jpd-preview]");
    var previewContent = productDetail.querySelector("[data-jpd-preview-content]");
    var previewOpen = productDetail.querySelector("[data-jpd-open-preview]");
    var previewClose = productDetail.querySelector("[data-jpd-preview-close]");
    var activeMedia = { type:"", src:"", alt:"" };

    function syncActiveFromStage(){
      if(!detailMain) return;
      var image = detailMain.querySelector("img");
      var video = detailMain.querySelector("video");
      if(video){
        activeMedia = {type:"video",src:video.getAttribute("src") || "",alt:""};
      }else if(image){
        activeMedia = {type:"image",src:image.getAttribute("src") || "",alt:image.getAttribute("alt") || ""};
      }
    }

    function showDetailMedia(type, src, alt){
      if(!detailMain || !src) return;
      detailMain.innerHTML = "";
      var media;
      if(type === "video"){
        media = document.createElement("video");
        media.controls = true;
        media.preload = "metadata";
        media.src = src;
      }else{
        media = document.createElement("img");
        media.src = src;
        media.alt = alt || "";
        media.decoding = "async";
      }
      detailMain.appendChild(media);
      activeMedia = {type:type,src:src,alt:alt || ""};
    }

    detailThumbs.forEach(function(thumb){
      thumb.addEventListener("click", function(){
        detailThumbs.forEach(function(item){ item.classList.remove("is-active"); });
        thumb.classList.add("is-active");
        showDetailMedia(
          thumb.getAttribute("data-type") || "image",
          thumb.getAttribute("data-src") || "",
          thumb.getAttribute("data-alt") || ""
        );
      });
    });

    function openProductPreview(){
      if(!preview || !previewContent || !activeMedia.src) return;
      previewContent.innerHTML = "";
      var media;
      if(activeMedia.type === "video"){
        media = document.createElement("video");
        media.controls = true;
        media.autoplay = true;
        media.src = activeMedia.src;
      }else{
        media = document.createElement("img");
        media.src = activeMedia.src;
        media.alt = activeMedia.alt || "";
      }
      previewContent.appendChild(media);
      preview.hidden = false;
      preview.setAttribute("aria-hidden","false");
      document.documentElement.style.overflow = "hidden";
      if(previewClose) previewClose.focus();
    }

    function closeProductPreview(){
      if(!preview) return;
      preview.hidden = true;
      preview.setAttribute("aria-hidden","true");
      if(previewContent) previewContent.innerHTML = "";
      document.documentElement.style.overflow = "";
      if(previewOpen) previewOpen.focus();
    }

    syncActiveFromStage();
    if(previewOpen) previewOpen.addEventListener("click", openProductPreview);
    if(previewClose) previewClose.addEventListener("click", closeProductPreview);
    if(preview){
      preview.addEventListener("click", function(event){
        if(event.target === preview) closeProductPreview();
      });
    }
    document.addEventListener("keydown", function(event){
      if(event.key === "Escape" && preview && !preview.hidden) closeProductPreview();
    });

    productDetail.querySelectorAll('.jpd-detail-nav a[href^="#"]').forEach(function(link){
      link.addEventListener("click", function(event){
        var target = document.querySelector(link.getAttribute("href"));
        if(!target) return;
        event.preventDefault();
        target.scrollIntoView({behavior:"smooth",block:"start"});
      });
    });
  }

})();
