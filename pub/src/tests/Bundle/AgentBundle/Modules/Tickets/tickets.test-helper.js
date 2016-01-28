import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';
import { ticketsNavInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/nav';

function fakeTicketsState() {
  return fakeState({
    Tickets: {
      nav: toImmutable(ticketsNavInitialState)
    }
  });
}

export function renderInTicketsApp(jsx, dispatch) {
  return renderInRedux(fakeTicketsState(), jsx, dispatch);
}