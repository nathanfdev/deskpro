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
    
    this.state = {
      open: false
    };
    
    this.filters = [];
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
    const { filter_set_filters_list, filterSet, loadFilterTickets, totalTickets, filterCounts } = this.props;
    
    if(filter_set_filters_list.filter_set_filters_list.filter_set_id == this.filterSetId) {
      this.filters = filter_set_filters_list.filter_set_filters_list.filters;
    }
    
    let expandButton = '';
    if(this.filters.length > 0 && totalTickets > 0) {
      const classes = this.state.open ? 'fa fa-angle-down' : 'fa fa-angle-right';
      expandButton = (
        <div className="list-counter-bucket">
          <a href="#" className="list-counter active" onClick={() => this.expandClick()}>{totalTickets}</a>
          <a className="list-counter-dropdown active" href="#" onClick={() => this.expandClick()}>
            &nbsp;<i className={classes}></i>
          </a>
        </div>
      );
    }
    
    const filtersClassName = 'with-connectors collapse ' + (this.state.open ? 'open' : 'closed');
    
    return (
      <li key={filterSet.id} className="counter-display with-collapsible-sublist">
        {expandButton}
        <a href="#" className="item">{filterSet.title}</a>

        <ul className={filtersClassName}>
          <TicketsTabFilterList filtersList={this.filters} filterSet={filterSet} filterCounts={filterCounts} loadFilterTickets={loadFilterTickets} />
        </ul>
      </li>
    );
  }
}
