import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

class TicketRepository extends ApiRepository {
  /**
   * @throws {Error}
   *
   * @returns {Promise} promise
   */
  loadAllFollowUps(ticketId, include = null) {
    const params = include ? `&${compileParams({ include })}` : '';
    return this.api.sendGet(`DP_API/${this.url}/${ticketId}/follow-ups?count=100${params}`);
  }

  createFollowUp(ticketId, record) {
    return this.api.sendPost(`DP_API/${this.url}/${ticketId}/follow-ups`, record);
  }

  deleteFollowUp(ticketId, followUpId) {
    return this.api.sendDelete(`DP_API/${this.url}/${ticketId}/follow-ups/${followUpId}`);
  }
}
export default TicketRepository;
