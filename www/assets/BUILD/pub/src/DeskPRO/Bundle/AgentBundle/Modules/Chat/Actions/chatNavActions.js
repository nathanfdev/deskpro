import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { releaseCollection, setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'chatNav';

export const initialLoad = createAction(
  'CHAT_INITIAL_LOAD',
  () => (dispatch) => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
              + '?get[my]=DP_API/user_chats/counts?group_by%3Ddate_period'
              + '&get[all]=DP_API/user_chats/counts?group_by%3Dagent'
              + '&get[agents]=DP_API/agents/assigned_to_chat'
        ;
      api.sendGet(batch).success(({ responses }) => {
        const payload = flattenBatchResponses(responses);
        dispatch(setCollection('Agent', 'all_chat', payload.agents));
        delete payload.agents;

        resolve(payload);
      });
    }
  )
);

export const loadCounts = createAction(
  'CHAT_NAV_LOAD_CONVERSATIONS_COUNTS',
  (groupBy, list) => repository('UserChat').loadCounts(groupBy, (list === 'my' ? 'me' : null)).then(promise => {
    const res = promise.getData();

    return { list, counts: res.data };
  })
);

export const toggleListGroupingVisibility = createAction(
  'CHAT_NAV_TOGGLE_LIST_GROUPING_VISIBILITY'
);

export const changeListGrouping = createAction(
  'CHAT_NAV_CHANGE_LIST_GROUPING',
  (list, groupBy) => dispatch => {
    dispatch(loadCounts(list, groupBy));
    return { list, groupBy };
  }
);

export const unmount = createAction(
  'CHAT_NAV_UNMOUNT',
  () => dispatch => {
    dispatch(releaseCollection('Department', recordStoresId));
  }
);
