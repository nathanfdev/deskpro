import $ from 'jquery';
import { renderInRedux } from 'helpers';
import { ticketsNavInitialState } from 'DemoState/Navigation/tickets';
import { ticketsListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/list';

export function renderInTicketsApp(additional, jsx, dispatch) {
  return renderInRedux(
    $.extend(true, {Tickets: {list: ticketsListInitialState}}, ticketsNavInitialState, additional),
    jsx,
    dispatch
  );
}
