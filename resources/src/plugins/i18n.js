import Vue from 'vue';
import VueI18n from 'vue-i18n';
import messages from '../translations';

Vue.use(VueI18n);

export const i18n = async function () {
  const store = await import('../store');

  return new VueI18n({
    locale: store.default.state.language.language,
    fallbackLocale: 'en',
    messages,
  });
};
