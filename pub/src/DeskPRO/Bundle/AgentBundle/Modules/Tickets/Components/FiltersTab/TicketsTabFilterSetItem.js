import React from "react";
import { connect } from 'redux/react';
import * as TicketActions from "../../Actions/TicketsListActions";

import TicketsTabFilterList from "./TicketsTabFilterList";

@connect((state) => ({
  filter_set_filters_list: state.filter_set_filters_list,
}))
export default class TicketsTabFilterSetItem extends React.Component {
  constructor(props) {
    super(props);
    
    const { dispatch, filterSet } = this.props;
    
    this.state = {
      open: false
    };
    
    this.filters = [];
    this.filterCounts = null;
    this.filterSetId = filterSet.id;
    dispatch(TicketActions.loadFiltersInSet(filterSet.id));
  }
  
  expandClick() {
    if(this.state.open) {
      this.setState({
        ...this.state,
        open: false
      });
    } else {
      this.setState({
        ...this.state,
        open: true
      });
    }
  }
  
  render() {
    const {
      filter_set_filters_list,
      filterSet,
      loadFilterTickets,
      totalTickets,
      filterCounts,
      showFilterGroupingOptions,
      dispatch,
    } = this.props;
    
    if(filter_set_filters_list.filter_set_filters_list.filter_set_id == this.filterSetId) {
      this.filters = filter_set_filters_list.filter_set_filters_list.filters;
      this.filterCounts = filterCounts;
    }
    
    const filtersClassName = 'with-connectors collapse ' + (this.state.open ? 'open' : 'closed');
    let filter_list = null;
    if(this.filterCounts === null) {
      filter_list = (
        <span>Loading&hellip;</span>
      );
    } else {
      filter_list = (
        <TicketsTabFilterList
          filtersList={this.filters}
          filterSet={filterSet}
          filterCounts={this.filterCounts}
          loadFilterTickets={loadFilterTickets}
          showFilterGroupingOptions={showFilterGroupingOptions}
          dispatch={dispatch} />
      );
    }
    
    return (
      <div className="list-filter-set">
        <h3 className="list-sidebar-title">{filterSet.title}</h3>
        {filter_list}
      </div>
    );
  }
}
