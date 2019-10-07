import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

export const loadBillingSummary = createAction(
  'VOICE_LOAD_BILLING_USAGE',
  (accountId, date) => new Promise((resolve) => {
    const url = `DP_API/voice_stats/${accountId}/billing_summary`;
    const params = {};
    if (date) {
      params.date = date;
    }

    return api.sendGet(`${url}?${compileParams(params)}`).success(response => resolve(Immutable.fromJS(response.data)));
  })
);
