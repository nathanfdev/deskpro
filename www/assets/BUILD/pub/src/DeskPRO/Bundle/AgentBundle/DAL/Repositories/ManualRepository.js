import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class ManualRepository extends ApiRepository {
  loadTree(manualId) {
    return this.api.sendGet(`DP_API/${this.url}/tree/${manualId}`);
  }

  saveTree(manualId, treeData) {
    return this.api.sendPut(`DP_API/${this.url}/tree/${manualId}`, { tree: treeData });
  }
}
export default ManualRepository;
