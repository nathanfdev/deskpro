import { newActionAlerts } from '../Modules/Application/Actions/notificationActions.js';
import { startChat } from '../Modules/IM/Actions/chatsActions';
import { markMessages } from '../Modules/IM/Actions/messagesActions';
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
        this.options.dispatch(markMessages([data.data.id], data.data.agent_chat_id, 1));
        this.options.dispatch(startChat(null, null, data.data.agent_chat_id, true));
        break;
      default:
        break;
    }
  }
}
