import { renderInRedux, fakeState, toImmutable } from 'Helpers';
import { feedbackNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Reducers/nav';
import { feedbackListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Reducers/list';
import { massActionsInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/massActions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

export function fakeFeedbackState(state) {
  const fakePeople = [{ id: 1 }];

  const fakeRecords = [
    { id: 1, category: 1, person: 1, num_comments: 12, title: 'record 1' },
    { id: 2, category: 1, person: 1, num_comments: 2, title: 'record 2' },
    { id: 3, category: 1, person: 1, num_comments: 7, title: 'record 3' }
  ];

  const fakeTypes = [{ display_order: 0, id: 1, title: 'Suggestion' }];

  let newstate =  fakeState({
    Application: { massActions: massActionsInitialState },
    Feedback:    {
      nav:  toImmutable(feedbackNavInitialState),
      list: toImmutable(feedbackListInitialState)
    },
    RecordsStore: {
      store: toImmutable({

        Person: {
          collections: { feedback: [1] },
          records:     mapKeyedFromArray(fakePeople, 'id'),
          statuses:    { loading: false, success: true }
        },

        Feedback: {
          collections: { feedback: [1] },
          records:     mapKeyedFromArray(fakeRecords, 'id'),
          statuses:    { loading: false, success: true }
        },

        FeedbackType: {
          collections: { feedback: [1] },
          records:     mapKeyedFromArray(fakeTypes, 'id'),
          statuses:    { loading: false, success: true }
        }
      })
    },
    ...state
  });

  newstate.Feedback.list = newstate.Feedback.list.set('elements', toImmutable([1]));

  return newstate;
}

export function renderInFeedbackApp(state, jsx, dispatch) {
  return renderInRedux(fakeFeedbackState(state), jsx, dispatch);
}
