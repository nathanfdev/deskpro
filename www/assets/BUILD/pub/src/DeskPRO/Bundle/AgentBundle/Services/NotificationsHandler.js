import Notify from 'notifyjs';
import striptags from 'striptags';
import $ from 'jquery';
import emojione from 'emojione';
import Message from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/ChatWindow/Message';

class NotificationsHandler {
  constructor(props) {
    this.options = {};
    Object.assign(this.options, props);
  }

  handle(payload) { // eslint-disable-line class-methods-use-this
    const { data } = payload;
    switch (payload.type) {
      case 'notification.agent_chat.new_message':
        if (!NotificationsHandler.active && !Notify.needsPermission) {
          const summary = striptags(emojione.shortnameToUnicode(Message.formatMessage(data.summary)));
          const notification = new Notify(
            data.title,
            {
              body:    summary,
              timeout: 5,
              icon:    data.icon
            }
          );
          notification.show();
        }
        break;
      default:
        break;
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
