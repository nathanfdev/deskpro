
/**
 * Groups widgets into groups. The first group in the returned list is the main group
 *
 * @param {Array<WidgetConfiguration>} widgetList
 */
export function fromWidgetListToWidgetGroupList(widgetList) {
  const defaultGroup = [];

  /**
   * @param {Object} acc
   * @param {WidgetConfiguration} config
   */
  function reducer(acc, config) {
    const { appSettings, id } = config;

    if (!appSettings || !appSettings.showInTab || appSettings.showInTab === 'default') {
      defaultGroup.push(config);
    } else if (appSettings.showInTab === 'own-tab') {
      acc[id] = [config];
    }

    return acc;
  }

  const namedGroups = widgetList.reduce(reducer, {});
  if (defaultGroup.length === 0) {
    return Object.keys(namedGroups).map(key => namedGroups[key]);
  }

  return [defaultGroup].concat(Object.keys(namedGroups).map(key => namedGroups[key]));
}
