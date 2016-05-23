import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'Ampliflux/reducers/handlers';
import { setAgentSettings, updateFilterGrouping } from '../Actions/settingsActions';

const initialState = {};
export default createReducer(initialState, {
  [setAgentSettings]:     setFullPayload(),
  [updateFilterGrouping]: (state, { filterId, prefId, groupBy }) => state
    .setIn(['tickets', 'filter_groupings', String(filterId)],
           Immutable.fromJS({ id: prefId, main_grouping: groupBy }))
});
