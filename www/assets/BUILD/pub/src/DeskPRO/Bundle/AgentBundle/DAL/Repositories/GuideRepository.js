import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class GuideRepository extends ApiRepository {
  loadTree(guideId) {
    return this.api.sendGet(`DP_API/${this.url}/tree/${guideId}`);
  }

  saveTree(guideId, treeData) {
    return this.api.sendPut(`DP_API/${this.url}/tree/${guideId}`, { tree: treeData });
  }
}
export default GuideRepository;
