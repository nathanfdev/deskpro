import { Reducer } from 'Ampliflux/reducers';

export default class ChatRoomsNavFrame extends Reducer {
  getInitialState() {
    return {
      groups: {
      	my:  {messagesNum: 0, rooms: [/* {title, messagesNum} */]},
      	all: {messagesNum: 0, rooms: [/* {title, messagesNum} */]},
      },
    };
  }

  registerHandlers() {
    this
      .r('CHAT_LOAD_ROOMS_LIST', this.setPayload('groups'));
  }
}
