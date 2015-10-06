import createAction from './actions/createAction';
import createReducer from './reducers/createReducer';
import combineReducerHierarchy from './reducers/combineReducerHierarchy';
import { pureRender } from './common/components/pureRenderDecorator';

export default {
  createAction,
  createReducer,
  combineReducerHierarchy,
  pureRender
};
