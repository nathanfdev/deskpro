class WidgetConfiguration
{
  /**
   * @param {String} target
   * @param {AppConfiguration} appConfig
   * @param {Object} xcomponentConfig
   */
  constructor(target, appConfig, xcomponentConfig) {

    this.props = {
      target,
      appConfig,
      xcomponentConfig
    };
  }

  get target() { return this.props.target; }

  /**
   * @return {AppConfiguration}
   */
  get appConfig() { return this.props.appConfig; }

  get xcomponentConfig() { return this.props.xcomponentConfig; }
}

export default WidgetConfiguration;
