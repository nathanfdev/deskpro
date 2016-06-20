import { renderInRedux, fakeState, toImmutable } from 'helpers';
import { crmNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/nav';
import { crmListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/list';
import { massActionsInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/massActions';

function fakeCrmState(state) {
  return fakeState({
    Application: { massActions: massActionsInitialState },
    CRM:         {
      nav:  toImmutable(crmNavInitialState),
      list: toImmutable(crmListInitialState)
    },
    ...state
  });
}

export function renderInCrmApp(state, jsx, dispatch) {
  return renderInRedux(fakeCrmState(state), jsx, dispatch);
}