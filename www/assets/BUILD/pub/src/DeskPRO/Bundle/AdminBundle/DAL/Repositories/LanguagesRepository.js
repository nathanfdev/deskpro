import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadEmailPhrases(group, languageId) {
    return this.api.sendGet(`DP_API/${this.url}/email_phrases/${group}/${languageId}`);
  }

  loadTranslations(phraseName) {
    return this.api.sendGet(`DP_API/${this.url}/translations/${phraseName}`);
  }

  saveCustomPhrase(phrase) {
    return this.api.sendPost(`DP_API/${this.url}/custom_phrase`, phrase);
  }

  saveTranslations(phraseName, translations) {
    return this.api.sendPost(`DP_API/${this.url}/translations/${phraseName}`, { translations });
  }
}
export default EmailTemplatesRepository;
