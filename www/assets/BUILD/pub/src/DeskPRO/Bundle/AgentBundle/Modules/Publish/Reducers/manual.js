import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/manualListActions';

export const manualsTree = {
  tree: []
};

export default createReducer(manualsTree, {
  [actions.loadTree]: async({
    success: setFullPayload('tree')
  })
});
