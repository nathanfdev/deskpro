import { createAction } from "Ampliflux/actions";

export const loadChatConversationsList = createAction(
  'CHAT_LOAD_CONVERSATIONS_LIST',
  trigger => trigger({
  	my: {
  	  count: 10,
  	  conversations: [
  	    {title: 'Today', count: 5}, 
  	    {title: 'Yeserday', count: 7}, 
  	    {title: 'This Week', count: 15},
  	  ]
  	},
  	all: {
  	  count: 33,
  	  conversations: [
  	    {title: 'Person1', count: 6},
  	    {title: 'Person2', count: 9},
  	    {title: 'Person3', count: 17},
  	  ]
  	},
  })
);
