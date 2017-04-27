import AppConfiguration from './AppConfiguration'
import WidgetConfiguration from './WidgetConfiguration'
import uuid from 'node-uuid';

class DeskproAppRegistry
{
  /**
   * @param {Array<Object>} rawManifests
   * @param {DeskproAppStoreConfiguration} config
   * @return { DeskproAppRegistry }
   */
  static fromJS(rawManifests, config) {
    let manifests;
    if (config.environment == 'development') {
      manifests = rawManifests.map(manifest => ({ id: 1, application_id: 1, baseUrl: config.endpoint, ...manifest }));
    } else {
      manifests = rawManifests.map(manifest => ({ baseUrl: `${config.endpoint}/file.php/apps/${manifest.id}/files`, ...manifest}))
    }

    const appList = manifests.map(manifest => AppConfiguration.fromJS(manifest));
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
  getAppsConfigByTargetType = (targetType) => {
    return this.appList.filter(app => app.hasTarget(targetType));
  };

  /**
   * @param {String} targetType
   * @return {Array<WidgetConfiguration>}
   */
  getWidgetConfigByTargetType = (targetType) => {
      return this.getAppsConfigByTargetType(targetType).map(app => DeskproAppRegistry.createWidget(targetType, app));
  };

  /**
   * @param {String} target
   * @param {AppConfiguration} app
   * @return {WidgetConfiguration}
   */
  static createWidget(target, app)
  {
    //const tag = `${target}-${appId}`;
    //TODO the tag set here needs to match the tag set in the iframe by the SDK otherwise communication will not happen
    const tag = target;
    const url = app.getUrl(target);

    const xconfig = {
      tag,
      url,
      scrolling: false,
      autoResize: true,
      dimensions: { width: '100%', height: 1000 },
      timeout: 3000, // millis
      // The properties they can (or must) pass down to my component
      props: {
        widgetId: { type: 'string', required: true },
        onDpMessage: { type: 'function', required: true }
      }
    };

    return new WidgetConfiguration(target, app, xconfig);
  }
}

export default DeskproAppRegistry;
