import React from "react";

import { connect } from 'redux/react';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/FiltersActions";
import * as LabelActions from "../Actions/LabelsListActions";

import FilterGroupingOptions from "./SidebarHover/FilterGroupingOptions";

@connect(state => ({
  sidebar_hover: state.sidebar_hover,
}))
export default class TicketsSidebarHoverFrame extends React.Component {
  render() {
    const { sidebar_hover, dispatch } = this.props;
    const classes = "sidebar-hover " + (sidebar_hover.open ? 'show' : 'hide');
    
    return (
      <section className={classes}>
        <FilterGroupingOptions dispatch={dispatch} payload={sidebar_hover.payload} />
      </section>
    );
  }
}
