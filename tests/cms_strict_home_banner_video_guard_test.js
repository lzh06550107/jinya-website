const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const strictScriptPath = path.join(
    __dirname,
    '..',
    'public',
    'assets',
    'kcm-pc-strict',
    '67a40dc5e4b056b7c69412ce.js'
);
const strictScript = fs.readFileSync(strictScriptPath, 'utf8');

function runScenario(video) {
    const swiperCalls = [];
    let wowInitCalls = 0;

    const jqueryObject = {
        scroll() { return this; },
        scrollTop() { return 0; },
        addClass() { return this; },
        removeClass() { return this; },
        find() { return this; },
        each() { return this; },
        on() { return this; },
        click() { return this; },
        show() { return this; },
        hide() { return this; },
        append() { return this; },
        remove() { return this; },
        css() { return this; },
        animate() { return this; },
        height() { return 0; },
        attr() { return undefined; },
        slide() { return this; },
    };

    function jquery(argument) {
        if (typeof argument === 'function') {
            argument();
        }
        return jqueryObject;
    }

    function Swiper(selector, options) {
        const instance = {
            activeIndex: 0,
            autoplay: {
                stopCalls: 0,
                startCalls: 0,
                stop() { this.stopCalls += 1; },
                start() { this.startCalls += 1; },
            },
        };
        swiperCalls.push({selector, options, instance});
        return instance;
    }

    function WOW() {
        this.init = function () {
            wowInitCalls += 1;
        };
    }

    const windowObject = {pageYOffset: 0};
    const documentObject = {
        body: {scrollTop: 0},
        documentElement: {scrollTop: 0},
        getElementById(id) {
            return id === 'sVideo' ? video : null;
        },
    };

    vm.runInNewContext(strictScript, {
        $: jquery,
        jQuery: jquery,
        Swiper,
        WOW,
        navigator: {userAgent: ''},
        window: windowObject,
        document: documentObject,
        console,
    }, {filename: strictScriptPath});

    return {swiperCalls, wowInitCalls};
}

let imageOnly;
assert.doesNotThrow(
    () => {
        imageOnly = runScenario(null);
    },
    'The strict homepage script must not fail when the Banner has no #sVideo element.'
);
const imageBanner = imageOnly.swiperCalls.find(({selector}) => selector === '.swiper0');
assert(imageBanner, 'The image-only Banner swiper should still initialize.');
assert.strictEqual(imageBanner.options.autoplay.delay, 5000, 'Image-only Banner autoplay should use a readable interval.');
assert.strictEqual(imageBanner.instance.autoplay.stopCalls, 0, 'Image-only Banner autoplay should remain running.');
assert(imageOnly.swiperCalls.some(({selector}) => selector === '.pro_bd'), 'Product swiper initialization must run after the Banner setup.');
assert(imageOnly.swiperCalls.some(({selector}) => selector === '.abt_bd'), 'Company swiper initialization must run after the Banner setup.');
assert.strictEqual(imageOnly.wowInitCalls, 1, 'The rest of the strict homepage bundle should finish initializing.');

const video = {
    currentTime: 0,
    duration: 10,
    playCalls: 0,
    pauseCalls: 0,
    play() {
        this.playCalls += 1;
        return Promise.resolve();
    },
    pause() {
        this.pauseCalls += 1;
    },
};
const withVideo = runScenario(video);
const videoBanner = withVideo.swiperCalls.find(({selector}) => selector === '.swiper0');
assert(videoBanner, 'The video Banner swiper should initialize.');
assert.strictEqual(videoBanner.options.autoplay.delay, 100, 'Video Banner should preserve its short post-video transition delay.');
assert.strictEqual(videoBanner.instance.autoplay.stopCalls, 1, 'Video Banner autoplay should stop while the first video plays.');
assert.strictEqual(typeof video.ontimeupdate, 'function', 'Video Banner should bind its playback progress handler.');
assert(video.playCalls >= 1, 'Video Banner should preserve initial video playback.');

process.stdout.write('cms_strict_home_banner_video_guard_test: PASS\n');
