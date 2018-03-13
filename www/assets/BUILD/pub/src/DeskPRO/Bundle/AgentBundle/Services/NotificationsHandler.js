import Notify from 'notifyjs';
import striptags from 'striptags';
import { AllHtmlEntities } from 'html-entities';
import $ from 'jquery';
import emojione from 'emojione';
import Message from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow/Message';
import { startChat } from '../Modules/IM/Actions/chatsActions';

class NotificationsHandler {
  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
  }

  handle(payload) { // eslint-disable-line class-methods-use-this
    const { data } = payload;
    let summary;
    let title;
    let icon;
    let notifyClick;
    if (!NotificationsHandler.active && !Notify.needsPermission) {
      switch (payload.type) {
        case 'notification.agent_chat.new_message':
          title = data.title;
          summary = striptags(emojione.shortnameToUnicode(Message.formatMessage(data.summary)));
          // Froala Editor encode html entities and return encoded message
          // Decode message to have plain message in Web Notification
          // For ex.: `don&#39;t` => `don't`
          summary = AllHtmlEntities.decode(summary);
          icon = data.icon;
          notifyClick = () => {
            this.options.dispatch(startChat(null, data.chat));
          };
          break;
        case 'chat.new':
          title   = data.title;
          summary = data.summary;
          icon    = data.icon;
          notifyClick = () => {}; // nothing really need here
          break;
        default:
          return; // no-op
      }

      const notification = new Notify(
        title,
        {
          body:               summary,
          // don't allow browser to close notification automatically, notification should remain available
          // until the user activates or dismisses the notification or we close by timeout
          // https://notifications.spec.whatwg.org/#require-interaction-preference-flag
          requireInteraction: true,
          icon,
          notifyClick:        () => { notifyClick(); $(window).focus(); }
        }
      );
      notification.show();
      if (!isNaN(window.DESKPRO_PERSON_NOTIFICATION_DISMISS)) {
        // Notify options `requireInteraction` and `timeout` are mutually exclusive
        // have to manually initialize close by timeout
        setTimeout(notification.close.bind(notification), window.DESKPRO_PERSON_NOTIFICATION_DISMISS * 1000);
      }
    }
  }
}

// just a quick wa right now
NotificationsHandler.active = true;
$(window).focus(() => {
  NotificationsHandler.active = true;
});
$(window).blur(() => {
  NotificationsHandler.active = false;
});

export default NotificationsHandler;
