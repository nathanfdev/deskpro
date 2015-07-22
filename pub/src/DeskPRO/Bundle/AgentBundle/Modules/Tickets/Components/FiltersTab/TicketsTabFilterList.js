import React from "react";

import { connect } from "redux/react";
import * as SidebarHoverActions from "../../Actions/SidebarHoverActions";
import * as FiltersActions from "../../Actions/FiltersActions";

import GenericGroup from "./Groups/GenericGroup";
import DepartmentGroup from "./Groups/DepartmentGroup";

@connect(state => ({
  filter_set_filter_groups: state.filter_set_filter_groups,
}))
export default class TicketsTabFilterList extends React.Component {  
  render() {
    const {
      filtersList,
      filterSet,
      filterCounts,
      loadFilterTickets,
      showFilterGroupingOptions,
      filter_set_filter_groups,
      dispatch,
    } = this.props;
    let totalTickets = 0;
    let expandButton = '';

    let filterItems = filtersList.map((filter) => {
      let count = 0;
      for(let k in filterCounts) {
        if(filterCounts[k].filter == filter.id) {
          count = filterCounts[k].count;
          break;
        }
      }
      
      if(filtersList.length > -1) {
        const classes = 'fa fa-angle-down';
        expandButton = (
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" onClick={() => dispatch(SidebarHoverActions.showFilterGroupingOptions(filter))} href="#">
              &nbsp;<i className={classes}></i>
            </a>
            <a href="#" className="list-counter active">{count}</a>
          </div>
        );
      }
      
      const filter_groups = filter_set_filter_groups.filter_set_filter_groups;
      
      let groups = [];
      if(filter_groups.filter_id == filter.id && filter_groups.data.length > 0) {
        groups = filter_groups.data.map(group => {
          console.log(filter_groups);
          // switch(filter_groups.grouping) {
          // case 'department':
            return (
              <DepartmentGroup
                count={group.count} item={group[filter_groups.grouping]} />
            );
          // default:
          //   return (
          //     <GenericGroup grouping={filter_groups.grouping}
          //       count={group.count} item={group[filter_groups.grouping]} />
          //   );
          // }
        });
        
        groups = (
          <ul className="with-connectors">
            {groups}
          </ul>
        );
      }
      
      return (
        <li className="sidebar-item" key={filter.id}>
          {expandButton}
          <a href="#" onClick={() => loadFilterTickets(filter.id)} className="item">{filter.title}</a>
            {groups}
        </li>
      );
    });
    
    return (
      <ul>
        {filterItems}
      </ul>
    );
  }
}
