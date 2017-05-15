class AppConfiguration
{
  /**
   * @param {object} config
   * @return {AppConfiguration}
   */
  static fromJS(config) {
    const { id, application_id, settings, targets, baseUrl, title, name } = config;
    return new AppConfiguration(id.toString(), application_id.toString(), settings, targets, baseUrl, title, name);
  }

  /**
   * @param {String} id
   * @param {String} applicationId
   * @param {Array} settings
   * @param {Array} targets
   * @param {String} baseUrl
   * @param {String} title
   * @param {String} packageName
   */
  constructor(id, applicationId, settings, targets, baseUrl, title, packageName) {
    this.props = {
      instanceId: id,
      applicationId,
      settings,
      targets,
      baseUrl,
      title,
      packageName
    };
  }

  get instanceId() { return this.props.instanceId; }

  get applicationId() { return this.props.applicationId; }

  get applicationTitle() { return this.props.title; }

  get applicationPackageName() { return this.props.packageName; }

  get settings() { return this.props.settings; }

  get targets() { return this.props.targets; }

  get baseUrl() { return this.props.baseUrl; }

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
    const js = { id: this.instanceId, applicationId: this.applicationId, settings: this.settings, targets: this.targets };
    return JSON.parse(JSON.stringify(js));
  }
}

export default AppConfiguration;
