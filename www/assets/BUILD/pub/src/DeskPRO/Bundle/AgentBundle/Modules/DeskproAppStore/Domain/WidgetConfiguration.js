class WidgetConfiguration
{
  /**
   * @param {String} target
   * @param {AppConfiguration} appConfig
   * @param {Object} xcomponentConfig
   */
  constructor(target, appConfig, xcomponentConfig) {
    this.target = target;
    this.appConfig = appConfig;
    this.xcomponentConfig = xcomponentConfig;
  }
}

export default WidgetConfiguration;
