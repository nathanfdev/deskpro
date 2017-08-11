import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class SnippetsRepository extends ApiRepository {
  saveSnippet(data) {
    if (data.id) {
      const snippet = Object.assign({}, data);
      delete snippet.id;
      return this.update(snippet, data.id);
    }
    return this.api.sendPost(`DP_API/${this.url}?inline_sideloads=true&include=snippet_translation`, data);
  }

  massActions(payload) {
    return this.api.sendPost(`DP_API/${this.url}/mass_actions`, payload);
  }

  exportSnippets(ids) {
    window.location.href = `${window.DP_BASE_API_URL}/v2/${this.url}/csv?ids=${ids}`;
  }
}
export default SnippetsRepository;
