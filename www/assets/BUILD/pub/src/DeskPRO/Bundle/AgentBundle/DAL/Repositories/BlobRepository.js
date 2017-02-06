import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class BlobRepository extends ApiRepository {
  loadFiles(authId) {
    return this.api.sendGet(`DP_API/${this.url}/${authId}/files`);
  }
}
export default BlobRepository;
