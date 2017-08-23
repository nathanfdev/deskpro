import uuid from 'node-uuid';
import AppConfiguration from './AppConfiguration';
import WidgetConfiguration from './WidgetConfiguration';

class DeskproAppRegistry {
  /**
   * @param {Array<Object>} rawManifests
   * @param {DeskproAppStoreConfiguration} config
   * @return { DeskproAppRegistry }
   */
  static fromJS(rawManifests, config) {
    // we need to process a bit the manifest before we can use it
    let mapper;
    if (config.environment === 'development') {
      mapper = manifest => ({
        id:             1,
        application_id: 1,
        baseUrl:        config.endpoint,
        ...manifest
      });
    } else {
      mapper = manifest => ({ baseUrl: `${config.endpoint}/file.php/apps/${manifest.application_id}`, ...manifest });
    }
    const manifests = rawManifests.map(mapper);

    const appList = manifests.map(manifest => AppConfiguration.fromAppManifestJS(manifest));
    return new DeskproAppRegistry(appList);
  }

  /**
   * @param {Array<AppConfiguration>} appList
   */
  constructor(appList) {
    this.appList = appList;
  }

  /**
   * @param {String} targetType
   * @return {Array<AppConfiguration>}
   */
  getAppsConfigByTargetType = targetType => this.appList.filter(app => app.hasTarget(targetType));

  /**
   * @param {String} targetType
   * @return {Array<WidgetConfiguration>}
   */
  getWidgetConfigByTargetType = (targetType) => {
    const mapper = app => DeskproAppRegistry.createWidget(targetType, app);
    return this.getAppsConfigByTargetType(targetType).map(mapper);
  };

  /**
   * @param {String} target
   * @param {AppConfiguration} appConfig
   * @return {WidgetConfiguration}
   */
  static createWidget(target, appConfig)  {
    /** @type {string} */ const id = uuid.v4();
    return new WidgetConfiguration({ id, target, appConfig });
  }
}

export default DeskproAppRegistry;
