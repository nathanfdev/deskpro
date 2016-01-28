import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadQrCode = createAction(
  'APP_PREFERENES_LOAD_QR',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/me/devise-setup-token').success(response => resolve(response.data.setup_token)))
);
