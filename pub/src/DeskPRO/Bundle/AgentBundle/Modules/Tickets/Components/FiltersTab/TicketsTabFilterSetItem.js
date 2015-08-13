import React from "react";
import { connect } from 'react-redux';
import { createSelector } from 'reselect';
import * as TicketActions from "../../Actions/FiltersActions";

import TicketsTabFilterList from "./TicketsTabFilterList";

@connect((state) => ({
  FilterSetFiltersList: state.Tickets.FilterSetFiltersList,
}))
export default class TicketsTabFilterSetItem extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, filterSet } = this.props;

    this.state = {
      open: false
    };

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

  shouldComponentUpdate(nextProps, nextState) {
    const { FilterSetFiltersList } = nextProps;
    return (FilterSetFiltersList.FilterSetFiltersList.filter_set_id == this.filterSetId);
  }

  render() {
    const {
      FilterSetFiltersList,
      filterSet,
      loadFilterTickets,
      totalTickets,
      filterCounts,
      showFilterGroupingOptions,
      dispatch,
    } = this.props;
    const filters = FilterSetFiltersList.FilterSetFiltersList.filters;

    const filtersClassName = 'with-connectors collapse ' + (this.state.open ? 'open' : 'closed');
    let filter_list = null;
    if(filterCounts === null) {
      filter_list = (
        <span>Loading&hellip;</span>
      );
    } else {
      filter_list = (
        <TicketsTabFilterList
          filtersList={filters}
          filterSet={filterSet}
          filterCounts={filterCounts}
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
