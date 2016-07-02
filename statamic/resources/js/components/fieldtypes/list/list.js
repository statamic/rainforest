module.exports = {

    template: require('./list.template.html'),

    props: ['name', 'data', 'config'],

    data: function () {
        return {
            newItem: '',
            editing: null
        }
    },

    methods: {
        addItem: function() {
            // Blank items are losers.
            if (this.newItem !== '') {
                this.data.push(this.newItem);
                this.newItem = '';
                this.editing = this.data.length;
            }

        },

        editItem: function(index) {
            event.preventDefault();
            
            this.editing = index;
            
            // Async is good times.
            this.$nextTick(function () {
                $(this.$el).find('.editing input').focus().select();
            });
        },

        goUp: function() {
            if (this.editing > 0) {
                this.editing = this.editing - 1;
                this.$nextTick(function () {
                    $(this.$el).find('.editing input').focus().select();
                });
            }
        },

        goDown: function() {
            
            // Check if we're at the last one
            if (this.editing === this.data.length - 1) {
                this.editing = this.data.length;
                $(this.$el).find('.new-item').focus();
            } else {
                this.editing = this.editing + 1;
                this.$nextTick(function () {
                    $(this.$el).find('.editing input').focus().select();
                });
            }
        },

        updateItem: function(value, index, event) {
            event.preventDefault();

            // Let's remove blank items
            if (value == '') {
                this.data.$remove(index);
            } else {
                this.data[index] = value;
            }

            this.editing = this.data.length;

            // Back to adding new items.
            $(this.$el).find('.new-item').focus();

        },

        deleteItem: function(item) {
            this.data.$remove(item);
        }
    },

    ready: function() {
        var self = this,
            start = '';

        if ( ! this.data) {
            this.data = [];
        }

        $(this.$el).sortable({
            axis: "y",
            revert: 175,
            items: '> li:not(:last-child)',

            start: function(e, ui) {
                start = ui.item.index();
            },
            
            update: function(e, ui) {
                var end  = ui.item.index(),
                    swap = self.data.splice(start, 1)[0];
                
                self.data.splice(end, 0, swap);
            }
        });
    }
};