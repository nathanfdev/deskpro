import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const cleanExtraTemplates = createAction('EMAIL_TEMPLATE_CLEAN_EXTRA_TEMPLATE');

export const cleanState = createAction('EMAIL_TEMPLATES_CLEAN_STATE');

export const deleteAsset = createAction(
  'EMAIL_TEMPLATES_DELETE_ASSET',
  themeSetAssetId => repository('EmailTemplates').deleteAsset(themeSetAssetId)
);

export const deletePreview = createAction('EMAIL_TEMPLATES_DELETE_PREVIEW');

export const loadAttachments = createAction(
  'EMAIL_TEMPLATES_LOAD_ATTACHMENTS',
  () => new Promise((resolve) => {
    repository('EmailTemplates').getFiles('attachment').then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadEmailAccounts = createAction(
  'EMAIL_TEMPLATES_LOAD_EMAIL_ACCOUNTS',
  () => new Promise(
    (resolve, reject) => repository('EmailAccounts').loadEmailAccounts()
      .success((result) => {
        resolve(result.data);
      })
      .error(response => reject(response))
  )
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

      const phrases = { all: { title: 'all', phrases: {} } };
      Object.keys(res).forEach((key) => {
        phrases.all.phrases[key] = res[key];
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

      res.original_code = {
        body:    res.template_code.body,
        code:    res.template_code.code,
        subject: res.template_code.subject,
      };

      resolve(res);
    });
  })
);

export const loadTemplates = createAction(
  'EMAIL_TEMPLATES_LOAD_TEMPLATES',
  () => new Promise((resolve) => {
    repository('EmailTemplates').loadInfo().then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadTranslations = createAction(
  'EMAIL_TEMPLATE_LOAD_TRANSLATIONS',
  phraseName => new Promise((resolve) => {
    repository('Languages').loadTranslations(phraseName).then((promise) => {
      resolve(promise.getData());
    });
  })
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

export const previewTemplate = createAction(
  'EMAIL_TEMPLATES_PREVIEW_TEMPLATE',
  (template, group, code, variables, lang, extraTemplates) => new Promise((resolve) => {
    if (code === '') {
      return resolve('');
    }
    return repository('EmailTemplates').previewTemplate(template, group, code, variables, lang, extraTemplates).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const removeVariables = createAction('EMAIL_TEMPLATES_REMOVE_VARIABLES');

export const resetTemplate = createAction(
  'EMAIL_TEMPLATES_RESET_TEMPLATE',
  name => new Promise((resolve) => {
    repository('EmailTemplates').resetTemplate(name).then((promise) => {
      const res = promise.getData();

      res.original_code = {
        body:    res.template_code.body,
        code:    res.template_code.code,
        subject: res.template_code.subject,
      };

      resolve(res);
    });
  })
);

export const saveCustomPhrase = createAction(
  'EMAIL_TEMPLATES_SAVE_CUSTOM_PHRASE',
  phrase => repository('Languages').saveCustomPhrase(phrase)
);

export const saveTemplate = createAction(
  'EMAIL_TEMPLATES_SAVE_TEMPLATE',
  (name, template) => new Promise((resolve) => {
    repository('EmailTemplates').saveTemplate(name, template).then((promise) => {
      resolve(promise.getData());
    });
  })
);

export const saveTranslations = createAction(
  'EMAIL_TEMPLATE_SAVE_TRANSLATIONS',
  (phraseName, translations) => new Promise((resolve) => {
    repository('Languages').saveTranslations(phraseName, translations).then((promise) => {
      resolve(promise.getData());
    });
  })
);

export const sendPreview = createAction(
  'EMAIL_TEMPLATES_SEND_PREVIEW',
  (viewModel, group, subject, body, variables, lang, from, to, extraTemplates) => new Promise((resolve, reject) =>
    repository('EmailTemplates').sendPreviewEmail(viewModel, group, subject, body, variables, lang, from, to, extraTemplates)
      .success((result) => {
        resolve(result);
      })
      .error(response => reject(response))
  )
);

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

export const setExtraTemplate = createAction('EMAIL_TEMPLATE_SET_EXTRA_TEMPLATE');

export const setTemplate = createAction('EMAIL_TEMPLATES_SET_TEMPLATE');

export const unselectTemplate = createAction('EMAIL_TEMPLATES_UNSELECT_TEMPLATE');

export const updateTemplateBody = createAction(
  'EMAIL_TEMPLATES_UPDATE_TEMPLATE_BODY',
  body => body
);

export const updateTemplateSubject = createAction(
  'EMAIL_TEMPLATES_UPDATE_TEMPLATE_SUBJECT',
  subject => subject
);
