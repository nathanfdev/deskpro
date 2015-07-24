import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	labels_list: [],
};

const r = handleActions({
  [ActionTypes.TICKETS_LOAD_TICKET_LABELS]: (state, action) => {
    const flat_data = action.payload.data;
    let grouped_data = {};
    
    for(let k in flat_data) {
      const label = flat_data[k];
      let letter = label[0];
      
      if(typeof grouped_data[letter] === 'undefined') {
        grouped_data[letter] = {
          letter: letter,
          labels: [label]
        };
      } else {
        grouped_data[letter].labels.push(label);
      }
    }
    
    const final_data = Object.keys(grouped_data).map(key => (grouped_data[key]));
    
    return {
      ...state,
      labels_list: final_data
    };
  },
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
