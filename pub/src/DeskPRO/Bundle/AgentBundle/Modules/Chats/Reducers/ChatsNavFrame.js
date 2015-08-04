import { Reducer } from 'Ampliflux/reducers';

export default class ChatsNavFrame extends Reducer {
  getInitialState() {
    return {
    	chatsList: [],
    };
  }

  registerHandlers() {
  	this
  	  .r('CHATS_LOAD_LIST', this.setPayload('chatsList'));
  }
}
