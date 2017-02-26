import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class BlobRepository extends ApiRepository {
  loadFiles(authId) {
    return this.api.sendGet(`DP_API/${this.url}/${authId}/files`);
  }
  uploadFile(data) {
    return this.api.sendPost(`DP_API/${this.url}/form_data`, data, { processData: false, contentType: false, jsonPayload: false });
  }
}
export default BlobRepository;
