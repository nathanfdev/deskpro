import { createSelector } from "reselect";
import Immutable from 'immutable';

export const users = state => state.Common.users;

export const userStatus = createSelector(
  [users],
  users => users.get('status')
);
export const userRecs = createSelector(
  [users],
  users => users.get('records')
);
export const userRequests = createSelector(
  [users],
  users => users.get('requests')
);

export const requestStatus = (requestId) => createSelector(
  [userStatus],
  status => (Immutable.Map({
    isLoading: status.getIn([requestId, 'isLoading']),
    isDone:    status.getIn([requestId, 'isDone'])
  }))
);

export const requestIds = (requestId) => createSelector(
  [userRequests],
  userRequests => userRequests.get(requestId, Immutable.List())
);

export const requestRecords = (requestId) => createSelector(
  [userRecs, requestIds(requestId)],
  (records, ids) => {
    return records.filter(r => ids.includes(r.get('id')));
  }
);
