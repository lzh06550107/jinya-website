const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const sourcePath = path.join(__dirname, '..', 'public', 'assets', 'js', 'cms-home-media.js');
assert(fs.existsSync(sourcePath), 'The homepage media fallback script is missing');

function createContainer(slideCount) {
    return {
        swiper: null,
        querySelectorAll(selector) {
            assert.strictEqual(selector, '.swiper-slide');
            return new Array(slideCount).fill({});
        },
    };
}

const containers = {
    '.pro_bd': createContainer(5),
    '.abt_bd': createContainer(5),
};
const swiperCalls = [];
let domReadyHandler = null;

function Swiper(selector, options) {
    assert(containers[selector], `Unexpected carousel selector: ${selector}`);
    swiperCalls.push({selector, options});
    containers[selector].swiper = this;
}

const context = {
    document: {
        readyState: 'loading',
        addEventListener(event, handler) {
            if (event === 'DOMContentLoaded') {
                domReadyHandler = handler;
            }
        },
        querySelector(selector) {
            return containers[selector] || null;
        },
    },
    Swiper,
};

vm.runInNewContext(fs.readFileSync(sourcePath, 'utf8'), context, {filename: sourcePath});
assert(domReadyHandler, 'The fallback must wait until the homepage DOM is ready');
domReadyHandler();

const productCall = swiperCalls.find(call => call.selector === '.pro_bd');
assert(productCall, 'The Product carousel should initialize when a missing Banner video stopped the strict script');
assert.strictEqual(productCall.options.slidesPerView, 3, 'The cloned three-product layout must be preserved');
assert.strictEqual(productCall.options.navigation.nextEl, '.snext', 'The existing Product next button must be preserved');
assert.strictEqual(productCall.options.navigation.prevEl, '.sprev', 'The existing Product previous button must be preserved');
assert.strictEqual(productCall.options.loop, true, 'More than three Product items should keep looping');
assert.strictEqual(productCall.options.autoplay, 5000, 'A scrollable Product carousel should keep automatic rotation');

const companyCall = swiperCalls.find(call => call.selector === '.abt_bd');
assert(companyCall, 'The Company carousel should initialize when the strict script stopped early');
assert.strictEqual(companyCall.options.slidesPerView, 4, 'The cloned four-card layout must be preserved');
assert.strictEqual(companyCall.options.navigation.nextEl, '.next4', 'The existing Company next button must be preserved');
assert.strictEqual(companyCall.options.navigation.prevEl, '.prev4', 'The existing Company previous button must be preserved');
assert.strictEqual(companyCall.options.loop, true, 'More than four Company items should keep looping');

const initializedCallCount = swiperCalls.length;
domReadyHandler();
assert.strictEqual(swiperCalls.length, initializedCallCount, 'Initialized homepage carousels must not be initialized twice');

process.stdout.write('cms_home_media_carousel_test: PASS\n');
