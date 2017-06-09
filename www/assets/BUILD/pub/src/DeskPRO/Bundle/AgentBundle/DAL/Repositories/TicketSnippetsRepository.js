import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class TicketSnippetsRepository extends ApiRepository {
  loadTicketSnippets(page) {
    return this.api.sendGet(`DP_API/${this.url}${page ? `?page=${page}` : ''}`);
  }
}
export default TicketSnippetsRepository;
