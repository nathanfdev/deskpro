class AppConfiguration
{
  /**
   * @param {object} config
   * @return {AppConfiguration}
   */
  static fromJS(config) {
    const { id, application_id, settings, targets } = config;
    return new AppConfiguration(id.toString(), application_id.toString(), settings, targets);
  }

  /**
   * @param {String} id
   * @param {String} applicationId
   * @param {Array} settings
   * @param {Array} targets
   */
  constructor(id, applicationId, settings, targets) {
    this.id = id;
    this.applicationId = applicationId;
    this.settings = settings;
    this.targets = targets;
  }

  /**
   * @param {String} target
   * @return {boolean}
   */
  hasTarget = (target) => {
    const targetDefs = this.targets.filter( targetDef => targetDef.target === target );
    return targetDefs.length > 0;
  };

  /**
   * @param {String} target
   * @return {String|null}
   */
  getUrl = (target) => {
    const targetDefs = this.targets.filter( targetDef => targetDef.target === target );
    if (targetDefs.length === 0) {
      return null;
    }

    return targetDefs[0].url;
  };

  toJS = () => {
    const js = { id: this.id, applicationId: this.applicationId, settings: this.settings, targets: this.targets };
    return JSON.parse(JSON.stringify(js));
  }
}

export default AppConfiguration;
