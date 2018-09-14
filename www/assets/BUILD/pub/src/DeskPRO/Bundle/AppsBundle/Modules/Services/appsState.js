/**
 * @param widgetId
 * @param {{ badgeCount:Number, badgeVisibility: String }} state
 * @param appState
 * @return {{}}
 */
export function setWidgetState(widgetId, state, appState) {
  return {
    ...appState,
    [widgetId]: JSON.parse(JSON.stringify(state)),

  };
}

/**
 * @param {{}} appState
 * @return {number}
 */
export function getWidgetBadgeTotalCount(appState) {
  return Object.keys(appState).reduce((acc, widgetId) => {
    const { badgeCount, badgeVisibility } = appState[widgetId];
    return badgeVisibility === 'visible' && badgeCount ? acc + parseInt(badgeCount, 10) : acc;
  }, 0);
}

/**
 * @param {String} widgetId
 * @param {{}} appState
 * @return {Number}
 */
export function getWidgetBadgeCount(widgetId, appState) {
  const state = appState[widgetId];
  if (state && typeof state === 'object') {
    const { badgeCount, badgeVisibility } = state;
    if (badgeVisibility === 'visible') {
      return badgeCount;
    }
  }

  return 0;
}
