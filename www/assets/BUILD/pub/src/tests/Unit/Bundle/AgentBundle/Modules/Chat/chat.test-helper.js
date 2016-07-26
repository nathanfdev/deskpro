import $ from 'jquery';
import { renderInRedux } from 'Helpers';
import { chatNavInitialState } from 'DemoState/Navigation/chat';
import { chatListInitialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Reducers/list';

export function renderInChatApp(additional, jsx, dispatch) {
  return renderInRedux(
    $.extend(true, {Chat: {list: chatListInitialState}}, chatNavInitialState, additional),
    jsx,
    dispatch
  );
}
