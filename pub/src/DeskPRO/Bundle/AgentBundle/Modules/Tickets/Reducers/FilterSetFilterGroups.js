import * as FiltersActions from "../Actions/FiltersActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetFilterGroups extends Reducer {
  getInitialState() {
    return {
      FilterSetFilterGroups: {
        filter_id: 0,
        grouping: null,
        data: []
      }
    };
  }
  
  registerHandlers() {this
    .r(FiltersActions.loadFilterGroups, this.setPayload('FilterSetFilterGroups'))
  }
}
