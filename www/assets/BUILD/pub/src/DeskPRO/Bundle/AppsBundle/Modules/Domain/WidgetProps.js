import { PropertyBag } from './PropertyBag';

export class InstanceProps extends PropertyBag {
  /**
   * @param {String} appId
   * @param {String} appTitle
   * @param {String} appPackageName
   * @param {String} instanceId
   * @param [undeclaredProps]
   */
  constructor({ appId, appTitle, appPackageName, instanceId, ...undeclaredProps })  {
    super({ appId, appTitle, appPackageName, instanceId, ...undeclaredProps });
  }

  get appId() { return this.props.appId; }

  get appTitle() { return this.props.appTitle; }

  get appPackageName() { return this.props.appPackageName; }

  get instanceId() { return this.props.instanceId; }
}

export class ContextProps extends PropertyBag  {
  /**
   * @param {String} type
   * @param {String} entityId
   * @param {String} locationId
   * @param {String} tabId
   * @param {String} tabUrl
   * @param {String} helpdeskUuid
   * @param undeclaredProps
   */
  constructor({ type, entityId, locationId, tabId, tabUrl, helpdeskUuid, ...undeclaredProps })  {
    super({ type, entityId, locationId, tabId, tabUrl, helpdeskUuid, ...undeclaredProps });
  }

  get type() { return this.props.type; }

  get entityId() { return this.props.entityId; }

  get locationId() { return this.props.locationId; }

  get tabId() { return this.props.tabId; }

  get tabUrl() { return this.props.tabUrl; }

  // eslint-disable-line no-unused-vars
  get helpdeskUuid() { return this.props.helpdeskUuid; }
}
