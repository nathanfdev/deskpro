import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadInfo() {
    return this.api.sendGet(`DP_API/${this.url}/info`);
  }

  loadVariables(viewModel) {
    return this.api.sendGet(`DP_API/${this.url}/view_model/variables/${viewModel}`);
  }
}
export default EmailTemplatesRepository;
