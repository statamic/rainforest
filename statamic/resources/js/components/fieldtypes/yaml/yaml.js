var CodeMirror = require('codemirror');
require('codemirror/mode/yaml/yaml');

module.exports = {

    template: `
        <div class="yaml-field">
            <span>YAML</span>
            <div class="editor" v-el:codemirror></div>
        </div>
    `,

    props: ['name', 'data', 'config'],

    ready: function() {
        var self = this;

        var cm = CodeMirror(this.$els.codemirror, {
            value: this.data || '',
            mode: 'yaml',
            lineNumbers: true,
            viewportMargin: Infinity
        });

        cm.on('change', function (cm) {
            self.data = cm.doc.getValue();
        });
    }

};
