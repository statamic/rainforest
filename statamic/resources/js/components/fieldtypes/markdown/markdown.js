var CodeMirror = require('codemirror');
require('codemirror/mode/markdown/markdown');

module.exports = {

    template: require('./markdown.template.html'),

    components: {
        selector: require('../assets/selector')
    },

    props: ['data', 'name', 'config'],

    data: function() {
        return {
            mode: 'write',
            selections: null,      // CodeMirror text selections
            assetSelector: false,  // Is the asset selector opened?
            selectedAssets: [],    // Assets selected in the selector
            selectorViewMode: null,
            draggingFile: false,
            showCheatsheet: false,
            codemirror: null       // The CodeMirror instance
        };
    },

    methods: {
        /**
         * Get the text for a selection
         *
         * @param  Range selection  A CodeMirror Range
         * @return string
         */
        getText: function(selection) {
            var i = _.indexOf(this.selections, selection);

            return this.codemirror.getSelections()[i];
        },

        /**
         * Inserts an image at the selection
         *
         * @param  String url  URL of the image
         * @param  String alt  Alt text
         */
        insertImage: function(url, alt) {
            var cm = this.codemirror.doc

            var selection = '';
            if (cm.somethingSelected()) {
                selection = cm.getSelection();
            } else if (alt) {
                selection = alt;
            }

            var url = url || '';

            // Replace the string
            var str = '![' + selection + ']('+ url +')';
            cm.replaceSelection(str, 'start');

            // Select the text
            var line = cm.getCursor().line;
            var start = cm.getCursor().ch + 2; // move past the ![
            var end = start + selection.length;
            cm.setSelection({line:line,ch:start}, {line:line,ch:end});

            this.codemirror.focus();
        },

        /**
         * Appends an image to the end of the data
         *
         * @param  String url  URL of the image
         * @param  String alt  Alt text
         */
        appendImage: function(url, alt) {
            alt = alt || '';
            this.data += '\n\n!['+alt+']('+url+')';
        },

        /**
         * Inserts a link at the selection
         *
         * @param  String url   URL of the link
         * @param  String text  Link text
         */
        insertLink: function(url, text) {
            var cm = this.codemirror.doc

            var selection = '';
            if (cm.somethingSelected()) {
                selection = cm.getSelection();
            } else if (text) {
                selection = text;
            }

            if (! url) {
                url = prompt('Enter URL', 'http://');
                if (! url) {
                    url = '';
                }
            }

            // Replace the string
            var str = '[' + selection + ']('+ url +')';
            cm.replaceSelection(str, 'start');

            // Select the text
            var line = cm.getCursor().line;
            var start = cm.getCursor().ch + 1; // move past the first [
            var end = start + selection.length;
            cm.setSelection({line:line,ch:start}, {line:line,ch:end});

            this.codemirror.focus();
        },

        /**
         * Inserts a link at the end of the data
         *
         * @param  String url   URL of the link
         * @param  String text  Link text
         */
        appendLink: function(url, text) {
            text = text || '';
            this.data += '\n\n['+text+']('+url+')';
        },

        /**
         * Toggle the boldness on the current selection(s)
         */
        bold: function() {
            var self = this;
            var replacements = [];

            _.each(self.selections, function (selection, i) {
                var replacement = (self.isBold(selection))
                    ? self.removeBold(selection)
                    : self.makeBold(selection);

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        /**
         * Check if a string is bold
         *
         * @param  Range  selection  CodeMirror selection
         * @return Boolean
         */
        isBold: function (selection) {
            return this.getText(selection).match(/^\*{2}(.*)\*{2}$/);
        },

        /**
         * Make a string bold
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        makeBold: function (selection) {
            return '**' + this.getText(selection) + '**';
        },

        /**
         * Remove the boldness from a string
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        removeBold: function (selection) {
            var text = this.getText(selection);

            return text.substring(2, text.length-2);
        },

        /**
         * Toggle the italics on the current selection(s)
         */
        italic: function() {
            var self = this;
            var replacements = [];

            _.each(self.selections, function (selection, i) {
                var replacement = (self.isItalic(selection))
                    ? self.removeItalic(selection)
                    : self.makeItalic(selection);

                replacements.push(replacement);
            });

            this.codemirror.replaceSelections(replacements, 'around');

            this.codemirror.focus();
        },

        /**
         * Check if a string is italic
         *
         * @param  Range  selection  CodeMirror selection
         * @return Boolean
         */
        isItalic: function (selection) {
            return this.getText(selection).match(/^\_(.*)\_$/);
        },

        /**
         * Make a string italic
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        makeItalic: function (selection) {
            return '_' + this.getText(selection) + '_';
        },

        /**
         * Remove the italics from a string
         *
         * @param  Range  selection  CodeMirror selection
         * @return String
         */
        removeItalic: function (selection) {
            var text = this.getText(selection);

            return text.substring(1, text.length-1);
        },

        /**
         * Bind the uploader plugin
         */
        bindUploader: function() {
            var self = this;
            var $uploader = $(this.$els.writer);

            $uploader.dmUploader({
                url: cp_url('assets'),
                extraData: {
                    container: self.container,
                    folder: self.folder
                },
                onUploadSuccess: function(id, data) {
                    if (data.asset.is_image) {
                        self.appendImage(data.asset.url);
                    } else {
                        self.appendLink(data.asset.url);
                    }
                },
                onUploadError: function(id, message) {
                }
            });

            self.plugin = $uploader.data('dmUploader');
        },

        /**
         * Open the asset selector
         */
        addAsset: function() {
            this.assetSelector = true;
        },

        /**
         * Execute a keyboard shortcut, when applicable
         */
        shortcut: function(e) {
            var key = e.keyCode;
            var meta = e.metaKey === true;

            if (meta && key === 66) { // cmd+b
                this.bold();
                e.preventDefault();
            }

            if (meta && key === 73) { // cmd+i
                this.italic();
                e.preventDefault();
            }

            if (meta && key === 75) { // cmd+k
                this.insertLink();
                e.preventDefault();
            }
        }
    },

    computed: {
        assetsEnabled: function() {
            return this.config && this.config.container;
        },

        container: function() {
            return this.config.container;
        },

        folder: function() {
            return this.config.folder || '/';
        },

        cheatsheet: function() {
            return this.config && this.config.cheatsheet;
        }
    },

    events: {

        /**
         * When assets are selected from the modal, this event gets fired.
         *
         * @param  Array assets  All the assets that were selected
         */
        'assets.selected': function (assets) {
            var self = this;

            // If one asset is chosen, it's safe to replace the selection.
            // Otherwise we'll just tack on the assets to the end of the text.
            var method = (assets.length === 1) ? 'insert' : 'append';

            _.each(assets, function (asset) {
                var alt = asset.alt || '';
                if (asset.is_image) {
                    self[method+'Image'](asset.url, alt);
                } else {
                    self[method+'Link'](asset.url, alt);
                }
            });

            // We don't want to maintain the asset selections
            this.selectedAssets = [];
        }

    },

    ready: function() {
        if (this.assetsEnabled) {
            this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';
            this.bindUploader();
        }

        var self = this;

        self.codemirror = CodeMirror(this.$els.codemirror, {
            value: self.data || '',
            mode: 'markdown',
            dragDrop: false,
            lineWrapping: true,
            viewportMargin: Infinity
        });

        self.codemirror.on('change', function (cm) {
            self.data = cm.doc.getValue();
        });

        // Expose the array of selections to the Vue instance
        self.codemirror.on('beforeSelectionChange', function (cm, obj) {
            self.selections = obj.ranges;
        });

        // Update CodeMirror if we change the value independent of CodeMirror
        this.$watch('data', function(val) {
            if (val !== self.codemirror.doc.getValue()) {
                self.codemirror.doc.setValue(val);
            }
        });
    }

};
