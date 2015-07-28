import { Reducer } from "Ampliflux/reducers";

export default class Translations extends Reducer {
  constructor() {
    super();
    
    this.locales = [
      {
        locales: "en-US",
        messages: {
          foobar: "Tickets"
        }
      },
      {
        locales: ["fr-FR", "fr-CA"],
        messages: {
          foobar: "Trucs"
        }
      }
    ];
    
    this.defaultLocale = "en-US";
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

  registerHandlers() {
    // None yet; need one to change language maybe?
  }
}
