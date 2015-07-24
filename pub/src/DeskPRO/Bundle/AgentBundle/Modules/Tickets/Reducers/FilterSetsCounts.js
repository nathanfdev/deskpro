import * as FiltersActions from "../Actions/FiltersActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetsCount extends Reducer {
  getInitialState() {
    return {
    	FilterSetsCounts: [],
    };
  }

  registerHandlers() {this
    .r(FiltersActions.loadFilterSetsCounts, this.setPayload('FilterSetsCounts', 'data'))
    ;
  }
}
