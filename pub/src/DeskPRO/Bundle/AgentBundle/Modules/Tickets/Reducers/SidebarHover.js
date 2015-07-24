import * as SidebarHoverActions from "../Actions/SidebarHoverActions";
import { Reducer } from "Ampliflux/reducers";

export default class SidebarHover extends Reducer {
  getInitialState() {
    return {
      open: false,
      show_mode: 'filter_group',
      payload: null,
    };
  }

  registerHandlers() {this
    .r(SidebarHoverActions.hideSidebarHover, this.hideSidebarHover)
    .r(SidebarHoverActions.showFilterGroupingOptions, this.showFilterGroupingOptions)
  }
  
  hideSidebarHover(state, action) {
    return {
      ...state,
      open: false,
    };
  }
  
  showFilterGroupingOptions(state, action) {
    return {
      ...state,
      open: !state.open,
      payload: action.payload,
    }
  }
}
