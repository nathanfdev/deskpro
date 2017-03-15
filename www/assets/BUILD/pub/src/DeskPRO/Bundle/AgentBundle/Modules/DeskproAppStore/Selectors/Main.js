import { createSelector } from 'reselect';
import Immutable from 'immutable';

const selectorsMap = new Map();
function resolveTargetConfig (appConfig, target) {
  if (appConfig instanceof Immutable.Map) {
    return appConfig.get(target).toJS();
  }

  // TODO better error message
  throw new Error('unexpected type');
}

export const filterAppConfig =  ({DeskproAppStore: {Main:state}}) => state.get('appconfig');
export const selectDeskproAppsConfigByTarget = (target) => {

  if (selectorsMap.has(target)) {
    return selectorsMap.get(target);
  }

  const selector = createSelector(filterAppConfig, appConfig => resolveTargetConfig(appConfig, target));
  selectorsMap.set(target, selector);

  return selector;
};

