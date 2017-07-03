import { AppUrlBuilder } from './AppUrlBuilder';
import { AppAssets } from './AppAssets';

class AppConfiguration {
  /**
   * @param {object} config
   * @return {AppConfiguration}
   */
  static fromAppManifestJS(config) {
    const { id, application_id, settings, targets, baseUrl, title, name, appVersion } = config;
    return new AppConfiguration({
      instanceId:    id.toString(),
      applicationId: application_id.toString(),
      settings,
      targets,
      baseUrl,
      title,
      packageName:   name,
      version:       appVersion
    });
  }

  /**
   * @param {String} instanceId
   * @param {String} applicationId
   * @param {Array} settings
   * @param {Array} targets
   * @param {String} baseUrl
   * @param {String} title
   * @param {String} packageName
   * @param version
   */
  constructor({ instanceId, applicationId, settings, targets, baseUrl, title, packageName, version }) {
    this.props = {
      instanceId,
      applicationId,
      settings,
      targets,
      baseUrl,
      title,
      packageName,
      version
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
   * @return {String}
   */
  get version() { return this.props.version; }

  /**
   * @return {AppAssets}
   */
  get assets() { return new AppAssets({ appVersion: `v${this.props.version}` }); }

  /**
   * @param {String} target
   * @return {boolean}
   */
  hasTarget = (target) => {
    const targetDefs = this.targets.filter(targetDef => targetDef.target === target);
    return targetDefs.length > 0;
  };

  /**
   * @param {String} target
   * @return {AppUrlBuilder|null}
   */
  getUrlBuilder = (target) => {
    const targetDefs = this.targets.filter(targetDef => targetDef.target === target);
    if (targetDefs.length === 0) {
      return null;
    }

    const { baseUrl } = this.props;
    const builder = new AppUrlBuilder({ baseUrl });
    builder.setBundlePath(targetDefs[0].url);
    builder.setAppVersion(`v${this.props.version}`);

    return builder;
  };

  toJS = () => {
    const { instanceId:id, applicationId, settings, targets } = this;
    const js = { id, applicationId, settings, targets };
    return JSON.parse(JSON.stringify(js));
  }
}

export default AppConfiguration;
