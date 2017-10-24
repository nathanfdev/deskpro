import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * ReportRepository
 */
class ReportRepository extends ApiRepository {

  /**
   * @returns {Promise} promise
   */
  loadAll() {
    return this.api
      .sendGet(
      'DP_API_OLD/dashboards/widgets/reports/list',
      {
        headers: {
          'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
          'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
          'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
        }
      });
  }
}

export default ReportRepository;
