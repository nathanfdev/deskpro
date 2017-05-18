
export class AppServices
{
  /**
   * @param {DpApi} api
   */
  constructor({ api, window }) {
    this.props = { api, window };
  }

  /**
   * @return {DpApi}
   */
  get api() { return this.props.api; }

  /**
   * @return {Window}
   */
  get window() { return this.props.window; }
}
