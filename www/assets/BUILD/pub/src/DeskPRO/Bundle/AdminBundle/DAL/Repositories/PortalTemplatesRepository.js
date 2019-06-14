import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class PortalTemplatesRepository extends ApiRepository {
  loadInfo() {
    return this.api.sendGet('/portal/api/style/edit-theme-set/templates');
  }

  loadTemplate(name) {
    return this.api.sendGet(`/portal/api/style/edit-theme-set/template-info?template=${name}`);
  }

  loadTagInfo(name) {
    return this.api.sendGet(`/portal/api/style/edit-theme-set/tag-info?tag=${name}`);
  }

  saveTemplate(name, template) {
    return this.api.sendPut(`/portal/api/style/edit-theme-set/template-sources?template=${name}`, template);
  }

  deleteTemplate(name) {
    return this.api.sendDelete(`DP_API/${this.url}/template/${name}`);
  }

  resetTemplate(name) {
    return this.api.sendDelete(`DP_API/${this.url}/template/${name}`);
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
export default PortalTemplatesRepository;
