require('./bootstrap');
window.Vue = require('vue');
// BOOTSTRAP
import BootstrapVue from "bootstrap-vue";
import "bootstrap-vue/dist/bootstrap-vue.css";
Vue.use(BootstrapVue);
// STORE
import store from './store/index';
// ROUTES
import router from './routes/index';
// COMPONENTS
import App from './components/App';
Vue.component('admin', require('./components/App').default);
// VALIDATE
import { ValidationProvider, ValidationObserver } from 'vee-validate';
Vue.component('ValidationProvider', ValidationProvider);
Vue.component('ValidationObserver', ValidationObserver);
import { extend } from 'vee-validate';
import * as rules from 'vee-validate/dist/rules';
import { messages } from 'vee-validate/dist/locale/es.json';
Object.keys(rules).forEach(rule => {
    extend(rule, {
        ...rules[rule],
        message: messages[rule]
    });
});
// PLUGIN QR
import QrcodeVue from 'qrcode.vue';
Vue.component('qrcode-vue', QrcodeVue);
// DATA TABLE
import VueGoodTablePlugin from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.css';
Vue.use(VueGoodTablePlugin);
// PLUGIN
import plugin from './plugins/index';
// Vue.config.productionTip = false;
Vue.use(plugin);
import api from './api'
Vue.prototype.$api = api

const VueSelect =  require("vue-select2");

import vSelect from 'vue-select'
Vue.component('v-select', vSelect)
// EXPORT
const app = new Vue({
    el: '#app',
    components: { App, },
    router,
    store,
});
