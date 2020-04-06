import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class PortalTemplatesRepository extends ApiRepository {
  loadInfo(brandSlug) {
    return this.api.sendGet(`/b/${brandSlug}/portal/api/style/edit-theme-set/templates`);
  }

  loadTemplate(name, brandSlug) {
    return this.api.sendGet(`/b/${brandSlug}/portal/api/style/edit-theme-set/template-info?template=${name}`);
  }

  loadTagInfo(name, brandSlug) {
    return this.api.sendGet(`/b/${brandSlug}/portal/api/style/edit-theme-set/tag-info?tag=${name}`);
  }

  saveTemplate(name, template, brandSlug) {
    return this.api.sendPut(`/b/${brandSlug}/portal/api/style/edit-theme-set/template-sources?template=${name}`, template);
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

  loadAssets(brandSlug) {
    return this.api.sendGet(`/b/${brandSlug}/portal/api/style/edit-theme-set/assets`);
  }

  deleteAsset(themeSetAssetId, brandSlug) {
    return this.api.sendDelete(`/b/${brandSlug}/portal/api/style/edit-theme-set/assets/${themeSetAssetId}`);
  }
}
export default PortalTemplatesRepository;
