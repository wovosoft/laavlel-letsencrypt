import {createI18n, type I18nOptions} from 'vue-i18n';

import bn from "./bn.json";
import en from "./en.json";

export type MessageSchemaBn = typeof en;
export type MessageSchemaEn = typeof en;
export type AvailableLanguagesType = 'bn' | 'en';

const options: I18nOptions = {
    legacy: false,
    locale: localStorage.getItem('locale') || 'bn',
    fallbackLocale: 'en',
    messages: {
		bn,
		en
    },
}

export default createI18n<false, typeof options>(options);