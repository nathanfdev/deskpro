import escape from 'lodash/escape';
import mapValues from 'lodash/mapValues';
import assign from 'lodash/assign';

class PortalPhrases {
  constructor() {
    this.phrases   = {};
    this.direction = 'LTR';
    this.isLoaded  = false;
  }

  setPhrases(phrases) {
    Object.assign(this.phrases, phrases.phrases);
    this.direction = phrases.direction || 'LTR';
  }

  get(phraseId, vars) {
    // Prevent to complain before getting any translation
    if (Object.keys(this.phrases).length === 0) {
      return '';
    }

    if (!this.phrases[phraseId]) {
      console.error(`Missing phrase: ${phraseId}`);
      return `[missing phrase ${phraseId}]`;
    }

    let text = this.phrases[phraseId];

    if (vars && typeof vars['{count}'] !== 'undefined' && text.indexOf('|') !== -1) {
      const texts = text.split('|');
      if (parseInt(vars['{count}'], 10) === 1) {
        text = texts[0];
      } else {
        text = texts[1];
      }
    }
    if (vars) {
      Object.keys(vars).forEach((k) => {
        text = text.replace(`{${k}}`, vars[k]);
      });
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
    let preparedVars = vars;
    preparedVars = mapValues(preparedVars, escape);
    preparedVars = assign(preparedVars, safeVars);

    const phrase = this.get(phraseId, preparedVars);

    // Special object representing HTML in react
    // https://facebook.github.io/react/tips/dangerously-set-inner-html.html
    return { __html: phrase };
  }

  getTextDirection() {
    return this.direction;
  }
}

export const portalPhrases = new PortalPhrases();
