import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadInfo() {
    return this.api.sendGet(`DP_API/${this.url}/info`);
  }

  loadTemplate(name) {
    return this.api.sendGet(`DP_API/${this.url}/template/${name}`);
  }

  saveTemplate(name, template) {
    return this.api.sendPost(`DP_API/${this.url}/template/${name}`, template);
  }

  loadVariables(viewModel) {
    return this.api.sendGet(`DP_API/${this.url}/view_model/variables/${viewModel}`);
  }
}
export default EmailTemplatesRepository;
