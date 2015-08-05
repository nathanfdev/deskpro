import { Reducer } from 'Ampliflux/reducers';

export default class ChatConversationsNavFrame extends Reducer {
  getInitialState() {
    return {
      groups: {
      	my:  {count: 0, conversations: [/* {title, count} */]},
      	all: {count: 0, conversations: [/* {title, count} */]},
      },
    };
  }

  registerHandlers() {
    this
      .r('CHAT_LOAD_CONVERSATIONS_LIST', this.setPayload('groups'));
  }
}
