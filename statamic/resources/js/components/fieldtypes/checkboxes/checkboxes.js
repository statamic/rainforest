module.exports = {

    template: require('./checkboxes.template.html'),

    props: ['name', 'data', 'config'],

    ready: function() {
        if (typeof this.config === 'string') {
            this.config = JSON.parse(this.config);
        }

        if ( ! this.data) {
            this.data = [];
        }
    }
};