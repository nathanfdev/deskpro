import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const cleanExtraTemplates = createAction('PORTAL_TEMPLATES_CLEAN_EXTRA_TEMPLATE');

export const cleanState = createAction('PORTAL_TEMPLATES_CLEAN_STATE');

export const deleteAsset = createAction(
  'PORTAL_TEMPLATES_DELETE_ASSET',
  themeSetAssetId => repository('PortalTemplates').deleteAsset(themeSetAssetId)
);

export const deletePreview = createAction('PORTAL_TEMPLATES_DELETE_PREVIEW');

export const loadAttachments = createAction(
  'PORTAL_TEMPLATES_LOAD_ATTACHMENTS',
  () => new Promise((resolve) => {
    repository('PortalTemplates').getFiles('attachment').then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadExampleTicket = createAction(
  'PORTAL_TEMPLATES_EXAMPLE_TICKET',
  ticketId => new Promise((resolve) => {
    repository('Tickets').loadTicket(ticketId).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadInlineImages = createAction(
  'PORTAL_TEMPLATES_LOAD_INLINE_IMAGES',
  () => new Promise((resolve) => {
    repository('PortalTemplates').getFiles('inline-image').then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadPhrases = createAction(
  'PORTAL_TEMPLATES_LOAD_PHRASES',
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
  'PORTAL_TEMPLATES_LOAD_TEMPLATE',
  name => new Promise((resolve) => {
    repository('PortalTemplates').loadTemplate(name).then((promise) => {
      const res = promise.getData();

      resolve(res.source);
    });
  })
);

const templateName = template => template.name.split(':')[2].replace(/\.twig/, '');
const templateGroup = (template) => {
  const parts = template.name.split(':');
  if (parts[1]) { return parts[1]; }  return parts[0];
};

export const loadTemplates = createAction(
  'PORTAL_TEMPLATES_LOAD_TEMPLATES',
  () => new Promise((resolve) => {
    repository('PortalTemplates').loadInfo().then((promise) => {
      const templates = promise.getData();
      const res = {};

      for (const template of Array.from(templates)) {
        if (!res[templateGroup(template)]) {
          res[templateGroup(template)] = {
            title:     templateGroup(template),
            templates: []
          };
        }
        res[templateGroup(template)].templates.push({
          value:  template.name,
          custom: template.is_custom,
          name:   templateName(template),
          group:  templateGroup(template)
        });
      }

      resolve(res);
    });
  })
);

export const loadTranslations = createAction(
  'PORTAL_TEMPLATES_LOAD_TRANSLATIONS',
  phraseName => new Promise((resolve) => {
    repository('Languages').loadTranslations(phraseName).then((promise) => {
      resolve(promise.getData());
    });
  })
);

export const loadVariables = createAction(
  'PORTAL_TEMPLATES_LOAD_VARIABLES',
  viewModel => new Promise((resolve) => {
    repository('PortalTemplates').loadVariables(viewModel).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const removeVariables = createAction('PORTAL_TEMPLATES_REMOVE_VARIABLES');

export const resetTemplate = createAction(
  'PORTAL_TEMPLATES_RESET_TEMPLATE',
  name => new Promise((resolve) => {
    repository('PortalTemplates').resetTemplate(name).then((promise) => {
      const res = promise.getData();

      res.original_code = {
        code: res.template_code.code,
      };

      resolve(res);
    });
  })
);

export const deleteTemplate = createAction(
  'PORTAL_TEMPLATES_DELETE_TEMPLATE',
  name => repository('PortalTemplates').deleteTemplate(name)
);

export const saveCustomPhrase = createAction(
  'PORTAL_TEMPLATES_SAVE_CUSTOM_PHRASE',
  phrase => repository('Languages').saveCustomPhrase(phrase)
);

export const saveTemplate = createAction(
  'PORTAL_TEMPLATES_SAVE_TEMPLATE',
  (name, template) => new Promise((resolve, reject) => {
    repository('PortalTemplates').saveTemplate(name, template).then((promise) => {
      resolve(promise.getData());
    }, (err) => {
      reject(err.data);
    });
  })
);

export const saveTranslations = createAction(
  'PORTAL_TEMPLATES_SAVE_TRANSLATIONS',
  (phraseName, translations) => new Promise((resolve) => {
    repository('Languages').saveTranslations(phraseName, translations).then((promise) => {
      resolve(promise.getData());
    });
  })
);

export const setCurrentLanguage = createAction(
  'PORTAL_TEMPLATES_SET_CURRENT_LANGUAGE',
  params => params
);

export const setCurrentTemplate = createAction(
  'PORTAL_TEMPLATES_SET_CURRENT_TEMPLATE',
  params => params
);

export const setCurrentTemplateGroup = createAction(
  'PORTAL_TEMPLATES_SET_CURRENT_TEMPLATE_GROUP',
  params => params
);

export const setExtraTemplate = createAction('PORTAL_TEMPLATES_SET_EXTRA_TEMPLATE');

export const setPreview = createAction('PORTAL_TEMPLATES_SET_PREVIEW');

export const setTemplate = createAction('PORTAL_TEMPLATES_SET_TEMPLATE');

export const unselectTemplate = createAction('PORTAL_TEMPLATES_UNSELECT_TEMPLATE');

export const updateTemplateCode = createAction(
  'PORTAL_TEMPLATES_UPDATE_TEMPLATE_CODE',
  code => code
);
