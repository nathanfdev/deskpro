import { renderInRedux, fakeState } from 'helpers';
import { ticketsNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/nav';
import { ticketsListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/list';

export function fakeTicketsState(state) {
  return fakeState({
    Tickets:     {
      nav:  ticketsNavInitialState,
      list: ticketsListInitialState
    },
    ...state
  });
}

export function renderInTicketsApp(state, jsx, dispatch) {
  return renderInRedux(fakeTicketsState(state), jsx, dispatch);
}