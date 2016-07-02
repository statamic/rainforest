Mousetrap = require('mousetrap');

// Mousetrap Bind Global
(function(a){var c={},d=a.prototype.stopCallback;a.prototype.stopCallback=function(e,b,a,f){return this.paused?!0:c[a]||c[f]?!1:d.call(this,e,b,a)};a.prototype.bindGlobal=function(a,b,d){this.bind(a,b,d);if(a instanceof Array)for(b=0;b<a.length;b++)c[a[b]]=!0;else c[a]=!0};a.init()})(Mousetrap);

module.exports = {

    template: require('./publish.template.html'),

    components: {
        'publish-fields': require('./fields'),
        'locale-selector': require('./locale-selector'),
        'user-options': require('./user-options')
    },

    deep: true,

    props: {
        title: String,
        extra: String,
        isNew: Boolean,
        contentData: String,
        contentType: String,
        uuid: String,
        fieldsetName: String,
        slug: {
            type: String,
            default: ''
        },
        url: String,
        status: {
            type: Boolean,
            default: true
        },
        locale: String,
        locales: String, // json string. parsed in ready()
        isDefaultLocale: {
            type: Boolean,
            default: true
        },
        removeTitle: {
            type: Boolean,
            default: false
        }
    },

    data: function() {
        return {
            loading: false,
            saving: false,
            editingLayout: false,
            fieldset: {},
            formData: { extra: {}, fields: {} },
            isSlugModified: false,
            iframeLoading: false,
            previewRequestQueued: false,
            errors: []
        };
    },

    computed: {

        isEntry: function() {
            return this.contentType === 'entry';
        },

        isTaxonomy: function() {
            return this.contentType === 'taxonomy';
        },

        isGlobal: function() {
            return this.contentType === 'global';
        },

        isUser: function() {
            return this.contentType === 'user';
        },

        isSettings: function() {
            return this.contentType === 'settings';
        },

        isAddon: function() {
            return this.contentType === 'addon';
        },

        isPage: function() {
            return this.contentType === 'page';
        },

        isHomePage: function() {
            return this.isPage && this.url === '/';
        },

        shouldShowMeta: function() {
            if (this.isHomePage) return true;

            return !this.isUser && !this.isSettings && !this.isAddon && (this.shouldShowSlug || this.shouldShowDate || this.shouldShowLocales);
        },

        shouldShowTitle: function() {
            return !this.isGlobal; // other conditions will hide the wrapper of this element
        },

        shouldShowSlug: function() {
            return !this.isSettings && !this.isAddon && !this.isGlobal && !this.isUser && !this.isHomePage;
        },

        shouldShowStatus: function() {
            return !this.isSettings && !this.isAddon && this.isDefaultLocale && !this.isGlobal && !this.isTaxonomy && !this.isUser && !this.isHomePage;
        },

        shouldShowLocales: function() {
            return this.locales && this.locales.length > 1 && !this.isNew;
        },

        shouldShowDate: function() {
            // Only entries can have a date
            if (!this.isEntry) {
                return false;
            }

            // Existing entry and a datetime has been passed in?
            if (!this.isNew && this.formData.extra.datetime) {
                return true;
            }

            // New entry and it uses dates for ordering?
            if (this.isNew && this.formData.extra.order_type === 'date') {
                return true;
            }

            return false;
        },

        shouldShowSneakPeek: function() {
            return !this.isGlobal && !this.isSettings && !this.isUser && !this.isAddon;
        },

        canEditLayout: function() {
            return !this.isSettings && this.can('fieldsets:manage');
        },

        hasErrors: function() {
            return _.size(this.errors) !== 0;
        }

    },

    methods: {

        initFormData: function() {
            this.formData = {
                fieldset: this.fieldsetName,
                new: this.isNew,
                type: this.contentType,
                uuid: this.uuid,
                status: this.status,
                slug: this.slug,
                locale: this.locale,
                extra: this.extra,
                fields: this.contentData
            };
        },

        publish: function() {
            var self = this;

            self.saving = true;
            self.errors = [];

            if (this.isSettings) {
                var url = cp_url('settings/') + this.slug;
            } else if (this.isAddon) {
                var url = cp_url('addons/') + this.extra.addon + '/settings';
            } else {
                var url = cp_url('publish');
            }

            var request = this.$http.post(url, this.formData)

            request.success(function(data) {
                self.loading = false;

                if (data.success) {
                    window.location = data.redirect;
                } else {
                    this.errors = data.errors;
                }
            });

            request.error(function(data) {
                alert('There was a problem saving the data. Please check your logs.');
            });
        },

        publishAndContinue: function() {
            this.formData.continue = true;
            this.publish();
        },

        editLayout: function(status) {
            this.$event.preventDefault();
            this.editingLayout = status;
        },

        /**
         * Trigger saving of the fieldset layout.
         */
        saveLayout: function() {
            // This will get picked up by the child `publish-fieldset` component.
            this.$broadcast('saveLayout');

            this.editingLayout = false;
        },

        initPreview: function() {
            var $iframe = $('<iframe frameborder="0" id="sneak-peek-iframe">').appendTo('#sneak-peek');
            var iframe = $iframe.get(0);
            this.updatePreview();
        },

        updatePreview: _.debounce(function(e) {
            if (this.iframeLoading) {
                this.previewRequestQueued = true;
                return;
            }

            var formData = this.formData;
            formData['preview'] = true;

            this.iframeLoading = true;

            if (! this.isNew) {
                // existing pages already have a url.
                var url = this.url;
            } else {
                if (this.isPage) {
                    var slug = this.formData.slug || 'new-page';
                    var url = this.extra.parent_url + '/' + slug;
                    url = url.replace('//', '/');
                } else {
                    var url = this.entryUrl();
                }
            }

            this.$http.post(url, formData, function(data, status, request) {
                this.updatePreviewIframe(data);
                this.iframeLoading = false;
                if (this.previewRequestQueued) {
                    this.previewRequestQueued = false;
                    this.updatePreview();
                }
            });
        }, 150),

        updatePreviewIframe: function(data) {
            var $iframe = $('#sneak-peek-iframe');
            var iframe = $iframe.get(0);

            var scrollX = $(iframe.contentWindow.document).scrollLeft();
            var scrollY = $(iframe.contentWindow.document).scrollTop();

            data += '<script type="text/javascript">window.scrollTo('+scrollX+', '+scrollY+');\x3c/script>';

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(data);
            iframe.contentWindow.document.close();
        },

        syncTitleAndSlugFields: function() {
            if (this.isNew) {
                this.$watch('formData.fields.title', function(title) {
                    if (! this.isSlugModified) {
                        this.formData.slug = this.$slugify(title);
                    }
                });
            }
        },

        entryUrl: function () {
            if (! this.isNew) {
                return this.url;
            }

            var fallbackSlug = (this.isTaxonomy) ? 'new-term' : 'new-entry';
            var slug = this.formData.slug || fallbackSlug;

            var route = this.extra.route;
            var url = route;

            if (this.extra.order_type === 'date') {
                var date = this.date();
            }

            var re = /{\s*([a-zA-Z0-9_\-]+)\s*}/g;
            var results;
            while ((results = re.exec(route)) !== null) {
                var match = results[0];
                var value = '';

                switch (match) {
                    case '{year}':
                        value = date.format('YYYY');
                        break;
                    case '{month}':
                        value = date.format('MM');
                        break;
                    case '{day}':
                        value = date.format('DD');
                        break;
                    case '{slug}':
                        value = slug;
                        break;
                    default:
                        var field = match.substring(1, match.length-1);
                        value = this.formData.fields[field];
                        break;
                }

                url = url.replace(match, value);
            }

            return url;
        },

        date: function () {
            var date = this.extra.datetime;

            var format = 'YYYY-MM-DD';

            if (date.length > 10) {
                format += ' HH:mm';
            }

            return moment(date, format);
        }

    },

    ready: function() {
        var self = this;

        // `contentData` is passed as json string prop, we need to parse it.
        this.contentData = JSON.parse(this.contentData);
        // `extra` is passed as json string prop, we need to parse it.
        this.extra = JSON.parse(this.extra);
        // `locales` is passed as json string prop, we need to parse it.
        this.locales = (this.locales) ? JSON.parse(this.locales) : null;

        this.initFormData();

        this.syncTitleAndSlugFields();

        var sneakPeekWatcher = null;
        if (this.shouldShowSneakPeek) {
            this.$root.isPublishPage = true;

            // We've initated Live Preview Mode
            this.$on('previewing', function() {
                this.initPreview();

                sneakPeekWatcher = this.$watch('formData', function(newVal) {
                    this.updatePreview();
                }, { deep: true });
            });
        }

        this.$on('previewing.stopped', function() {
            // The watcher returns a method to stop itself.
            sneakPeekWatcher();
        });

        this.$on('fieldsetLoaded', function(fieldset) {
            this.fieldset = fieldset;
        });

        Mousetrap.bindGlobal('mod+s', function(e) {
            e.preventDefault();
            self.publishAndContinue();
        });

        Mousetrap.bindGlobal(['meta+enter','meta+return'], function(e) {
            e.preventDefault();
            self.publish();
        });
    }

};
