import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { releaseCollection, setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export const initialLoad = createAction(
  'CHAT_INITIAL_LOAD',
  () => (dispatch, getState) => new Promise(
    (resolve) => {
      const currentMyGrouping  = hashStateSelectorFactory(['group', 'my'], 'date_period')(getState());
      const currentAllGrouping = hashStateSelectorFactory(['group', 'all'], 'date_period')(getState());
      const batchComponents    = {
        agents: { endpoint: 'agents/assigned_to_chat' },
        my:     { endpoint: 'user_chats/counts', query: `group_by=${currentMyGrouping}` },
        all:    { endpoint: 'user_chats/counts', query: `group_by=${currentAllGrouping}` }
      };

      const batch = api.prepareParams(batchComponents);
      api.sendGet(batch).success(({ responses }) => {
        const data = flattenBatchResponses(responses);
        dispatch(setCollection('Agent', 'all_chat', data.agents));
        resolve({counts: [
          {id: 'my', title: 'My Chats', ...data.my},
          {id: 'all', title: 'All Chats', ...data.all}
        ]});
      });
    }
  )
);

export const reloadCount = createAction(
  'CHAT_NAV_LOAD_CONVERSATIONS_COUNTS',
  (countId, groupBy) => repository('UserChat').loadCounts(groupBy, (countId === 'my' ? 'me' : null)).then(promise => {
    const res = promise.getData();

    return { countId, count: res.data };
  })
);

export const changeCountGrouping = createAction(
  'CHAT_NAV_CHANGE_LIST_GROUPING',
  (countId, groupBy) => dispatch => {
    dispatch(reloadCount(countId, groupBy));

    return { countId, groupBy };
  }
);
