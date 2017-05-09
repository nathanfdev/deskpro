import { newActionAlerts } from '../Modules/Application/Actions/notificationActions';
import { startChat } from '../Modules/IM/Actions/chatsActions';
import { markMessages } from '../Modules/IM/Actions/messagesActions';
import { addToCollection, updateCollection } from '../../AppBundle/Modules/RecordsStore/Actions/store';

class ActionAlertsHandler {
  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
  }

  handle(payload) {
    const { data, linked } = payload.data;
    const records = [];
    switch (payload.type) {
      case 'notification.agent_chat.new_message':
        if (linked.agent_chat[data.chat].chat_type === 'department') {
          this.options.dispatch(addToCollection('Department', 'my', linked.department));
        }
        if (linked.agent_chat[data.chat].chat_type === 'team') {
          this.options.dispatch(addToCollection('AgentTeam', 'my', linked.agent_team));
        }
        if (linked.agent_chat[data.chat].chat_type === 'group') {
          this.options.dispatch(addToCollection('AgentChat', 'group', linked.agent_chat[data.chat]));
        }
        this.options.dispatch(addToCollection('AgentChat', 'recent', [linked.agent_chat[data.chat]]));
        this.options.dispatch(markMessages([data.id], [data.uuid], data.chat, 1));
        if (!(data.metadata.mention && data.person === this.options.me)) {
          this.options.dispatch(startChat(null, data.chat, true));
        }
        break;
      case 'agents.update_online':
        if (payload.data.online) {
          payload.data.online.forEach((item) => {
            records.push({ id: item, online: true });
          });
        }
        if (payload.data.offline) {
          payload.data.offline.forEach((item) => {
            records.push({ id: item, online: false });
          });
        }
        this.options.dispatch(updateCollection('Person', records, 'merge'));
        break;
      case 'organization.added':
        ActionAlertsHandler.handleLegacyClientMessage(payload.data);
        break;
      default:
        ActionAlertsHandler.handleLegacyClientMessage(payload.data);
    }
    this.options.dispatch(newActionAlerts(payload));
  }

  static handleLegacyClientMessage(payload) {
    DeskPRO_Window.messageBroker.sendMessage(payload.type, payload); // eslint-disable-line no-undef
  }
}

export default ActionAlertsHandler;
