import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info:                 {},
  inlineImages:         {},
  attachments:          {},
  phrases:              {},
  legacyTemplates:      {},
  template:             {},
  currentLanguage:      window.DP_PERSON_LANG_CODE,
  currentTemplate:      null,
  currentTemplateGroup: 'user',
  emailPhrases:         null,
  exampleTicket:        null,
  legacyTemplate:       null,
  preview:              null,
  variables:            null,
};

export default createReducer(initialState, {
  [actions.loadAttachments]: async({
    success: setFullPayload('attachments')
  }),
  [actions.loadInlineImages]: async({
    success: setFullPayload('inlineImages')
  }),
  [actions.loadPhrases]: async({
    success: setFullPayload('phrases')
  }),
  [actions.setTemplate]:   setFullPayload('template'),
  [actions.loadTemplates]: async({
    success: setFullPayload('info')
  }),
  [actions.setLegacyTemplate]:   setFullPayload('legacyTemplate'),
  [actions.loadLegacyTemplates]: async({
    success: setFullPayload('legacyTemplates')
  }),
  [actions.loadVariables]: async({
    success: setFullPayload('variables')
  }),
  [actions.setPreview]:        setFullPayload('preview'),
  [actions.loadExampleTicket]: async({
    success: setFullPayload('exampleTicket')
  }),
  [actions.removeVariables]:         state => state.set('variables', null),
  [actions.setCurrentLanguage]:      (state, payload) => state.set('currentLanguage', payload),
  [actions.setCurrentTemplate]:      (state, payload) => state.set('currentTemplate', payload),
  [actions.setExtraTemplate]:        (state, payload) => state.setIn(['template', 'extra_templates', payload.name], payload.code),
  [actions.unselectTemplate]:        state => state.delete('template'),
  [actions.deletePreview]:           state => state.delete('preview'),
  [actions.setCurrentTemplateGroup]: (state, payload) => state.set('currentTemplateGroup', payload),
  [actions.updateTemplateSubject]:   (state, payload) => state.setIn(['template', 'template_code', 'subject'], payload),
  [actions.updateTemplateBody]:      (state, payload) => state.setIn(['template', 'template_code', 'body'], payload),
  [actions.updateTemplateCode]:      (state, payload) => state.setIn(['template', 'template_code', 'code'], payload),
  [actions.cleanState]:              state => state.clear(),
  [actions.cleanExtraTemplates]:     state => state.deleteIn(['template', 'extra_templates'])
});
