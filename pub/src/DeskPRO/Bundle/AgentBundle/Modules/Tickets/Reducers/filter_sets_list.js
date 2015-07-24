import ActionTypes from "../Actions/ActionTypes";
import * as FiltersActions from "../Actions/FiltersActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetslist extends Reducer {
  getInitialState() {
    return {
    	filter_sets_list: [],
      filter_sets_counts: [],
    };
  }

  registerHandlers() {this
    .r(FiltersActions.loadFilterSets, this.filterSetsLoaded)
    .r(ActionTypes.TICKETS_LOAD_FILTER_COUNTS, this.setPayload('filter_sets_counts', 'data'))
    ;
  }
  
  filterSetsLoaded(state, action) {
    return {
      ...state,
      filter_sets_list: action.payload.data
    };
  }
}

// export default (state, action = {type: null}) => {
//   let reducer = new FilterSetslist();
//   return reducer.compile()(state, action);
// }
