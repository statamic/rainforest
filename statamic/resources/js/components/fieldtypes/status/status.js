// @TODO: connect/move to a publish "controller" and hook up translations

module.exports = {

    template: require('./status.template.html'),

    props: ['selected', 'translations'],

    data: function () {
        return {
            options: [
                {text: 'Live', value: 'live'},
                {text: 'Hidden', value: 'hidden'},
                {text: 'Draft', value: 'draft'}
            ]
        }
    }
}