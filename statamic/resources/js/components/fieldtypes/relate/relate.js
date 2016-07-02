module.exports = {

    template: require('./relate.template.html'),

    props: ['data', 'config', 'name', 'suggestionsProp'],

    data: function() {
        return {
            loading: true,
            suggestions: [],
            search: null,
            active: -1
        }
    },

    computed: {

        availableSuggestions: function() {
            var self = this;

            return _.reject(self.suggestions, function(suggestion) {
                var hasBeenSelected = _.contains(self.data, suggestion.value);

                var matchesSearchTerm = true;
                if (self.search) {
                    matchesSearchTerm = suggestion.text.toLowerCase().indexOf(self.search.toLowerCase()) !== -1;
                }

                return hasBeenSelected || !matchesSearchTerm;
            });
        },

        selected: function() {
            var self = this;

            return _.map(self.data, function(item) {
                return _.findWhere(self.suggestions, { value: item });
            });
        },

        suggestFieldSuggestions: function() {
            var suggestions = this.suggestions;

            suggestions.unshift({ value: null, text: '' });

            return suggestions;
        },

        suggestFieldConfig: function () {
            return {
                max_items: 1
            }
        },

        suggestFieldData: {
            get: function () {
                return [this.data];
            },
            set: function (arr) {
                this.data = (arr) ? arr[0] : null;
            }
        },

        single: function () {
            return this.maxItems === 1;
        },

        maxItems: function() {
            return this.config.max_items;
        },

        maxSelected: function() {
            if (this.maxItems) {
                return this.data.length >= this.config.max_items;
            } else {
                return false;
            }
        }
    },

    methods: {

        getSuggestions: function() {
            if (this.suggestionsProp) {
                this.suggestions = this.suggestionsProp;
                this.removeInvalidData();
                this.loading = false;
                this.$nextTick(function() {
                    this.initSortable();
                });
            } else {
                this.$http.post(cp_url('addons/suggest/suggestions'), this.config, function(data) {
                    this.suggestions = data;
                    this.removeInvalidData();
                    this.loading = false;
                    this.$nextTick(function() {
                        this.initSortable();
                    });
                });
            }
        },

        initSortable: function() {
            var self = this;

            self.getSortable().sortable({
                axis: 'y',
                placeholder: 'item-placeholder',
                forcePlaceholderSize: true,
                revert: 175,
                start: function(e, ui) {
                    ui.item.data('start', ui.item.index())
                },
                update: function(e, ui) {
                    var start = ui.item.data('start'),
                        end   = ui.item.index();

                    self.data.splice(end, 0, self.data.splice(start, 1)[0]);
                }
            });
        },

        getSortable: function() {
            return $(this.$el).find('.pane-selections .relate-items');
        },

        select: function(item) {
            if (! this.maxSelected) {
                this.data.push(item.value);
            }
        },

        remove: function(item) {
            var index = _.indexOf(this.data, item.value);
            this.data.splice(index, 1);
        },

        goUp: function() {
            this.active--;

            if (this.active < 0) {
                this.active = 0;
            }
        },

        goDown: function() {
            this.active++;

            if (this.active >= this.availableSuggestions.length-1) {
                this.active = this.availableSuggestions.length-1;
            }
        },

        selectActive: function() {
            var item = this.availableSuggestions[this.active];
            this.select(item);

            if (this.active >= this.availableSuggestions.length) {
                this.active = this.availableSuggestions.length-1;
            }
        },

        createItem: function() {
            console.log('creating item');
        },

        /**
         * Remove data that doesn't exist in the suggestions.
         *
         * These may be entries that have been deleted, for example.
         */
        removeInvalidData: function () {
            var self = this;

            if (self.single) {
                if (! _.findWhere(self.suggestions, { value: self.data })) {
                    self.data = null;
                }
            } else {
                self.data = _.filter(self.data, function (item) {
                    return _.findWhere(self.suggestions, { value: item });
                });
            }
        }

    },

    ready: function() {
        if (!this.data) {
            this.data = [];
        }

        this.getSuggestions();

        this.$watch('suggestionsProp', function(suggestions) {
            this.suggestions = suggestions;
        });

        this.$watch('search', function() {
            if (this.availableSuggestions.length <= this.active) {
                this.active = this.availableSuggestions.length-1;
            }
        });

        this.$watch('data', function() {
            this.$nextTick(function() {
                this.getSortable().sortable('refresh');
            });
        })
    }

};
