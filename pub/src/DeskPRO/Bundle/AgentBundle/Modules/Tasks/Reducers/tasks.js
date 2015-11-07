import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async } from 'Ampliflux/reducers/handlers';
import * as TasksActions from '../Actions/tasksActions';

const initialState = {
  listParams: null,
  elements: [],
  async: {
    done: null
  }
};

export default createReducer(initialState, {
  [TasksActions.setListParams]: setFullPayload('listParams'),
  [TasksActions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
