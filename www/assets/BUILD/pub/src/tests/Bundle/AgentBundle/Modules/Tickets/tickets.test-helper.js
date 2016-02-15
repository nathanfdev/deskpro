import { renderInRedux, fakeState, toImmutable } from 'Helpers';
import { ticketsNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/nav';
import { ticketsListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/list';

function fakeTicketsState(state) {
  return fakeState({
    Tickets: {
      nav: toImmutable(ticketsNavInitialState),
      list: toImmutable(ticketsListInitialState)
    },
    ...state
  });
}

export function renderInTicketsApp(state, jsx, dispatch) {
  return renderInRedux(fakeTicketsState(state), jsx, dispatch);
}