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

  get applicationId() {
    return this.appConfig.applicationId;
  }
}

export default WidgetConfiguration;
