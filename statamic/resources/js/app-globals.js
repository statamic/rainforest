// Plugins
Vue.use(require('./plugins/translate'));
Vue.use(require('./plugins/cp_url'));
Vue.use(require('./plugins/resource_url'));
Vue.use(require('./plugins/can'));
Vue.use(require('./plugins/slugify'));

// Filters
Vue.filter('pre', require('./filters/pre'));
Vue.filter('reverse', require('./filters/reverse'));
Vue.filter('pluck', require('./filters/pluck'));
Vue.filter('parse', require('./filters/parse'));
Vue.filter('optionize', require('./filters/optionize'));
Vue.filter('markdown', require('./filters/markdown'));
Vue.filter('caseInsensitiveOrderBy', require('./filters/orderby'));

// Mixins
window.Dossier = require('./components/dossier/dossier');

// Components
Vue.component('list', require('./components/list'));
Vue.component('alert', require('./components/alert'));
Vue.component('asset-editor', require('./components/assets/modals/asset-editor'));
Vue.component('asset-folder-editor', require('./components/assets/modals/folder-editor'));
Vue.component('asset-listing', require('./components/assets/listing/listing'));
Vue.component('branch', require('./components/page-tree/branch'));
Vue.component('branches', require('./components/page-tree/branches'));
Vue.component('set-builder', require('./components/fieldset-builder/set-builder'));
Vue.component('fields-builder', require('./components/fieldset-builder/fields-builder'));
Vue.component('field-settings', require('./components/fieldset-builder/field-settings'));
Vue.component('fieldset-fields', require('./components/fieldset-builder/fieldset-fields'));
Vue.component('fieldtype-selector', require('./components/fieldset-builder/fieldtype-selector'));
Vue.component('modal', require('./components/modal/modal'));

// Fieldtypes
Vue.component('array-fieldtype', require('./components/fieldtypes/array/array'));
Vue.component('assets-fieldtype', require('./components/fieldtypes/assets/assets'));
Vue.component('asset_container-fieldtype', require('./components/fieldtypes/asset-container/asset-container'));
Vue.component('asset_folder-fieldtype', require('./components/fieldtypes/asset-folder/asset-folder'));
Vue.component('checkboxes-fieldtype', require('./components/fieldtypes/checkboxes/checkboxes'));
Vue.component('collection-fieldtype', require('./components/fieldtypes/collection/collection'));
Vue.component('date-fieldtype', require('./components/fieldtypes/date/date'));
Vue.component('fieldset-fieldtype', require('./components/fieldtypes/fieldset/fieldset'));
Vue.component('grid-fieldtype', require('./components/fieldtypes/grid/grid'));
Vue.component('hidden-fieldtype', require('./components/fieldtypes/hidden/hidden'));
Vue.component('list-fieldtype', require('./components/fieldtypes/list/list'));
Vue.component('locale_settings-fieldtype', require('./components/fieldtypes/locale-settings/locale-settings'));
Vue.component('markdown-fieldtype', require('./components/fieldtypes/markdown/markdown'));
Vue.component('pages-fieldtype', require('./components/fieldtypes/pages/pages'));
Vue.component('radio-fieldtype', require('./components/fieldtypes/radio/radio'));
Vue.component('redactor-fieldtype', require('./components/fieldtypes/redactor/redactor'));
Vue.component('redactor_settings-fieldtype', require('./components/fieldtypes/redactor/settings'));
Vue.component('relate-fieldtype', require('./components/fieldtypes/relate/relate'));
Vue.component('replicator-fieldtype', require('./components/fieldtypes/replicator/replicator'));
Vue.component('section-fieldtype', require('./components/fieldtypes/section/section'));
Vue.component('select-fieldtype', require('./components/fieldtypes/select/select'));
Vue.component('status-fieldtype', require('./components/fieldtypes/status/status'));
Vue.component('suggest-fieldtype', require('./components/fieldtypes/suggest/suggest'));
Vue.component('table-fieldtype', require('./components/fieldtypes/table/table'));
Vue.component('tags-fieldtype', require('./components/fieldtypes/tags/tags'));
Vue.component('taxonomy-fieldtype', require('./components/fieldtypes/taxonomy/taxonomy'));
Vue.component('template-fieldtype', require('./components/fieldtypes/template/template'));
Vue.component('theme-fieldtype', require('./components/fieldtypes/theme/theme'));
Vue.component('text-fieldtype', require('./components/fieldtypes/text/text'));
Vue.component('textarea-fieldtype', require('./components/fieldtypes/textarea/textarea'));
Vue.component('time-fieldtype', require('./components/fieldtypes/time/time'));
Vue.component('title-fieldtype', require('./components/fieldtypes/title/title'));
Vue.component('toggle-fieldtype', require('./components/fieldtypes/toggle/toggle'));
Vue.component('users-fieldtype', require('./components/fieldtypes/users/users'));
Vue.component('user_groups-fieldtype', require('./components/fieldtypes/user-groups/user-groups'));
Vue.component('user_roles-fieldtype', require('./components/fieldtypes/user-roles/user-roles'));
Vue.component('yaml-fieldtype', require('./components/fieldtypes/yaml/yaml'));

// Directives
Vue.directive('elastic', require('./directives/elastic'));
