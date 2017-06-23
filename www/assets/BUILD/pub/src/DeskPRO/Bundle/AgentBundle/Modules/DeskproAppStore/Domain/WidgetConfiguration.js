class WidgetConfiguration {
  /**
   * @param {String} id
   * @param {String} target
   * @param {AppConfiguration} appConfig
   * @param {Object} xcomponentConfig
   */
  constructor(id, target, appConfig, xcomponentConfig) {
    this.props = {
      id,
      target,
      appConfig,
      xcomponentConfig
    };
  }

  get id() { return this.props.id; }

  get target() { return this.props.target; }

  /**
   * @return {AppConfiguration}
   */
  get appConfig() { return this.props.appConfig; }

  get xcomponentConfig() { return this.props.xcomponentConfig; }
}

export default WidgetConfiguration;
