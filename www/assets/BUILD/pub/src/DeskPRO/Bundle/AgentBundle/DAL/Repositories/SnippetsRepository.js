import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class SnippetsRepository extends ApiRepository {
  getSnippet(id) {
    return this.api.sendGet(`DP_API/${this.url}/${id}?inline_sideloads=true&include=snippet_translation`);
  }
  saveSnippet(data) {
    if (data.id) {
      const snippet = Object.assign({}, data);
      delete snippet.id;
      return this.api.sendPut(`DP_API/${this.url}/${data.id}`, snippet);
    }
    return this.api.sendPost(`DP_API/${this.url}?inline_sideloads=true&include=snippet_translation`, data);
  }
}
export default SnippetsRepository;
