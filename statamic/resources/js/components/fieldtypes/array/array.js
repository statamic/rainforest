module.exports = {

    template: require('./array.template.html'),

    props: ['name', 'data', 'config'],

    ready: function() {
        this.data = this.data || [];

        if (this.componentType === 'keyed') {
            this.data = (this.data.length === 0) ? {} : this.data;
        }

        if (this.componentType === 'dynamic') {
            this.initSortable();
        }
    },

    computed: {
        componentType: function() {
            return (this.config.keys) ? 'keyed' : 'dynamic';
        },

        hasRows: function() {
            return this.data && this.data.length > 0;
        },

        addRowButton: function() {
            return this.config.add_row || translate_choice('cp.add_row', 1);
        },

        valueHeader: function() {
            return this.config.value_header || 'Value';
        },

        textHeader: function() {
            return this.config.text_header || 'Text';
        }
    },

    methods: {
        addRow: function() {
            this.data.push({ value: '', text: '' });
        },

        deleteRow: function(index) {
            this.data.splice(index, 1);
        },

        initSortable: function() {
            var self = this;
            var start = '';

            $(this.$els.tbody).sortable({
                axis: "y",
                revert: 175,
                handle: '.drag-handle',
                placeholder: 'table-row-placeholder',
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },

                update: function(e, ui) {
                    var end  = ui.item.index(),
                        swap = self.data.splice(start, 1)[0];

                    self.data.splice(end, 0, swap);
                }
            });
        }
    }

};
