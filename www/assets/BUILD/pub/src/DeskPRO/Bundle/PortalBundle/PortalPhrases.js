class PortalPhrases {
  constructor() {
    this.phrases = {};
    this.isLoaded = false;
  }

  setPhrases(phrases) {
    Object.assign(this.phrases, phrases);
  }

  get(phraseId, vars) {
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
}

export const portalPhrases = new PortalPhrases;
