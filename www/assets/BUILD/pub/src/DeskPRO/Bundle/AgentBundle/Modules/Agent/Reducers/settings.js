import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'Ampliflux/reducers/handlers';
import { setAgentSettings, updateFilterGrouping } from '../Actions/settingsActions';

const initialState = {};
export default createReducer(initialState, {
  [setAgentSettings]:     setFullPayload(),
  [updateFilterGrouping]: (state, { id, groupBy }) => state.setIn(['tickets', 'filter_groupings', String(id)], groupBy)
});
