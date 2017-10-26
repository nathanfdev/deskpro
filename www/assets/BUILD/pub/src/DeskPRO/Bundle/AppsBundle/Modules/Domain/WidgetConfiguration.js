import { PropertyBag } from './PropertyBag';
import { WidgetUrlBuilder } from './WidgetUrlBuilder';

export class WidgetConfiguration extends PropertyBag {
  /**
   * @param {String} id
   * @param {String} target
   * @param {AppConfiguration} appConfig
   * @param [undeclaredProps]
   */
  constructor({ id, target, appConfig, ...undeclaredProps }) {
    super({ id, target, appConfig, ...undeclaredProps });
  }

  /**
   * @param {string} [params]
   * @return {String}
   */
  getUrl(params) {
    if (params && typeof params !== 'object') {
      throw new Error('params must be of type object');
    }

    const { /** @type {AppConfiguration} */ appConfig } = this;

    const { baseUrl, version } = appConfig;
    const targetPath = appConfig.getTargetPath(this.target);

    const builder = new WidgetUrlBuilder({ baseUrl });
    builder.setAppTargetPath(targetPath).setAppVersion(`v${version}`).setWidgetId(this.id);

    if (params) {
      builder.setParams(params);
    }

    return builder.build();
  }

  /**
   * @type {string}
   */
  get id() { return this.props.id; }

  /**
   * @type {string}
   */
  get target() { return this.props.target; }

  /**
   * @type {AppConfiguration}
   */
  get appConfig() { return this.props.appConfig; }

  toJS() {
    const { appConfig, ...rest } = this.props;
    const props = { ...rest, appConfig: appConfig.toJS() };

    return JSON.parse(JSON.stringify(props));
  }

}
