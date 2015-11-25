import { Reducer } from "Ampliflux/reducers";

// TODO this should be in DeskPRO/Component/Ampliflux/common/components

export default class TranslationReducer extends Reducer {
  constructor() {
    super();

    this.locales = this.getLocales();

    this.defaultLocale = "en-US";
  }

  getLocales() {
    return [];
  }

  getTranslations(locale) {
    let translation = this.locales.reduce(
      (prev, current) => {
        let locales = current.locales;
        if(!typeof(current.locales) == 'Array') {
          locales = [current.locales];
        }

        for(let k in current.locales) {
          if(current.locales[k] == locale) {
            return current;
          }
        }
        return prev;
      }
    );

    if(!translation) {
      return this.getTranslations(this.defaultLocale);
    }

    return translation;
  }

  getInitialState() {
    return this.getTranslations(this.defaultLocale);
  }

  changeLocale(state, action) {
    const new_translations = this.getTranslations(action.payload);
    return new_translations ? new_translations : state;
  }
}
