module.exports = {

    template: require('./create-page.template.html'),

    data: function() {
        return {
            parent: null,
            show: false,
            saving: false,
            loading: true,
            fieldsets: []
        }
    },

    events: {
        'pages.create': function(parent) {
            this.loading = true;
            this.show = true;
            this.parent = parent;
            this.getFieldsets();
        }
    },

    methods: {
        cancel: function() {
            this.show = false;
        },

        create: function(fieldset) {
            window.location = cp_url('pages/create/'+this.parent+'?fieldset='+fieldset);
        },

        getFieldsets: function() {
            var url = cp_url('fieldsets/get?url='+this.parent+'&hidden=false');

            this.$http.get(url, function(data) {
                var fieldsets = [];

                _.each(data.items, function(fieldset) {
                    fieldsets.push({
                        value: fieldset.uuid,
                        text: fieldset.title
                    });
                });

                // Ensure there is a default
                if (! _.findWhere(fieldsets, { value: 'default' })) {
                    fieldsets.push({ value: 'default', text: 'Default' });
                }

                // Sort alphabetically
                fieldsets = _.sortBy(fieldsets, function (fieldset) {
                    return fieldset.text;
                });

                this.fieldsets = fieldsets;
                this.loading = false;
            });
        }
    }

};
