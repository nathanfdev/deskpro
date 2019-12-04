import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/iconPickerActions';

export const icons = {
  icons: []
};

export default createReducer(icons, {
  [actions.loadIcons]: async({
    success: setFullPayload('icons')
  })
});
