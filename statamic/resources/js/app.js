var $ = require('jquery');
var Mousetrap = require('mousetrap');

Vue.config.debug = true;

// Vue filters, component, fieldtypes, etc, will need to be made globally available
// so that child components and third party components will have access to them.
require('./app-globals');

var vm = new Vue({
    el: '#statamic',

    data: {
        isPublishPage: false,
        isPreviewing: false,
        showShortcuts: false,
        navVisible: false,
        version: Statamic.version
    },

    components: {
        'asset-browser': require('./components/assets/browser/browser'),
        'page-tree': require('./components/page-tree/page-tree'),
        'publish': require('./components/publish/publish'),
        'fieldset-builder': require('./components/fieldset-builder/builder'),
        'formset-builder': require('./components/formset-builder/formset-builder'),
        'typeahead': require('./components/typeahead/typeahead'),
        'installer': require('./components/installer/installer'),
        'updater': require('./components/updater'),
        'importer': require('./components/importer/importer'),
        'addon-listing': require('./components/listings/addons'),
        'entry-listing': require('./components/listings/entries'),
        'collection-listing': require('./components/listings/collections'),
        'configure-collection-listing': require('./components/listings/collections-configure'),
        'term-listing': require('./components/listings/terms'),
        'taxonomies-listing': require('./components/listings/taxonomies'),
        'configure-taxonomies-listing': require('./components/listings/taxonomies-configure'),
        'globals-listing': require('./components/listings/globals'),
        'configure-globals-listing': require('./components/listings/globals-configure'),
        'asset-container-listing': require('./components/listings/asset-containers'),
        'configure-asset-container-listing': require('./components/listings/asset-containers-configure'),
        'user-listing': require('./components/listings/users'),
        'user-group-listing': require('./components/listings/user-groups'),
        'user-role-listing': require('./components/listings/user-roles'),
        'fieldset-listing': require('./components/listings/fieldsets'),
        'form-submission-listing': require('./components/listings/form-submissions'),
        'asset-container-form': require('./components/assets/forms/container'),
        'roles': require('./components/roles/roles'),
        'login': require('./components/login/login')
    },

    computed: {
        showPage: function() {
            return !this.hasSearchResults;
        },

        hasSearchResults: function() {
            return this.$refs.search.hasItems;
        }
    },

    methods: {
        preview: function() {
            var self = this;
            self.$broadcast('previewing');
            self.isPreviewing = true;

            $('.sneak-peek-viewport').addClass('on');

            setTimeout(function() {
                $(self.$el).addClass('sneak-peeking');
                $('#sneak-peek').find('iframe').show();
                setTimeout(function() {
                    $(self.$el).addClass('sneak-peek-editing');
                }, 200);
            }, 200);
        },

        stopPreviewing: function() {
            var self = this;
            var $viewport = $('.sneak-peek-viewport');
            var $icon = $viewport.find('.icon');

            $(self.$el).removeClass('sneak-peek-editing');
            $('#sneak-peek').find('iframe').fadeOut().remove();
            $icon.hide();
            setTimeout(function() {
                $(self.$el).removeClass('sneak-peeking');
                $viewport.removeClass('on');
                setTimeout(function(){
                    $icon.show();
                    self.isPreviewing = false;
                    self.$broadcast('previewing.stopped');
                }, 200);
            }, 500);
        },

        toggleNav: function () {
            this.navVisible = !this.navVisible;
        }
    },

    ready: function() {
        Mousetrap.bind(['/', 'ctrl+f'], function(e) {
            $('#global-search').focus();
        }, 'keyup');

        Mousetrap.bind('?', function(e) {
            this.showShortcuts = true;
        }.bind(this), 'keyup');
    }
});
