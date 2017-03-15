import { createSelector } from 'reselect';
import Immutable from 'immutable';

const selectorsMap = new Map();
function resolveTargetConfig (instanceConfig, target) {
  if (instanceConfig.hasOwnProperty(target)) {
    return instanceConfig[target];
  }

  return [];
}

export const filterInstanceConfig =  ({DeskproAppStore: {Main:state}}) => state.get('instances').toJS();

/**
 * @param {String} target
 * @return {Array<Object>}
 */
export const selectInstanceConfigByTarget = (target) => {

  if (selectorsMap.has(target)) {
    return selectorsMap.get(target);
  }

  const selector = createSelector(filterInstanceConfig, instanceConfig => resolveTargetConfig(instanceConfig, target));
  selectorsMap.set(target, selector);

  return selector;
};

