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

export const setCurrentTemplate = createAction(
  'EMAIL_TEMPLATES_SET_CURRENT_TEMPLATE',
  params => params
);
