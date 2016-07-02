module.exports = {

    template: require('./selector.template.html'),

    props: {
        show: {
            type: Boolean,
            default: false,
            twoWay: true
        },
        container: String,
        folder: String,
        selected: Array,
        viewMode: String
    },

    data: function() {
        return {
            loading: true,
            showListing: true
        }
    },

    methods: {

        select: function() {
            this.$dispatch('assets.selected', this.selected);
            this.close();
        },

        close: function() {
            this.show = false;
        }

    },

    ready: function() {
        this.$on('asset-listing.loading-complete', function() {
            this.loading = false;
        });
    },

    events: {
        // A folder was selected to navigate to in the listing.
        'path.updated': function (path) {
            // We'll stop showing the listing, update the path, then re-show the listing.
            // This will force the listing component to refresh with the new path.
            // It's the simplest solution for now to allow folder navigation.
            this.showListing = false;
            this.folder = path;
            this.$nextTick(function () {
                this.showListing = true;
            })
        }
    }

};
