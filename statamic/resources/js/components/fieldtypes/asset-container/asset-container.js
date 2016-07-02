module.exports = {

    template: require('./asset-container.template.html'),

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
        },

        allowBlank: function() {
            return this.config && this.config.allow_blank;
        }
    },

    ready: function() {
        this.$http.get(cp_url('assets/containers/get'), function(data) {
            var options = (this.allowBlank) ? [{ value: null, text: '', }] : [];

            _.each(data.items, function(container) {
                options.push({
                    value: container.id,
                    text: container.title
                });
            });
            this.options = options;
            this.loading = false;

            if (!this.data) {
                this.data = options[0].value;
            }
        });
    }

};
