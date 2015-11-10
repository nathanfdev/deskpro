import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async } from 'Ampliflux/reducers/handlers';
import * as TasksActions from '../Actions/tasksActions';

const initialState = {
  listParams: {
    filter: null,
    sort: null,
    order: null
  },
  elements: {},
  view: 'card',
  async: {
    done: null
  }
};

export default createReducer(initialState, {
  [TasksActions.setListParamsFilter]: setFullPayload('listParams.filter'),
  [TasksActions.setListParamsSort]: setFullPayload('listParams.sort'),
  [TasksActions.setListParamsOrder]: setFullPayload('listParams.order'),
  [TasksActions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
