import { renderInRedux, fakeState } from 'helpers';
import { crmNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/nav';
import { crmListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/list';
import { massActionsInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/massActions';

function fakeCrmState(state) {
  return fakeState({
    CRM: {
      nav:  crmNavInitialState,
      list: crmListInitialState
    },
    ...state
  });
}

export function renderInCrmApp(state, jsx, dispatch) {
  return renderInRedux(fakeCrmState(state), jsx, dispatch);
}