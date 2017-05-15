import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

import * as actions from '../Actions/templatesActions';

const initialState = {
  info:                 {},
  inlineImages:         {},
  attachments:          {},
  phrases:              {},
  template:             {},
  currentLanguage:      'en',
  currentTemplate:      null,
  currentTemplateGroup: 'user',
  emailPhrases:         null,
  exampleTicket:        null,
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
  [actions.resetTemplate]: async({
    success: setFullPayload('template')
  }),
  [actions.loadVariables]: async({
    success: setFullPayload('variables')
  }),
  [actions.previewTemplate]: async({
    success: setFullPayload('preview')
  }),
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
  [actions.cleanState]:              state => state.clear()
});
