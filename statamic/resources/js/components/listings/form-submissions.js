module.exports = {

    mixins: [Dossier],

    props: ['get'],

    data: function() {
        return {
            ajax: {
                get: this.get
            },
            tableOptions: {
                checkboxes: false,
                sort: 'datestamp',
                sortOrder: 'desc',
                partials: {
                    cell: `
                        <a v-if="$index === 0" :href="item.edit_url">
                            {{ item[column.label] }}
                        </a>
                        <template v-else>
                            {{{ item[column.label] }}}
                        </template>`
                }
            }
        }
    }

};
