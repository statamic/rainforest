module.exports = {

    template: require('./user-roles.template.html'),

    props: ['data', 'config', 'name'],

    data: function() {
        return {
            loading: true,
            roles: {}
        };
    },

    computed: {

        checkboxesConfig: function() {
            return { options: this.roles };
        },

        canEdit: function() {
            return Vue.can('user-roles:manage');
        },

        selectedRoleNames: function() {
            var self = this;
            return _.map(this.data, function(id) {
                return _.findWhere(self.roles, { value: id }).text;
            });
        }

    },

    methods: {

        getRoles: function() {
            this.$http.get(cp_url('users/roles/get'), function(data) {
                var roles = [];
                _.each(data.items, function(role) {
                    roles.push({
                        value: role.id,
                        text: role.title
                    });
                });

                this.roles = roles;
                this.loading = false;
            });
        }

    },

    ready: function() {
        this.getRoles();
    }

};
