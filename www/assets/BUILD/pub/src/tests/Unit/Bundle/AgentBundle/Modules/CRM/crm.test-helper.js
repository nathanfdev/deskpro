import $ from 'jquery';
import { renderInRedux } from 'Helpers';
import { crmNavInitialState } from 'DemoState/Navigation/crm';
import { crmListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/list';

export function renderInCrmApp(additional, jsx, dispatch) {
  return renderInRedux(
    $.extend(true, {CRM: {list: crmListInitialState}}, crmNavInitialState, additional),
    jsx,
    dispatch
  );
}
