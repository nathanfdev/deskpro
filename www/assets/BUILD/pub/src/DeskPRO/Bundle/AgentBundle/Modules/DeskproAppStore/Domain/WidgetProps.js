export class InstanceProps {
  /**
   * @param {String} appId
   * @param {String} appTitle
   * @param {String} appPackageName
   * @param {String} instanceId
   */
  constructor({ appId, appTitle, appPackageName, instanceId })  {
    this.props = { appId, appTitle, appPackageName, instanceId };
  }

  get appId() { return this.props.appId; }

  get appTitle() { return this.props.appTitle; }

  get appPackageName() { return this.props.appPackageName; }

  get instanceId() { return this.props.instanceId; }

  toJS = () => JSON.parse(JSON.stringify(this.props));

}

export class ContextProps {
  /**
   * @param {String} type
   * @param {String} entityId
   * @param {String} locationId
   * @param {String} tabId
   * @param {String} tabUrl
   */
  constructor({ type, entityId, locationId, tabId, tabUrl })  {
    this.props = { type, entityId, locationId, tabId, tabUrl };
  }

  get type() { return this.props.type; }

  get entityId() { return this.props.entityId; }

  get locationId() { return this.props.locationId; }

  get tabId() { return this.props.tabId; }

  get tabUrl() { return this.props.tabUrl; }

  toJS = () => JSON.parse(JSON.stringify(this.props));
}
