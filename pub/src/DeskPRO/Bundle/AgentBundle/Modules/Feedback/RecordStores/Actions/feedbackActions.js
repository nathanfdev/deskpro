import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedback = createAction(
  'LOAD_FEEDBACK',
  createRecordsRequest(
    ['RecordStores', 'feedback', 'feedback'], 'all',
    (params) => new Promise(
      (resolve, reject) => {
        let paramsEncoded = [];
        paramsEncoded.push('sort=' + params.sort);
        paramsEncoded.push('order=' + params.order);
        if (params.group) {
          paramsEncoded.push(params.group.name + '=' + String(params.group.value).replace(/\s/g, '%20'));
        }
        if (params.filters.value && params.filters.value.length > 0) {
          paramsEncoded.push(params.filters.alias + '=' + params.filters.value.replace(/\s/g, '%20'));
        }
        // console.log('DP_API/feedback/?' + paramsEncoded.join('&'));
        return DpApi.sendGet('DP_API/feedback/?' + paramsEncoded.join('&'))
          .success(response => resolve(response.data))
          .error(response => reject(response));
      }
    )
  )
);
