import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * TicketFilterRepository
 */
export class TicketFilterRepository extends ApiRepository {
  /**
   * @param id
   * @param groupBy
   * @returns {*}
   */
  loadFilterCounts(id, groupBy) {
    return this.api.sendGet(`DP_API/${this.url}/${id}/count` + (groupBy ? `?group_by=${groupBy}` : ''));
  }

  postFilterPref(parentId, groupBy) {
    const params = { main_grouping: groupBy };
    return this.api.sendPost(`DP_API/${this.url}/${parentId}/prefs`, params);
  }
}
