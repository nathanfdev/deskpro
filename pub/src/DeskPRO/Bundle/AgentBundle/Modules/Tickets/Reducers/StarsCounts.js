import * as StarsListActions from "../Actions/StarsListActions";
import { Reducer } from "Ampliflux/reducers";

export default class FilterSetsList extends Reducer {
  getInitialState() {
    return {
    	StarsCounts: [],
    };
  }

  registerHandlers() {this
    .r(StarsListActions.loadStarCounts, this.setPayload('StarsCounts', 'data'))
  }
}
