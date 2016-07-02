module.exports = {

    template: require('./template.template.html'),

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
        this.$http.get(cp_url('system/templates/get'), function(data) {
            var options = [{ value: null, text: '' }];
            _.each(data, function(template) {
                options.push({
                    value: template,
                    text: template
                });
            });
            this.options = options;
            this.loading = false;
        });
    }

};
