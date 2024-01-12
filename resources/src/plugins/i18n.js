import Vue from 'vue';
import VueI18n from 'vue-i18n';
import messages from '../translations';

Vue.use(VueI18n);

const storedLocale = window.localStorage.getItem('storedLocale');
export const i18n = new VueI18n({
  locale: storedLocale || 'en',
  fallbackLocale: 'en',
  messages,
});
