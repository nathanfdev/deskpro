import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadEmailPhrases(group, languageId) {
    return this.api.sendGet(`DP_API/${this.url}/email_phrases/${group}/${languageId}`);
  }

  loadTranslations(code) {
    return this.api.sendGet(`DP_API/${this.url}/translations/${code}`);
  }

  saveCustomPhrase(phrase) {
    return this.api.sendPost(`DP_API/${this.url}/custom_phrase`, phrase);
  }
}
export default EmailTemplatesRepository;
