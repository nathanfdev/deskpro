import React from "react";

import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/FiltersActions";
import * as LabelActions from "../Actions/LabelsListActions";

import FilterGroupingOptions from "./SidebarHover/FilterGroupingOptions";

@connect(state => ({
  SidebarHover: state.SidebarHover,
}))
export default class TicketsSidebarHoverFrame extends React.Component {
  render() {
    const { SidebarHover, dispatch } = this.props;
    const classes = "sidebar-hover " + (SidebarHover.open ? 'show' : 'hide');
    
    return (
      <section className={classes}>
        <FilterGroupingOptions dispatch={dispatch} payload={SidebarHover.payload} />
      </section>
    );
  }
}
