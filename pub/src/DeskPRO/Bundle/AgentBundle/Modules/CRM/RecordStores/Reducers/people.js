import * as peopleActions from '../Actions/peopleActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: peopleActions.gcPeople,
    releaseRecordsAction: peopleActions.releasePeople,
    releaseRequestAction: peopleActions.releaseRequest,
    setRequestRecordAction: peopleActions.setPeopleRequest,
    requestRecordsAction: peopleActions.loadPeople
  })
);
