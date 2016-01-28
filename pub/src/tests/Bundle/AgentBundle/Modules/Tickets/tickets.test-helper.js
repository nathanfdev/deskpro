import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';
import { ticketsNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/nav';

function fakeTicketsState(state) {
  return fakeState({
    Tickets: {
      nav: toImmutable(ticketsNavInitialState)
    },
    ...state
  });
}

export function renderInTicketsApp(state, jsx, dispatch) {
  return renderInRedux(fakeTicketsState(state), jsx, dispatch);
}