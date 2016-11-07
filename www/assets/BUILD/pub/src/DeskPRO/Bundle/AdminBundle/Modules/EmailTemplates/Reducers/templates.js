import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info:            {},
  currentTemplate: null,
  emailPhrases:    null,
  variables:       null,
};

export default createReducer(initialState, {
  [actions.loadTemplates]: async({
    success: setFullPayload('info')
  }),
  [actions.loadPhrases]: async({
    success: setFullPayload('phrases')
  }),
  [actions.loadVariables]: async({
    success: setFullPayload('variables')
  }),
  [actions.removeVariables]:    state => state.set('variables', null),
  [actions.setCurrentTemplate]: (state, payload) => state.set('currentTemplate', payload),
  [actions.setCurrentLanguage]: (state, payload) => state.set('currentLanguage', payload),
});
