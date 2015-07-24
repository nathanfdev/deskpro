import * as FiltersActions from "../Actions/FiltersActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetsList extends Reducer {
  getInitialState() {
    return {
    	FilterSetsList: [],
      FilterSetsCounts: [],
    };
  }

  registerHandlers() {this
    .r(FiltersActions.loadFilterSets, this.filterSetsLoaded)
  }
  
  filterSetsLoaded(state, action) {
    return {
      ...state,
      FilterSetsList: action.payload.data
    };
  }
}
