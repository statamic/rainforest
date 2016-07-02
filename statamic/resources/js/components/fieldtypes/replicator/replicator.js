var Vue = require('vue');

module.exports = {

    template: require('./replicator.template.html'),

    props: ['name', 'data', 'config'],

    data: function() {
        return {
            blank: {},
            sortableOptions: {}
        };
    },

    computed: {
        hasData: function() {
            return this.data !== null && this.data.length;
        }
    },

    ready: function() {
        // Initialize with an empty array if there's no data.
        if (! this.data) {
            this.data = [];
        }

        this.sortable();
    },

    methods: {

        sortable: function() {
            var self = this;
            var start = '';

            $(this.$el).find('.replicator-sets').sortable({
                axis: "y",
                revert: 175,
                placeholder: 'stacked-placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,
                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },
                update: function(e, ui) {
                    var end  = ui.item.index();

                    // Make a local copy and reorder
                    var data = JSON.parse(JSON.stringify(self.data));
                    data.splice(end, 0, data.splice(start, 1)[0]);

                    self.data = data;
                }
            });
        },

        setConfig: function(type) {
            return _.findWhere(this.config.sets, { name: type });
        },

        deleteSet: function(set) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                self.data.splice(set.$index, 1);
            });
        },

        addSet: function(type) {
            var newSet = { type: type };

            // Get nulls for all the set's fields so Vue can track them more reliably.
            var set = this.setConfig(type);
            _.each(set.fields, function(field) {
                newSet[field.name] = field.default || Statamic.fieldtypeDefaults[field.type] || null;
            });

            var index = this.data.length;
            this.data.$set(index, newSet);

            this.sortable();
        },

        toggle: function(set) {
            var hidden = set['#hidden'] || false;
            Vue.set(set, '#hidden', !hidden);
        },

        isHidden: function(set) {
            return set['#hidden'];
        },

        /**
         * Bootstrap Column Width class
         * Takes a percentage based integer and converts it to a bootstrap column number
         * eg. 100 => 12, 50 => 6, etc.
         */
        colClass: function(width) {
            if (this.$root.isPreviewing) {
                return 'col-md-12';
            }

            width = width || 100;
            return 'col-md-' + Math.round(width / 8.333);
        }
    }
};
