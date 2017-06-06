export class EventSubscribersRegistry
{
  constructor() {
    this.state = { subscribers: new Map() };
  }

  /**
   * @param {String} eventName
   * @param {Widget} widget
   */
  addSubscriber = (eventName, widget) =>
  {
    /** @var {Map} eventSubscribers */
    const { subscribers } = this.state;
    const firstTime = subscribers.has(eventName);

    const subscribersList = firstTime ? eventSubscribers.get(eventName) : new Set();
    subscribersList.add(widget);

    if (firstTime) {
      subscribers.set(eventName, subscribersList);
    }
  };

  /**
   * @param {String} eventName
   * @param {Widget} widget
   * @return boolean
   */
  isSubscriber = (eventName, widget) =>
  {
    /** @var {Map} eventSubscribers */
    const { subscribers } = this.state;
    const subscribersList = subscribers.has(eventName) ? eventSubscribers.get(eventName) : null;

    if (! subscribersList) { return false; }
    return subscribersList.has(widget);
  };

  /**
   * @param {String} eventName
   * @return Array<Widget>
   */
  getSubscribers = (eventName) =>
  {
    /** @var {Map} eventSubscribers */
    const { subscribers } = this.state;
    const subscribersList = subscribers.has(eventName) ? eventSubscribers.get(eventName) : new Set();
    return [...subscribersList.values()];
  }
}
