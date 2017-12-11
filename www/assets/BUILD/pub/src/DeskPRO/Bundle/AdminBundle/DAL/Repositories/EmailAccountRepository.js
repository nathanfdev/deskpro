import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailAccountRepository extends ApiRepository {
  loadEmailAccounts() {
    return this.api.sendGet(`DP_API/${this.url}`);
  }
}
export default EmailAccountRepository;
