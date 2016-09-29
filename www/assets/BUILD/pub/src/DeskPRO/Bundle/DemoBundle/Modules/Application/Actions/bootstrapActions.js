import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
const bootstrapDemo = createAction(
  'DEMO_BOOTSTRAP',
  () => dispatch => new Promise((resolve) => {
    const batchComponents = {
      me: { endpoint: 'me' }
    };
    const batch           = api.prepareParams(batchComponents);

    api.sendGet(batch)
      .success(({ responses }) => {
        const data = flattenBatchResponses(responses);
        dispatch(setCollection('Person', 'me', [data.me.person]));
        dispatch(donePreloading());
      })
    ;

    return resolve();
  })
);
export default bootstrapDemo;
