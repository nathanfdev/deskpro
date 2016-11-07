import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadEmailPhrases(languageId) {
    return this.api.sendGet(`DP_API/${this.url}/email_phrases/${languageId}`);
  }
}
export default EmailTemplatesRepository;
