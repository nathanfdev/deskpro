import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info:                 {},
  phrases:              {},
  template:             {},
  currentLanguage:      'en',
  currentTemplate:      null,
  currentTemplateGroup: 'user',
  emailPhrases:         null,
  variables:            null,
};

export default createReducer(initialState, {
  [actions.loadTemplates]: async({
    success: setFullPayload('info')
  }),
  [actions.loadPhrases]: async({
    success: setFullPayload('phrases')
  }),
  [actions.loadTemplate]: async({
    success: setFullPayload('template')
  }),
  [actions.loadVariables]: async({
    success: setFullPayload('variables')
  }),
  [actions.removeVariables]:         state => state.set('variables', null),
  [actions.setCurrentLanguage]:      (state, payload) => state.set('currentLanguage', payload),
  [actions.setCurrentTemplate]:      (state, payload) => state.set('currentTemplate', payload),
  [actions.setCurrentTemplateGroup]: (state, payload) => state.set('currentTemplateGroup', payload),
  [actions.updateTemplateSubject]:   (state, payload) => state.setIn(['template', 'template_code', 'subject'], payload),
  [actions.updateTemplateBody]:      (state, payload) => state.setIn(['template', 'template_code', 'body'], payload),
  [actions.cleanState]:              state => state.clear()
});
