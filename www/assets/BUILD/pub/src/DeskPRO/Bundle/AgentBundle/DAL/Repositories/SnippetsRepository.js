import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class SnippetsRepository extends ApiRepository {
  loadTicketSnippets(page) {
    return new Promise((resolve) => {
      this.api.sendGet(`DP_API/${this.url}/counts?type=ticket`).then((promise) => {
        this.api.sendGet(`DP_API/${this.url}?type=ticket&inline_sideloads=true&include=snippet_translation,language&count=${promise.data.data.count}${page ? `&page=${page}` : ''}`)
          .then((snippets) => {
            resolve(snippets);
          });
      });
    });
  }
}
export default SnippetsRepository;
