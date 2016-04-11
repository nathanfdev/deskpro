import { renderInRedux, fakeState, toImmutable } from 'Helpers';
import { feedbackNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Reducers/nav';
import { feedbackListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Reducers/list';
import { massActionsInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/massActions';

function fakeFeedbackState(state) {
  return fakeState({
    Application: { massActions: massActionsInitialState },
    Feedback:    {
      nav:  toImmutable(feedbackNavInitialState),
      list: toImmutable(feedbackListInitialState)
    },
    ...state
  });
}

export function renderInFeedbackApp(state, jsx, dispatch) {
  return renderInRedux(fakeFeedbackState(state), jsx, dispatch);
}