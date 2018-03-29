import debounce from 'lodash/debounce';
import clone from 'lodash/clone';
import { newActionAlerts } from '../Modules/Application/Actions/notificationActions';
import { startChat } from '../Modules/IM/Actions/chatsActions';
import { markMessages } from '../Modules/IM/Actions/messagesActions';
import { updateAgentStatus } from '../Modules/Agent/Actions/agentActions';
import { addToCollection, updateCollection, removeFromCollection } from '../../AppBundle/Modules/RecordsStore/Actions/store';
import * as snippetActions from '../Modules/Snippets/Actions/snippetsActions';
import * as filtersActions from '../Modules/Filters/Actions/filterActions';

/* eslint no-undef: "warn" */

class ActionAlertsHandler {
  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
  }

  handle(payload) {
    const { data, linked } = payload.data;
    const records = [];
    switch (payload.type) {
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
        this.handleLegacyClientMessage(payload.data);
        break;
      case 'helpdesk.agent.refresh_interface': {
        const { who, message, isIgnoreAllowed, reasonCode } = payload.data;
        if (reasonCode === 'upgrade_complete') {
          if (!window.DeskPRO_Window.update_running) {
            // the upgrading message was never displayed,
            // show a fake one now and then refresh
            $('#reload_overlay').show();
            $('#reload_overlay_updates').show();
          }

          // on a slight delay to let any offline trigger files to be unset
          window.setTimeout(() => window.location.reload(false), 5000);
        } else {
          window.DeskPRO_Window.showRefreshAlert(who, message, isIgnoreAllowed);
        }
      }
        break;
      // from DeskPRO/Bundle/AgentBundle/Services/Helpers/MessagesHelper.js
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
        this.options.dispatch(newActionAlerts(payload));
        break;
      case 'notification.agent_chat.mark_message':
      case 'notification.agent_chat.mark_all_messages':
      case 'read.notifications.alert':
        this.options.dispatch(newActionAlerts(payload));
        break;
      case 'ticket.follow_up_updated': {
        const event = new CustomEvent('dpFollowUpUpdate', { detail: { ticketId: payload.data.ticket_id } });
        window.document.dispatchEvent(event);
        break;
      }
      case 'snippet.snippets_updated':
        switch (payload.data.action) {
          case 'update':
            this.handleNewSnippet(payload.data.snippet_id);
            break;
          case 'remove':
            this.options.dispatch(removeFromCollection('Snippets', 'all', [payload.data.snippet_id]));
            break;
          default:
            break;
        }
        break;
      case 'agent.update_status':
        this.options.dispatch(updateAgentStatus(payload.data));
        break;
      case 'agent.filter-update':
        payload.data.operations.forEach((datum) => {
          const newData = clone(datum);
          newData.eventType = payload.data.eventType;
          newData.ticket_id = payload.data.ticket_id;
          if (window.DP_HAS_NEW_FILTERS) {
            this.handleFilterUpdate(newData);
          }
          this.handleLegacyBroadcastClientMessage(newData, true);
        });
        break;
      default:
        if (payload.data && payload.data.eventType) {
          this.handleLegacyClientMessage(payload.data);
        } else {
          this.options.dispatch(newActionAlerts(payload));
        }
    }
  }

  snippetIds = new Set();

  loadBatch = () => {
    this.options.dispatch(snippetActions.getSnippets([...this.snippetIds])).then((snippets) => {
      const shortcutCodes = window.DESKPRO_TICKET_SNIPPET_SHORTCODES;
      snippets.forEach((snippet) => {
        if (shortcutCodes[snippet.shortcut_code] && shortcutCodes[snippet.shortcut_code].indexOf(snippet.id) === -1) {
          shortcutCodes[snippet.shortcut_code] = shortcutCodes[snippet.shortcut_code].concat([snippet.id]);
        } else if (!shortcutCodes[snippet.shortcut_code]) {
          shortcutCodes[snippet.shortcut_code] = [snippet.id];
        }
      });
    });
    this.snippetIds.clear();
  };

  debouncedLoad = debounce(() => this.loadBatch(), 1000);

  handleNewSnippet(snippetId) {
    this.snippetIds.add(snippetId);
    this.debouncedLoad();
  }

  handleLegacyClientMessage(payload, checkTarget = false) {
    if (!payload.eventType) {
      console.error('payload.eventType is not set', payload);
    } else if (!checkTarget || (checkTarget && payload.target === this.options.me)) {
      DeskPRO_Window.messageBroker.sendMessage(payload.eventType, payload); // eslint-disable-line no-undef
    }
  }

  handleLegacyBroadcastClientMessage(payload, checkTarget = false) {
    if (!payload.eventType) {
      console.error('payload.eventType is not set', payload);
    } else if (!checkTarget || (checkTarget && payload.targets && payload.targets.indexOf(this.options.me) !== -1)) {
      DeskPRO_Window.messageBroker.sendMessage(payload.eventType, payload); // eslint-disable-line no-undef
    }
  }

  handleFilterUpdate(payload) {
    switch (payload.op) {
      case 'del':
        this.options.dispatch(filtersActions.decreaseCount(payload.filter_id));
        break;
      case 'add':
        this.options.dispatch(filtersActions.increaseCount(payload.filter_id));
        break;
      default:
        break;
    }
  }
}

export default ActionAlertsHandler;
