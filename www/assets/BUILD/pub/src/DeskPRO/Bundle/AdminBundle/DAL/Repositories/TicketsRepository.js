import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

class TicketsRepository extends ApiRepository {
  loadTicket(ticketId) {
    return this.api.sendGet(`DP_API/${this.url}/${ticketId}`);
  }
}
export default TicketsRepository;
