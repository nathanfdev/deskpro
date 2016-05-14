import escape from 'lodash/string/escape';
import mapValues from 'lodash/object/mapValues';
import assign from 'lodash/object/assign';

class PortalPhrases {
  constructor() {
    this.phrases = {};
    this.isLoaded = false;
  }

  setPhrases(phrases) {
    Object.assign(this.phrases, phrases);
  }

  get(phraseId, vars) {
    // Prevent to complain before getting any translation
    if (Object.keys(this.phrases).length == 0) {
      return '';
    }

    if (!this.phrases[phraseId]) {
      console.error('Missing phrase: ' + phraseId);
      return '[missing phrase ' + phraseId + ']';
    }

    let text = this.phrases[phraseId];

    if (vars) {
      for (var k in vars) {
        if (vars.hasOwnProperty(k)) {
          text = text.replace(`{${k}}`, vars[k]);
        }
      }
    }

    return text;
  }

  /**
   *
   * @param phraseId phraseId of the translation
   * @param vars regular variables to be escaped
   * @param safeVars html parts that won't be escaped (cannot contain user variables)
   * @returns {{__html: *}}
     */
  getHtml(phraseId, vars, safeVars) {
    vars = mapValues(vars, escape);

    vars = assign(vars, safeVars);

    const phrase = this.get(phraseId, vars);

    // Special object representing HTML in react
    // https://facebook.github.io/react/tips/dangerously-set-inner-html.html
    return {__html: phrase};
  }
}

export const portalPhrases = new PortalPhrases;
