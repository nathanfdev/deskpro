import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import * as actions from '../Actions/actions';

const initialState = {
  navItem: null
};

export default createReducer(initialState, {
  [actions.setWidgetFilter]: (state, payload) => {
    const params = payload.params;
    const type = params.widgetType ? 'widgetType' : 'filter';
    return state.set('navItem', Immutable.fromJS({ type: type, params: params }));
  }
});
