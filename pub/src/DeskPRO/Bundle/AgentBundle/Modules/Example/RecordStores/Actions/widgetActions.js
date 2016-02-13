import { createAction } from 'Ampliflux';
import Immutable from 'immutable';

import * as recordStoreActions from 'Ampliflux/common/record-store/actions';

export const release = createAction('EG_WIDGETS_RELEASE', recordStoreActions.releaseRecords());
export const releaseRequest = createAction('EG_WIDGETS_RELEASE_REQ', recordStoreActions.releaseRequest());
export const setWidgets = createAction('EG_WIDGETS_SET', recordStoreActions.setRequestRecords());

// note: there is no arbitrary loadWidgets as the UI is always based around filtering
