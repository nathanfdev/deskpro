/**
 * Holds phrases from language APIv2
 * Phrases used in IntlProvider
 */
class AgentPhrases {
  constructor() {
    this.phrases = {};
  }

  setPhrases(phrases) {
    Object.assign(this.phrases, phrases);
  }

  getPhrases() {
    return this.phrases;
  }
}
const agentPhrases = new AgentPhrases();
export default agentPhrases;
