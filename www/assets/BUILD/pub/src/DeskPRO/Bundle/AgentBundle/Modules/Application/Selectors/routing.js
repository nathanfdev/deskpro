import { createSelector } from 'reselect';

export const routingStateSelector = state => state.Application.routing.get('hash');

export const hashStateSelectorFactory = ([component, key], defaultValue = null) => {
  return createSelector(
    routingStateSelector,
    state => state.getIn([component, key], defaultValue)
  );
};
