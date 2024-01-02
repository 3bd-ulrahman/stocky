import store from "./store";
import Vue from "vue";
import router from "./router";
import { ValidationObserver, ValidationProvider, extend, localize } from 'vee-validate';
import * as rules from "vee-validate/dist/rules";
import BootstrapVue from 'bootstrap-vue/dist/bootstrap-vue.esm';
import "./assets/styles/sass/themes/lite-purple.scss";
import Meta from "vue-meta";
import { i18n } from "./plugins/i18n";
import VuePerfectScrollbar from 'vue-perfect-scrollbar'


window.axios = require('axios');
window.axios.defaults.baseURL = '';
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Vue.use(BootstrapVue);
Vue.use(Meta, {
  keyName: "metaInfo",
  attribute: "data-vue-meta",
  ssrAttribute: "data-vue-meta-server-rendered",
  tagIDKeyName: "vmid",
  refreshOnceOnNavigation: true
});

Vue.component("large-sidebar", () => import("./containers/layouts/largeSidebar"));
Vue.component("customizer", () => import("./components/common/customizer.vue"));
Vue.component("vue-perfect-scrollbar", VuePerfectScrollbar);
Vue.component("ValidationObserver", ValidationObserver);
Vue.component('ValidationProvider', ValidationProvider);

localize({
  en: {
    messages: {
      required: 'This field is required',
      required_if: 'This field is required',
      regex: 'This field must be a valid',
      mimes: `This field must have a valid file type.`,
      size: (_, { size }) => `This field size must be less than ${size}.`,
      min: 'This field must have no less than {length} characters',
      max: (_, { length }) => `This field must have no more than ${length} characters`
    }
  },
});
// Install VeeValidate rules and localization
Object.keys(rules).forEach(rule => {
  extend(rule, rules[rule]);
});

// Register it globally




axios.interceptors.response.use((response) => {

  return response;
}, (error) => {
  if (error.response && error.response.data) {
    if (error.response.status === 401) {
      window.location.href = '/login';
    }

    if (error.response.status === 404) {
      router.push({ name: 'NotFound' });
    }
    if (error.response.status === 403) {
      router.push({ name: 'not_authorize' });
    }

    return Promise.reject(error.response.data);
  }
  return Promise.reject(error.message);
});

window.Fire = new Vue();

Vue.component('login-component', require('./views/app/sessions/signIn.vue').default);
Vue.component('forgot-component', require('./views/app/sessions/forgot.vue').default);
Vue.component('reset-component', require('./views/app/sessions/reset.vue').default);

Vue.config.productionTip = true;
Vue.config.silent = true;
Vue.config.devtools = false;

var login = new Vue({
  el: '#login',
  store,
  i18n,
  router: router,
});
