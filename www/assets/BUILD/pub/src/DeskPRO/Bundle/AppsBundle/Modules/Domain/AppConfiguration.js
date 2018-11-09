import { AppAssets } from './AppAssets';
import { PropertyBag } from './PropertyBag';
import { InstanceProps } from './WidgetProps';

export class AppConfiguration extends PropertyBag {
  /**
   * @param {object} config
   * @return {AppConfiguration}
   */
  static fromAppManifestJS(config) {
    const { id, application_id, settings, targets, baseUrl, title, name, appVersion, bundleUpdatedAt } = config;
    return new AppConfiguration({
      instanceId:    id.toString(),
      applicationId: application_id.toString(),
      settings,
      targets,
      baseUrl,
      title,
      packageName:   name,
      version:       appVersion,
      bundleUpdatedAt
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
   * @param {String} version
   * @param {Number} bundleUpdatedAt
   * @param undeclared
   */
  constructor({ instanceId, applicationId, settings, targets, baseUrl, title, packageName, version, bundleUpdatedAt, ...undeclared }) {
    super({ instanceId, applicationId, settings, targets, baseUrl, title, packageName, version, bundleUpdatedAt, ...undeclared });
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
   * @return {Array<{target:String, url:String}>}
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
   * @return {Number}
   */
  get bundleUpdatedAt() { return this.props.bundleUpdatedAt; }

  /**
   * @return {AppAssets}
   */
  get assets() { return new AppAssets({ appVersion: `v${this.props.version}` }); }

  /**
   * @param {String} target
   * @return {boolean}
   */
  hasTarget(target) {
    const targetDefs = this.targets.filter(targetDef => targetDef.target === target);
    return targetDefs.length > 0;
  }

  /**
   * @param {string} target
   * @return {string|null}
   */
  getTargetPath(target)  {
    const targetDefs = this.targets.filter(targetDef => targetDef.target === target);
    if (targetDefs.length !== 1) {
      return null;
    }

    return targetDefs[0].url;
  }

  /**
   * @return {InstanceProps}
   */
  toWidgetProps() {
    const { instanceId, applicationId, applicationTitle, applicationPackageName } = this;

    return new InstanceProps({
      appId:          applicationId.toString(),
      appTitle:       applicationTitle.toString(),
      appPackageName: applicationPackageName.toString(),
      instanceId:     instanceId.toString()
    });
  }

  toJS() {
    const { instanceId:id, applicationId, settings, targets } = this;
    const js = { id, applicationId, settings, targets };
    return JSON.parse(JSON.stringify(js));
  }
}

