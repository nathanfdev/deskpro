import { AppUrlBuilder } from './AppUrlBuilder'
import { AppAssets } from './AppAssets'

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

  /**
   * @return {String}
   */
  get instanceId() { return this.props.instanceId; }

  /**
   * @return {String}
   */
  get applicationId() { return this.props.applicationId; }

  /**
   * @return {String}
   */
  get applicationTitle() { return this.props.title; }

  /**
   * @return {String}
   */
  get applicationPackageName() { return this.props.packageName; }

  /**
   * @return {Array}
   */
  get settings() { return this.props.settings; }

  /**
   * @return {Array}
   */
  get targets() { return this.props.targets; }

  /**
   * @return {String}
   */
  get baseUrl() { return this.props.baseUrl; }

  /**
   * @return {AppAssets}
   */
  get assets() { return new AppAssets(); }

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
   * @return {AppUrlBuilder|null}
   */
  getUrlBuilder = (target) => {
    const targetDefs = this.targets.filter( targetDef => targetDef.target === target );
    if (targetDefs.length === 0) {
      return null;
    }

    const { baseUrl } = this.props;
    const builder = new AppUrlBuilder({ baseUrl });
    builder.setBundlePath(targetDefs[0].url);

    return builder;
  };

  toJS = () => {
    const js = { id: this.instanceId, applicationId: this.applicationId, settings: this.settings, targets: this.targets };
    return JSON.parse(JSON.stringify(js));
  }
}

export default AppConfiguration;
