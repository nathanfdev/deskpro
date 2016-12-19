import { createSelector } from 'reselect';

const stateSelector = state => state.Chat.nav;
export const isLoadedSelector = createSelector(stateSelector, state => state.getIn(['async', 'done']));
export const countsSelector = createSelector(stateSelector, state => state.get('counts'));
