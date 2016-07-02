module.exports = {

    template: require('./user-groups.template.html'),

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            loading: true,
            groups: {}
        };
    },

    computed: {

        checkboxesConfig: function() {
            return { options: this.groups };
        }

    },

    methods: {

        getGroups: function() {
            this.$http.get(cp_url('/users/groups'), function(data) {
                var groups = {};
                _.each(data, function(group, id) {
                    groups[id] = group.title;
                });

                this.groups = groups;
                this.loading = false;
            });
        }

    },

    ready: function() {
        this.getGroups();
    }

};
