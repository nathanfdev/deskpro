import deprecate from 'util-deprecate';
import escape from 'lodash/escape';
import mapValues from 'lodash/mapValues';
import assign from 'lodash/assign';
import reactStringReplace from 'react-string-replace';

class AgentPhrases {
  static getCount(text, count) {
    const variations = text.split('|');
    if (count === 1 || variations.length === 1) {
      return variations[0].replace('{{count}}', count);
    }
    return variations[1].replace('{{count}}', count);
  }

  constructor() {
    this.phrases = {};
    this.isLoaded = false;
  }

  setPhrases(phrases) {
    Object.assign(this.phrases, phrases);
  }

  get(phraseId, vars) {
    deprecate(() => {}, 'AgentPhrases.get() is deprecated please use FormattedMessage instead')();
    // Prevent to complain before getting any translation
    if (Object.keys(this.phrases).length === 0) {
      return '';
    }

    if (!this.phrases[phraseId]) {
      console.error(`Missing phrase: ${phraseId}`);
      return `[missing phrase ${phraseId}]`;
    }

    let text = this.phrases[phraseId];

    if (vars) {
      if ({}.hasOwnProperty.call(vars, 'count')) {
        text = AgentPhrases.getCount(text, vars.count);
      }
      Object.entries(vars).map((value) => {
        text = text.replace(`{${value[0]}}`, value[1]);
        return null;
      });
    }

    return text;
  }

  getPhrases() {
    return this.phrases;
  }

  /**
   *
   * @param phraseId phraseId of the translation
   * @param vars regular variables to be escaped
   * @param safeVars html parts that won't be escaped (cannot contain user variables)
   * @returns {{__html: *}}
   */
  getHtml(phraseId, vars, safeVars) {
    let localVars = mapValues(vars, escape);

    localVars = assign(localVars, safeVars);

    const phrase = this.get(phraseId, localVars);

    // Special object representing HTML in react
    // https://facebook.github.io/react/tips/dangerously-set-inner-html.html
    return { __html: phrase };
  }

  getHtmlWithComponents(phraseId, vars) {
    return reactStringReplace(this.get(phraseId), /({[\w\d]+})/g, (match) => {
      if (vars) {
        for (const id in vars) {
          if ({}.hasOwnProperty.call(vars, id)) {
            if (`{${id}}` === match) {
              return vars[id];
            }
          }
        }
      }

      return match;
    });
  }
}
const agentPhrases = new AgentPhrases();
export default agentPhrases;
