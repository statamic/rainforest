module.exports = {

    template: require('./theme.template.html'),

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            loading: true,
            options: {}
        }
    },

    computed: {
        selectConfig: function() {
            return {
                options: this.options
            };
        }
    },

    ready: function() {
        this.$http.get(cp_url('system/themes/get'), function(data) {
            var options = [];
            _.each(data, function(theme) {
                options.push({
                    value: theme.folder,
                    text: theme.name
                });
            });
            this.options = options;
            this.loading = false;
        });
    }

};
