module.exports = {

    template: require('./toggle.template.html'),

    props: ['name', 'data', 'config'],

    computed: {

        isOn: function () {
            return this.data === true;
        }

    },

    methods: {

        toggle: function () {
            this.data = !this.data;
        }

    }
};