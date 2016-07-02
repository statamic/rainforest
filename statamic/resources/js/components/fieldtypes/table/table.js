module.exports = {

    template: require('./table.template.html'),

    props: ['name', 'data', 'config'],

    data: function () {
        return {
            max_rows: this.config.max_rows || null,
            max_columns: this.config.max_columns || null
        }
    },

    computed: {
    	columnCount: function() {
            if (! this.data) {
                return 0;
            }

            if (this.data[0]) {
                return this.data[0].cells.length;
            }

            return 0;
    	},

        rowCount: function() {
            if (! this.data) {
                return 0;
            }

            if (this.data.length) {
                return this.data.length;
            }

            return 0;
        },

        canAddRows: function() {
            if (this.max_rows) {
                return this.rowCount < this.max_rows;
            }

            return true;
        },

        canAddColumns: function() {
            if (this.rowCount || this.columnCount) {

                if (this.max_columns) {
                    return this.columnCount < this.max_columns;
                }

                return true;
            }

            return false;
        }
    },

    methods: {
    	addRow: function() {
            // If there are no columns, we will add one when we add a row.
            var count = (this.columnCount === 0) ? 1 : this.columnCount;

            this.data.push({
                cells: new Array(count)
            });
    	},

    	addColumn: function() {
            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].cells.push('');
            }
    	},

        deleteRow: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                self.data.splice(index, 1);
            });
        },

        deleteColumn: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                text: translate('cp.confirm_delete_item'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                var rows = self.data.length;

                for (var i = 0; i < rows; i++) {
                    self.data[i].cells.splice(index, 1);
                }
            });
        }
    },

    ready: function() {
        var self = this,
            start = '';

        if ( ! this.data) {
            this.data = [];
        }

        $(this.$el).find('tbody').sortable({
            axis: "y",
            revert: 175,
            handle: '.drag-handle',
            placeholder: "table-row-placeholder",
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
