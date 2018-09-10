import uuid from 'uuid';
import { AppConfiguration, WidgetConfiguration }  from '../Domain';

function filterByTargetType(targetType) {
  /**
   * @param {AppConfiguration} config
   * @return {boolean}
   */
  function filter({ config }) {
    return config.hasTarget(targetType);
  }

  return filter;
}

function createWidget(targetType) {
  function create({ config, settings }) {
    /** @type {string} */ const id = uuid.v4();
    return new WidgetConfiguration({ id, target: targetType, appConfig: config, appSettings: settings });
  }

  return create;
}

/**
 * @param {{}} manifest
 * @param {AppsConfig} config
 * @return {AppConfiguration}
 */
function createAppConfiguration(manifest, config) {
  // we need to process a bit the manifest before we can use it
  let props;
  if (config.environment === 'development') {
    props = {
      id:             config.instanceId,
      application_id: config.applicationId,
      baseUrl:        config.endpoint,
      ...manifest
    };
  } else {
    props = { baseUrl: `${config.endpoint}/file.php/apps/${manifest.application_id}`, ...manifest };
  }
  return AppConfiguration.fromAppManifestJS(props);
}


export class AppsRegistry {

  /**
   * @param {Array<Object>} rawManifests
   * @param {AppsConfig} config
   * @return { AppsRegistry }
   */
  static fromJS(rawManifests, config) {
    const appList = rawManifests.map(({ settings, manifest }) => ({ settings, config: createAppConfiguration(manifest, config) }));
    return new AppsRegistry(appList);
  }

  /**
   * @param {Array<{settings: Object, config: AppConfiguration}>} appList
   */
  constructor(appList) {
    this.appList = appList;
  }

  /**
   * @param {String} targetType
   * @return {Array<WidgetConfiguration>}
   */
  getWidgetConfigByTargetType = targetType => this.appList.filter(filterByTargetType(targetType)).map(createWidget(targetType));

}
