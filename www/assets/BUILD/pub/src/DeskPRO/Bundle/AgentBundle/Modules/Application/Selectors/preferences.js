import { createSelector } from 'reselect';

const stateSelector = state => state.Application.preferences;
export const setupTokenSelector = createSelector(stateSelector, state => state.get('setup_token'));
