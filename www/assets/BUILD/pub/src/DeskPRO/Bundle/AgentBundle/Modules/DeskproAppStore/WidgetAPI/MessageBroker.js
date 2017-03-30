import * as Messages from "./Messages";

class MessageBroker
{
  /**
   * @param {MessageGateway} gateway
   */
  constructor(gateway)
  {
    this.gateway = gateway;
  }

  bindWidget = (widget, widgetWindow, subscriptionId, subscribeToEventsList) => {

    const validSubscriptions = subscribeToEventsList.filter(subscription => this.isValidMessageSubscription(subscription));
    if (validSubscriptions.length === 0) {
      return false;
    }

    //bind to window message channels
    const { gateway } = this;
    validSubscriptions.forEach(subscription => {
      const eventName = typeof subscription == 'string' ? subscription : subscription.eventName;

      if (typeof subscription == 'string') {
        gateway.messageChannelForEvent(eventName, widget).bind(widgetWindow, subscriptionId);
      } else {
        gateway.messageChannelForEvent(eventName, widget).bindWithHandler(
          widgetWindow
          , subscriptionId
          , subscription.requestHandler
        );
      }
    });

  };

  isValidMessageSubscription = (subscription) => {

    let valid = typeof subscription == 'string' && Messages.isEventName(subscription);
    if (valid) {
      return valid;
    }

    valid = typeof subscription == 'object'
      && subscription.hasOwnProperty('eventName')
      && Messages.isEventName(subscription.eventName)
      && subscription.hasOwnProperty('requestHandler')
      && typeof subscription.requestHandler == 'function'
    ;

    return valid;
  }
}

export default MessageBroker;
