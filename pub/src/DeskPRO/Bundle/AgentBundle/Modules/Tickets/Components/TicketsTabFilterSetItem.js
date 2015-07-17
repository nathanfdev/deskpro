import React from "react";
import { connect } from 'redux/react';
import * as TicketActions from "../Actions/TicketsListActions";

import TicketsTabFilterList from "./TicketsTabFilterList";

@connect((state) => ({
  filter_set_filters_list: state.filter_set_filters_list,
}))
export default class TicketsTabFilterSetItem extends React.Component {
  constructor(props) {
    super(props);
    
    const { dispatch, filterSet } = this.props;
    
    this.filters = [];
    this.filterSetId = filterSet.id;
    dispatch(TicketActions.loadFiltersInSet(filterSet.id));
  }
  
  render() {
    const { filter_set_filters_list, filterSet, loadFilterTickets, totalTickets, filterCounts } = this.props;
    
    if(filter_set_filters_list.filter_set_filters_list.filter_set_id == this.filterSetId) {
      this.filters = filter_set_filters_list.filter_set_filters_list.filters;
    }
    
    return (
      <li key={filterSet.id} className="counter-display">
        <div className="list-counter-bucket">
          {totalTickets > 0 ? <a className="list-counter-dropdown active" href="#">&nbsp;<i className="fa fa-angle-down"></i></a> : ''}
          <a href="#" className="list-counter active">{totalTickets}</a>
        </div>
        <a href="#" className="item">{filterSet.title}</a>

        <ul className="with-connectors">
          <TicketsTabFilterList filtersList={this.filters} filterSet={filterSet} filterCounts={filterCounts} loadFilterTickets={loadFilterTickets} />
        </ul>
      </li>
    );
  }
}
