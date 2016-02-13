import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * UserChatRepository
 */
export class UserChatRepository extends ApiRepository {
  /**
   * @param groupBy
   * @param agent
   * @return Promise
   */
  loadCounts(groupBy, agent) {
    return this.api.sendGet(`DP_API/${this.url}/counts?group_by=` + groupBy + (agent ? '&agent=' + agent : ''));
  }
}
