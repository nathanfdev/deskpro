import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info:            {},
  assets:          {},
  phrases:         {},
  template:        {},
  currentLanguage: window.DP_PERSON_LANG_CODE,
  currentTemplate: null,
  variables:       null,
};

export default createReducer(initialState, {
  [actions.loadAssets]: async({
    success: setFullPayload('assets')
  }),
  [actions.loadPhrases]: async({
    success: setFullPayload('phrases')
  }),
  [actions.setTemplate]:   setFullPayload('template'),
  [actions.loadTemplates]: async({
    success: setFullPayload('info')
  }),
  [actions.loadVariables]: async({
    success: setFullPayload('variables')
  }),
  [actions.setPreview]:        setFullPayload('preview'),
  [actions.loadExampleTicket]: async({
    success: setFullPayload('exampleTicket')
  }),
  [actions.deleteTemplate]:          state => state.delete('template').delete('preview').delete('currentTemplate'),
  [actions.removeVariables]:         state => state.set('variables', null),
  [actions.setCurrentLanguage]:      (state, payload) => state.set('currentLanguage', payload),
  [actions.setCurrentTemplate]:      (state, payload) => state.set('currentTemplate', payload),
  [actions.setExtraTemplate]:        (state, payload) => state.setIn(['template', 'extra_templates', payload.name], payload.code),
  [actions.setTag]:                  (state, payload) => state.setIn(['template', 'tags', payload.name], payload.info),
  [actions.unselectTemplate]:        state => state.delete('template'),
  [actions.deletePreview]:           state => state.delete('preview'),
  [actions.setCurrentTemplateGroup]: (state, payload) => state.set('currentTemplateGroup', payload),
  [actions.updateTemplateCode]:      (state, payload) => state.setIn(['template', 'template_code', 'code'], payload),
  [actions.saveTemplate]:            (state, payload) => state.setIn(['template', 'original_code', 'code'], payload),
  [actions.cleanState]:              state => state.delete('template').delete('preview').delete('currentTemplate'),
  [actions.cleanExtraTemplates]:     state => state.deleteIn(['template', 'extra_templates'])
});
