import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import * as PeopleNamesActions from '../Actions/PeopleNamesActions';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction:               PeopleNamesActions.gcPeopleNames,
    releaseRecordsAction:   PeopleNamesActions.releasePeopleNames,
    releaseRequestAction:   PeopleNamesActions.releasePeopleNamesRequest,
    setRequestRecordAction: PeopleNamesActions.setPeopleNamesRequest,
    requestRecordsAction:   PeopleNamesActions.loadPeopleNames
  })
);
