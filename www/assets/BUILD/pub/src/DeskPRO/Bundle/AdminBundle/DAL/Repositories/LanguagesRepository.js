import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadEmailPhrases(group, languageId) {
    return this.api.sendGet(`DP_API/${this.url}/email_phrases/${group}/${languageId}`);
  }
}
export default EmailTemplatesRepository;
