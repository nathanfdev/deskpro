import React from "react";

import { connect } from 'redux/react';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/TicketsListActions";
import * as LabelActions from "../Actions/LabelsListActions";

import FilterGroupingOptions from "./SidebarHover/FilterGroupingOptions";

@connect(state => ({
  sidebar_hover: state.sidebar_hover,
}))
export default class TicketsSidebarHoverFrame extends React.Component {
  constructor(props) {
    super(props);
  }
  
  render() {
    const { sidebar_hover } = this.props;
    const classes = "sidebar-hover " + (sidebar_hover.sidebar_hover.open ? 'show' : 'hide');
    
    return (
      <section className={classes}>
        <FilterGroupingOptions />
      </section>
    );
  }
}
