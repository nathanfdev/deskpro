import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, mergeFullPayload } from 'Ampliflux/reducers/handlers';

const initialState = {
  chat: {
    messages: [
      {
        type: 'agent',
        message: 'I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.'
      },
      {
        type: 'user',
        message: 'I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.'
      },
      {
        type: 'user',
        message: 'I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.'
      },
      {
        type: 'agent',
        message: 'I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.'
      },
      {
        type: 'user',
        message: 'I have already written a view to show the current order status of every order that was touched today. I based it off of the view that exists in the system that is based off of the audit table.'
      }
    ]
  }
};

export default createReducer(initialState, {
  [actions.createChat]: async({
    success: mergeFullPayload('chat')
  }),
  [actions.pollingChat]: async({
    success: mergeFullPayload('chat')
  })
});
