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
   * @param {AppConfiguration} app
   * @return {WidgetConfiguration}
   */
  static createWidget(target, app)  {
    // TODO the tag set here needs to match the tag set in the iframe by the SDK otherwise communication will not happen

    const id = uuid.v4();
    const tag = [target, app.instanceId, id].join('-');
    const url = app.getUrlBuilder(target).setXconfTag(tag).build();

    const xconfig = {
      // TODO: this should not be necessary when rendering as iframe. Dig into xcomponent's code to understand why the template mechanism is activated
      defaultContext: 'iframe',
      parentTemplate: '<div class="{CLASS.ELEMENT}" />',

      autoResize: true,
      scrolling:  false,
      tag,
      url,

      dimensions: { width: '100%', height: '100%' },

      timeout: 3000, // millis
      // The properties they can (or must) pass down to my component
      props:   {

        // WIDGET PROPERTIES

        widgetId: {
          type:     'string',
          required: true
        },

        onDpMessage: {
          type:     'function',
          required: true
        },

        instanceProps: {
          type:     'object',
          required: true
        },

        contextProps: {
          type:     'object',
          required: true
        }
      }
    };

    return new WidgetConfiguration(id, target, app, xconfig);
  }
}

export default DeskproAppRegistry;
