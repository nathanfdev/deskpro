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

  resetTemplate(name) {
    return this.api.sendDelete(`DP_API/${this.url}/template/${name}`);
  }

  previewTemplate(template, code, variables) {
    return this.api.sendPost(`DP_API/${this.url}/render_template`, { template, code, variables });
  }

  loadVariables(viewModel) {
    return this.api.sendGet(`DP_API/${this.url}/view_model/variables/${viewModel}`);
  }

  getFiles(type) {
    return this.api.sendGet(`DP_API/${this.url}/email_assets/${type}`);
  }

  saveFile(type, file) {
    return this.api.sendPost(`DP_API/${this.url}/email_assets/${type}`, file);
  }
}
export default EmailTemplatesRepository;
