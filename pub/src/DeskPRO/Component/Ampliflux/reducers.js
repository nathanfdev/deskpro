import { handleActions } from "redux-actions";
import { composeStores } from 'redux';

export class Reducer {
  constructor() {
    this.actionsMap = {};
    this.registerHandlers();
  }
  
  // Compiles the reducer class into a bunch of handlers.
  compile() {
    return handleActions(this.actionsMap, this.getInitialState());
  }
  
  // Defines the initial value of the reducer's state.
  getInitialState() {
    return {};
  }
  
  registerHandlers() {
    return {};
  }
  
  r(action, handler) {
    let action_type = action;
    if(typeof action == 'function' || typeof action == 'object') {
      action_type = action.actionType;
    }
    
    this.actionsMap[action_type] = handler;
    
    return this;
  }
  
  setPayload(property, payload_prop = null) {
    return (state, action) => {
      let data = action.payload;
      if(payload_prop) {
        data = action.payload[payload_prop];
      }
      return {
        ...state,
        [property]: data,
      }
    };
  }
  
  static isAmplifluxReducer() {
    return true;
  }
}

export function composeReducers(reducers) {
  let processed_reducers = {};
  for(let k in reducers) {
    try {
      if(reducers[k].isAmplifluxReducer && reducers[k].isAmplifluxReducer()) {
        let reducer = new reducers[k]();
        processed_reducers[k] = reducer.compile();
      } else {
        processed_reducers[k] = reducers[k];
      }
    }
    catch(err) {
      processed_reducers[k] = reducers[k];
    }
  }

  return composeStores(processed_reducers);
}
