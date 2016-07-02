// Bring in the Lang library
global.Lang = require('./lang');

// Global aliases
global.translate = function(key, replacements) {
    return Lang.get(key, replacements);
};
global.translate_choice = function(key, count, replacements) {
    return Lang.choice(key, count, replacements);
};

// Set the translation messages. The object will be in the page body.
Lang.setMessages(Statamic.translations);