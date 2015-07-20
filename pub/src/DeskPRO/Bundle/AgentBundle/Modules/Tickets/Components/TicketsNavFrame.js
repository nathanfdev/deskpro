import React from "react";

import TicketsTabFilters from "./FiltersTab/TicketsTabFilters";
import TicketsTabLabels from "./LabelsTab/TicketsTabLabels";
import TicketsTabFlags from "./StarsTab/TicketsTabStars";

import { connect } from 'redux/react';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/TicketsListActions";
import * as LabelActions from "../Actions/LabelsListActions";

@connect(state => ({
  filter_sets_list: state.filter_sets_list,
  filter_sets_counts: state.filter_sets_counts,
  labels_list: state.labels_list,
}))
export default class TicketsNavContent extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showTab: "labels"
    };
    
    const { dispatch } = this.props;
    dispatch(TicketActions.loadFilterSets());
    dispatch(TicketActions.loadFilterSetsCounts());
    dispatch(LabelActions.loadLabels());
  }
  
  changeTab(newTab) {
    let newState = {
      ...this.state,
      showTab: newTab
    }
    this.setState(newState);
  }
  
  renderTab(key, title) {
    let link_class = 'show-' + key;
    let tab_class = '';
    if(this.state.showTab == key) {
      tab_class = 'active';
    }
    let clickythingy = () => this.changeTab(key);
    return (
      <li className={tab_class}>
        <a href="#" className={link_class} onClick={clickythingy}>{title}</a>
      </li>
    );
  }
  
  render() {
    const { filter_sets_list, filter_sets_counts, labels_list, dispatch } = this.props;
    
    let tab = null;
    switch(this.state.showTab) {
    case "labels":
      tab = <TicketsTabLabels labelsList={labels_list} />
      break;
    case "stars":
      tab = <TicketsTabStars />
      break;
    case "filters":
    default:
      tab = <TicketsTabFilters
              filterSetsList={filter_sets_list}
              filterSetsCounts={filter_sets_counts}
              {...bindActionCreators(TicketActions, dispatch)}
              {...this.props} />
      break;
    }
    
    return (
      <section className="ticket-nav-frame">
        <aside className="sidebar has-tabs">
          <div className="sidebar-title">
            <span className="sidebar-type-icon">
              <i className="fa fa-envelope-o"></i>
              <span className="help"><i className="fa fa-question"></i></span>
            </span>
            <h1>Tickets</h1>
            <hr />
            <a href="#" className="slider-control"></a>
          </div>

          <ul className="tabs sidebar-tabs">
            {this.renderTab('filters', 'Filters')}
            {this.renderTab('labels', 'Labels')}
            {this.renderTab('stars', 'Stars')}
          </ul>
    
          {tab}
    
        </aside>
      </section>
    );
  }
}
