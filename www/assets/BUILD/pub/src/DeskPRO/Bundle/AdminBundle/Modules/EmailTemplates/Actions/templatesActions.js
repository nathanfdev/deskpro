import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadTemplates = createAction(
  'EMAIL_TEMPLATES_LOAD_TEMPLATES',
  () => dispatch => new Promise((resolve) => {
    repository('EmailTemplates').loadInfo().then((promise) => {
      const res = promise.getData();

      dispatch(setCollection('EmailTemplates', 'info', res.data));
      resolve(res.data);
    });
  })
);

