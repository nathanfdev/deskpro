import { createSelector } from 'reselect';
const stateSelector = state => state.Chat.nav;

export const isLoadedSelector = createSelector(
  stateSelector,
    list => list.getIn(['async', 'done'])
);

export const myChatsSelector = createSelector(
  stateSelector,
    list => list.get('my')
);

export const allChatsSelector = createSelector(
  stateSelector,
    list => list.get('all')
);

