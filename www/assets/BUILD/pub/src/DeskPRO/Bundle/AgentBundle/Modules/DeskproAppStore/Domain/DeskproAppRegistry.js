import AppConfiguration from './AppConfiguration'
import WidgetConfiguration from './WidgetConfiguration'

class DeskproAppRegistry
{
  /**
   * @param {Array<Object>} jsList
   * @return { DeskproAppRegistry }
   */
  static fromJS(jsList) {
    // TODO validate with a schema
    const appList = jsList.map(js => AppConfiguration.fromJS(js));
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
    const url = `http://127.0.0.1:31080/${app.getUrl(target)}`;

    const xconfig = {
      tag,
      url,
      dimensions: { width: '100%', height: '100%' },
      timeout: 1000, // seconds
      // The properties they can (or must) pass down to my component
      props: {

        app: {
          type: 'string',
          required: true
        },

        onDpMessage: {
          type: 'function',
          required: true
        }
      }
    };

    return new WidgetConfiguration(target, app, xconfig);
  }
}

export default DeskproAppRegistry;
