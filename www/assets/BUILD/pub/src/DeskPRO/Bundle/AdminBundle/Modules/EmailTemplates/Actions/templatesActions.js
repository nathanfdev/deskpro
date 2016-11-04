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

export const setCurrentTemplate = createAction(
  'EMAIL_TEMPLATES_SET_CURRENT_TEMPLATE',
  params => params
);
