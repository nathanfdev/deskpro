import { createAction } from 'DeskPRO/Component/Ampliflux';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const getGeoIp = createAction(
  'EXTEND_GET_GEO_IP',
  () =>
    api.sendGet('DP_API/cloud/geo_ip', { dataType: 'json' })
);
export const loadGeoIp = createAction(
  'DEMO_LOAD_GEO_IP',
  () => dispatch => new Promise((resolve) => {
    // background request data
    dispatch(getGeoIp())
      .success((geoIp) => {
        dispatch(setCollection('GeoIp', 'all', geoIp));
        resolve();
      });
  })
);

export const donePreloading = createAction('APP_BOOTSTRAP_DONE_PRELOADING');
const bootstrapDemo = createAction(
  'DEMO_BOOTSTRAP',
  () => dispatch => new Promise((resolve) => {
    const batchComponents = {
      me: { endpoint: 'me' }
    };
    const batch = api.prepareParams(batchComponents);

    const batchPromises = api.sendGet(batch)
      .success(({ responses }) => {
        const data = flattenBatchResponses(responses);
        dispatch(setCollection('Person', 'me', [data.me.person]));
        dispatch(donePreloading());
      })
    ;

    const promises = [
      dispatch(loadGeoIp()),
      batchPromises
    ];

    Promise.all(promises).then(() => resolve(), () => resolve());
  })
);
export default bootstrapDemo;
