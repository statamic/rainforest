module.exports = {

    template: '<div class="taxonomy-fieldtype"><relate-fieldtype :data.sync="data" :name="name" :config="config"></relate-fieldtype></div>',

    props: ['data', 'config', 'name']

};
