import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/guideListActions';

export const guidesTree = {
  tree: []
};

export default createReducer(guidesTree, {
  [actions.loadTree]: async({
    success: setFullPayload('tree')
  })
});
