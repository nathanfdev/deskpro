import uuid from 'uuid';
import { AppConfiguration, WidgetConfiguration }  from '../Domain';

export class AppsRegistry {

  /**
   * @param {{}} manifest
   * @param {AppsConfig} config
   * @return {AppConfiguration}
   */
  static appConfiguration(manifest, config)  {
    // we need to process a bit the manifest before we can use it
    let props;
    if (config.environment === 'development') {
      props = {
        id:             1,
        application_id: 1,
        baseUrl:        config.endpoint,
        ...manifest
      };
    } else {
      props = { baseUrl: `${config.endpoint}/file.php/apps/${manifest.application_id}`, ...manifest };
    }
    return AppConfiguration.fromAppManifestJS(props);
  }

  /**
   * @param {Array<Object>} rawManifests
   * @param {AppsConfig} config
   * @return { AppsRegistry }
   */
  static fromJS(rawManifests, config) {
    const appList = rawManifests.map(manifest => AppsRegistry.appConfiguration(manifest, config));
    return new AppsRegistry(appList);
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
    const mapper = app => AppsRegistry.createWidget(targetType, app);
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
