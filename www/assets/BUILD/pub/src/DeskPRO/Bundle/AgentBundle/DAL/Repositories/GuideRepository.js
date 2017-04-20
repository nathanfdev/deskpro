import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class GuideRepository extends ApiRepository {
  loadTree(guideId) {
    return this.api.sendGet(`DP_API/${this.url}/${guideId}/tree`);
  }

  saveTree(guideId, treeData) {
    return this.api.sendPut(`DP_API/${this.url}/${guideId}/tree`, { tree: treeData });
  }
}
export default GuideRepository;
