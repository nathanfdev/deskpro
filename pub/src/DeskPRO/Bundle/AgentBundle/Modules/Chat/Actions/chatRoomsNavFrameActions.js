import { createAction } from "Ampliflux/actions";

export const loadChatRoomsList = createAction(
  'CHAT_LOAD_ROOMS_LIST',
  trigger => trigger({
  	my: {
  	  messagesNum: 10,
  	  rooms: [
  	    {title: 'Today', messagesNum: 5}, 
  	    {title: 'Yeserday', messagesNum: 7}, 
  	    {title: 'This Week', messagesNum: 15},
  	  ]
  	},
  	all: {
  	  messagesNum: 33,
  	  rooms: [
  	    {title: 'Person1', messagesNum: 6},
  	    {title: 'Person2', messagesNum: 9},
  	    {title: 'Person3', messagesNum: 17},
  	  ]
  	},
  })
);
