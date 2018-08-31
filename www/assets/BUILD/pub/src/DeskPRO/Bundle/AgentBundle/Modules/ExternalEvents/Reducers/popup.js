import { createReducer } from 'Ampliflux';
import { newActionAlerts } from '../../Application/Actions/notificationActions';

const initialState = {
  me:        {},
  popupOpen: false,
  popupData: {}
};

export default createReducer(initialState, {
  [newActionAlerts]: (state, payload) => {
    let newState = state;
    switch (payload.type) { // eslint-disable-line default-case
      // that't it, we don't need default here at all, default is just no-op
      case 'external_event.popup':
        newState = newState.set('popupOpen', payload.data.action === 'raise');
        newState = newState.set('popupData', payload.data.action === 'raise' ? payload.data : {});
        break;
    }
    return newState;
  }
});
