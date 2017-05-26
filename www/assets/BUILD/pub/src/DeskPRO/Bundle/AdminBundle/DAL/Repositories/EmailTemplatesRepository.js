import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class EmailTemplatesRepository extends ApiRepository {
  loadInfo() {
    return this.api.sendGet(`DP_API/${this.url}/info`);
  }

  loadLegacyTemplates() {
    return this.api.sendGet(`DP_API/${this.url}/legacy_templates`);
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

  previewTemplate(template, group, code, variables, lang, extraTemplates) {
    return this.api.sendPost(`DP_API/${this.url}/render_template`, { template, group, code, variables, lang, extraTemplates });
  }

  sendPreviewEmail(viewModel, group, subject, body, variables, lang, from, to, extraTemplates) {
    return this.api.sendPost(`DP_API/${this.url}/send_preview`, { viewModel, group, subject, body, variables, lang, from, to, extraTemplates });
  }

  loadVariables(viewModel) {
    return this.api.sendGet(`DP_API/${this.url}/view_model/variables/${viewModel}`);
  }

  getFiles(type) {
    return this.api.sendGet(`DP_API/${this.url}/email_assets/${type}`);
  }

  deleteAsset(themeSetAssetId) {
    return this.api.sendDelete(`DP_API/${this.url}/email_assets/${themeSetAssetId}`);
  }
}
export default EmailTemplatesRepository;
