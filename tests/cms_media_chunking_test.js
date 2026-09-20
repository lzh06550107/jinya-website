const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync('public/assets/js/backend/cms/media_preview.js', 'utf8');
let mediaPreview = null;
let createdUploadButton = null;

const emptyCollection = {
    length: 0,
    first: function () { return this; },
    next: function () { return this; }
};

function element(initialAttributes) {
    return {
        length: 1,
        attributes: Object.assign({}, initialAttributes || {}),
        children: [],
        attr: function (name, value) {
            if (arguments.length === 1) {
                return this.attributes[name];
            }
            this.attributes[name] = String(value);
            return this;
        },
        append: function (child) {
            this.children.push(child);
            return this;
        },
        after: function () { return this; },
        css: function () { return this; },
        first: function () { return this; },
        next: function () { return emptyCollection; }
    };
}

const form = element();
const group = element();
const input = element({'data-media-preview': 'image', 'data-media-upload': 'true'});

form.find = function (selector) {
    if (selector === '[data-media-preview]') {
        return {
            each: function (callback) {
                callback.call(input);
            }
        };
    }
    return emptyCollection;
};
input.closest = function (selector) {
    return selector === 'form' ? form : group;
};
input.wrap = function () { return input; };

function jquery(value) {
    if (value === undefined) {
        return emptyCollection;
    }
    if (typeof value !== 'string') {
        return value;
    }
    const node = element();
    if (value.indexOf('faupload') !== -1) {
        createdUploadButton = node;
    }
    return node;
}
jquery.trim = function (value) {
    return String(value || '').trim();
};

const sandbox = {
    Config: {
        upload: {
            chunking: true,
            chunksize: 1048576
        }
    },
    define: function (dependencies, factory) {
        mediaPreview = factory(jquery);
    },
    setTimeout: setTimeout
};

vm.runInNewContext(source, sandbox, {filename: 'media_preview.js'});
assert(mediaPreview, 'media_preview AMD module should load');
mediaPreview.prepare(form);

assert(createdUploadButton, 'prepare should create a CMS upload button');
assert.strictEqual(createdUploadButton.attributes['data-chunking'], 'true', 'CMS media upload should enable chunking');
assert.strictEqual(createdUploadButton.attributes['data-chunk-size'], '1048576', 'CMS media upload should use the configured 1 MB chunk size');

console.log('cms_media_chunking_test: PASS');
