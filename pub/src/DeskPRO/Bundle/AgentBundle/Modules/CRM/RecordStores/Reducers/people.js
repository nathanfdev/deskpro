import * as peopleActions from '../Actions/peopleActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import { newActionAlerts } from '../../../Application/Actions/notificationActions';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: peopleActions.releasePeople,
    releaseRequestAction: peopleActions.releasePeopleRequest,
    setRequestRecordAction: peopleActions.setPeopleRequest,
    requestRecordsAction: peopleActions.loadPeople,
    updateRecordStateAction: peopleActions.updatePeople
  }),
  {
    [newActionAlerts]: (state, payload) => {
      let newState = state;
      if (payload.type === 'notification.agents.update_online') {
        payload.data.online.map((id) => newState = newState.mergeIn(['records', id], {online: true}));
        payload.data.offline.map((id) => newState = newState.mergeIn(['records', id], {online: false}));
      }

      return newState;
    }
  }
);
