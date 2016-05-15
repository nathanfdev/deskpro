import { newActionAlerts } from '../Modules/Application/Actions/notificationActions';
import { startChat } from '../Modules/IM/Actions/chatsActions';
import { markMessages } from '../Modules/IM/Actions/messagesActions';
import { addToCollection } from '../../AppBundle/Modules/RecordsStore/Actions/store';

export class ActionAlertsHandler
{
  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
  }

  handle(payload) {
    const { data, linked } = payload.data;
    switch (payload.type) {
      case 'notification.agent_chat.new_message':
        this.options.dispatch(addToCollection('AgentChat', 'recent', [linked.agent_chat[data.chat]]));
        this.options.dispatch(markMessages([data.id], [data.uuid], data.chat, 1));
        this.options.dispatch(startChat(null, null, data.chat, true));
        break;
      default:
        break;
    }
    this.options.dispatch(newActionAlerts(payload));
  }
}
