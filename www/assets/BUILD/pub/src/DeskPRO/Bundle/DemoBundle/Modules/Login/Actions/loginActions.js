import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import bootstrapDemo from 'DeskPRO/Bundle/DemoBundle/Modules/Application/Actions/bootstrapActions';

const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => dispatch => new Promise((resolve, reject) => {
    api.sendPost('DP_API/get_session', params).then(() => {
      dispatch(bootstrapDemo()).then(() => {
        resolve();
      });
    }).catch(() => {
      reject();
    });
  })
);
export default login;
