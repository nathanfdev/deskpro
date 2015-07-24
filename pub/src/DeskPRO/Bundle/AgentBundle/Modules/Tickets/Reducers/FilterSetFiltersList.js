import * as FiltersActions from "../Actions/FiltersActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetFiltersList extends Reducer {
  getInitialState() {
    return {
    	FilterSetFiltersList: {
        filter_set_id: null,
        filters: [],
      },
    };
  }

  registerHandlers() {this
    .r(FiltersActions.loadFiltersInSet, this.setPayload('FilterSetFiltersList'))
  }
}
