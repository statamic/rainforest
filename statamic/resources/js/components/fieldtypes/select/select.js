module.exports = {

    props: ['name', 'data', 'config', 'options'],

    template: require('./select.template.html'),

    data: function() {
        return {
            keyed: false,
            selectOptions: []
        }
    },

    ready: function() {
        if (this.options) {
            this.selectOptions = this.options;
        } else {
            this.selectOptions = this.config.options;
        }
    },

    computed: {
        label: function() {
            var option = _.findWhere(this.selectOptions, {value: this.data});
            return (option) ? option.text : this.data;
        }
    }
};
