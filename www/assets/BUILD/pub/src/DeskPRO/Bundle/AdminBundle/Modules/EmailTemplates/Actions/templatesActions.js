import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadTemplates = createAction(
  'EMAIL_TEMPLATES_LOAD_TEMPLATES',
  () => new Promise((resolve) => {
    repository('EmailTemplates').loadInfo().then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadAttachments = createAction(
  'EMAIL_TEMPLATES_LOAD_ATTACHMENTS',
  () => new Promise((resolve) => {
    repository('EmailTemplates').getFiles('attachment').then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadInlineImages = createAction(
  'EMAIL_TEMPLATES_LOAD_INLINE_IMAGES',
  () => new Promise((resolve) => {
    repository('EmailTemplates').getFiles('inline-image').then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadPhrases = createAction(
  'EMAIL_TEMPLATES_LOAD_PHRASES',
  (templateGroup, languageId) => new Promise((resolve) => {
    repository('Languages').loadEmailPhrases(templateGroup, languageId).then((promise) => {
      const res = promise.getData();

      const phrases = {};
      Object.keys(res).forEach((key) => {
        if ({}.hasOwnProperty.call(res, key)) {
          const keySections = key.split('.');
          const group = keySections[1];
          const phraseKey = keySections[2];
          if (!{}.hasOwnProperty.call(phrases, group)) {
            phrases[group] = {
              title:   group,
              phrases: {}
            };
          }
          phrases[group].phrases[phraseKey] = {
            key,
            phrase: res[key]
          };
        }
      });
      resolve(phrases);
    });
  })
);

export const loadTemplate = createAction(
  'EMAIL_TEMPLATES_LOAD_TEMPLATE',
  name => new Promise((resolve) => {
    repository('EmailTemplates').loadTemplate(name).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const saveTemplate = createAction(
  'EMAIL_TEMPLATES_SAVE_TEMPLATE',
  (name, template) => new Promise((resolve) => {
    repository('EmailTemplates').saveTemplate(name, template).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const resetTemplate = createAction(
  'EMAIL_TEMPLATES_RESET_TEMPLATE',
  name => new Promise((resolve) => {
    repository('EmailTemplates').resetTemplate(name).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const previewTemplate = createAction(
  'EMAIL_TEMPLATES_PREVIEW_TEMPLATE',
  (template, code, variables) => new Promise((resolve) => {
    repository('EmailTemplates').previewTemplate(template, code, variables).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const updateTemplateSubject = createAction(
  'EMAIL_TEMPLATES_UPDATE_TEMPLATE_SUBJECT',
  subject => subject
);

export const updateTemplateBody = createAction(
  'EMAIL_TEMPLATES_UPDATE_TEMPLATE_BODY',
  body => body
);

export const loadVariables = createAction(
  'EMAIL_TEMPLATES_LOAD_VARIABLES',
  viewModel => new Promise((resolve) => {
    repository('EmailTemplates').loadVariables(viewModel).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const removeVariables = createAction('EMAIL_TEMPLATES_REMOVE_VARIABLES');

export const setCurrentLanguage = createAction(
  'EMAIL_TEMPLATES_SET_CURRENT_LANGUAGE',
  params => params
);

export const setCurrentTemplate = createAction(
  'EMAIL_TEMPLATES_SET_CURRENT_TEMPLATE',
  params => params
);

export const setCurrentTemplateGroup = createAction(
  'EMAIL_TEMPLATES_SET_CURRENT_TEMPLATE_GROUP',
  params => params
);

export const loadExampleTicket = createAction(
  'EMAIL_TEMPLATES_EXAMPLE_TICKET',
  ticketId => new Promise((resolve) => {
    repository('Tickets').loadTicket(ticketId).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const saveCustomPhrase = createAction(
  'EMAIL_TEMPLATES_SAVE_CUSTOM_PHRASE',
  phrase => repository('Languages').saveCustomPhrase(phrase)
);

export const cleanState = createAction('EMAIL_TEMPLATES_CLEAN_STATE');

export const deleteAsset = createAction(
  'EMAIL_TEMPLATES_DELETE_ASSET',
  themeSetAssetId => repository('EmailTemplates').deleteAsset(themeSetAssetId)
);
