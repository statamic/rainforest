module.exports = {

    props: ['data', 'config', 'name'],

    template: '<input type="hidden" :name="name" v-model="data" />'

};
