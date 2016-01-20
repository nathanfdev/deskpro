import { newActionAlerts } from '../Modules/Application/Actions/notificationActions.js';
import { startChat } from '../Modules/IM/Actions/chatsActions';
import ChatHelper from '../Modules/IM/ChatHelper';

export class ActionAlertsHandler
{
  constructor(props) {
    this.options = {};
    this.chatHelper = new ChatHelper();
    Object.assign(this.options, props);
  }

  handle(data) {
    this.options.dispatch(newActionAlerts(data));
    switch (data.type) {
      case 'notification.agent_chat.new_message':
        this.options.dispatch(startChat(this.chatHelper.getChatTarget(data.data, this.options.me), data.data.chat_type, data.data.agent_chat_id, true));
        break;
      default:
        break;
    }
  }
}
