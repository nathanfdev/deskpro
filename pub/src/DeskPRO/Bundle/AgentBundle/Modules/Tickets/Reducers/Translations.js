import TranslationReducer from "DeskPRO/Component/TranslationReducer";

export default class Translations extends TranslationReducer {
  getLocales() {
    return [
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
  }

  registerHandlers() {
    // None yet; need one to change language maybe?
  }
}
