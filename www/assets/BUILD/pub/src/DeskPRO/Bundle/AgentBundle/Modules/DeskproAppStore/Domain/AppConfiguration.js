class AppConfiguration
{
  /**
   * @param {object} config
   * @return {AppConfiguration}
   */
  static fromJS(config) {
    const { id, application_id, settings, targets, baseUrl } = config;
    return new AppConfiguration(id.toString(), application_id.toString(), settings, targets, baseUrl);
  }

  /**
   * @param {String} id
   * @param {String} applicationId
   * @param {Array} settings
   * @param {Array} targets
   * @param {String} baseUrl
   */
  constructor(id, applicationId, settings, targets, baseUrl) {
    this.id = id;
    this.applicationId = applicationId;
    this.settings = settings;
    this.targets = targets;
    this.baseUrl = baseUrl;
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

    return this.baseUrl + "/" + targetDefs[0].url;
  };

  toJS = () => {
    const js = { id: this.id, applicationId: this.applicationId, settings: this.settings, targets: this.targets };
    return JSON.parse(JSON.stringify(js));
  }
}

export default AppConfiguration;
