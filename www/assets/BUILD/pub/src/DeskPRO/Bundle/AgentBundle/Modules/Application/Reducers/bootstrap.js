import { showWelcomePage, doneInitialLoad, preloadData } from '../Actions/bootstrapActions';
import { createReducer } from 'Ampliflux';
import { async, setValue } from 'Ampliflux/reducers/handlers';

const initialState = {
  showWelcomePage: true,
  isDoneInitialLoad: false,
  isPreloading: false
};

export default createReducer(initialState, {
  [showWelcomePage]: setValue('showWelcomePage', true),
  [doneInitialLoad]: state => state.merge({showWelcomePage: false, isDoneInitialLoad: true}),
  [preloadData]: async({
    start: state => state.set('isPreloading', true),
    done: state => state.set('isPreloading', false)
  })
});
