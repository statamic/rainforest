module.exports = {
    template: `
        <input type="text" v-model="data" />
    `,

    props: ['data', 'config', 'name']
};
