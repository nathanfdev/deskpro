import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

class TicketFollowUpRepository extends ApiRepository {
  /**
   * @throws {Error}
   *
   * @returns {Promise} promise
   */
  loadAll(include = null) {
    const url = `tickets/${include.ticketId}/follow-ups`;
    delete include.ticketId;
    const params = include ? `?${compileParams({ include })}` : '';
    return this.api.sendGet(`DP_API/${url}${params}`);
  }
}
export default TicketFollowUpRepository;
