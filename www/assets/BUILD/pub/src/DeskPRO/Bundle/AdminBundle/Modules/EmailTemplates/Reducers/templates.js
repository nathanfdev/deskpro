import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info: {}
};

export default createReducer(initialState, {
  [actions.loadTemplates]: async({
    success: setFullPayload('info')
  })
});
