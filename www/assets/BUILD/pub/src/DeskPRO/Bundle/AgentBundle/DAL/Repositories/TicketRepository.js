import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

class TicketRepository extends ApiRepository {
  /**
   * @throws {Error}
   *
   * @returns {Promise} promise
   */
  loadAllFollowUps(ticketId, include = null) {
    const params = include ? `?${compileParams({ include })}` : '';
    return this.api.sendGet(`DP_API/${this.url}/${ticketId}/follow-ups${params}`);
  }

  createFollowUp(ticketId, record) {
    return this.api.sendPost(`DP_API/${this.url}/${ticketId}/follow-ups`, record);
  }
}
export default TicketRepository;
