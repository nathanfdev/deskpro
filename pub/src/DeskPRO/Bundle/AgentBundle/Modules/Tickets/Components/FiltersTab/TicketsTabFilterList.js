import React from "react";

import { connect } from "react-redux";
import * as SidebarHoverActions from "../../Actions/SidebarHoverActions";
import * as FiltersActions from "../../Actions/FiltersActions";

import GenericGroup from "./Groups/GenericGroup";
import DepartmentGroup from "./Groups/DepartmentGroup";
import PeopleGroup from "./Groups/PeopleGroup";
import UrgencyGroup from "./Groups/UrgencyGroup";
import AgentTeamGroup from "./Groups/AgentTeamGroup";
import WaitingTimeGroup from "./Groups/WaitingTimeGroup";

@connect(state => ({
  FilterSetFilterGroups: state.Tickets.FilterSetFilterGroups,
}))
export default class TicketsTabFilterList extends React.Component {
  render() {
    const {
      filtersList,
      filterSet,
      filterCounts,
      loadFilterTickets,
      showFilterGroupingOptions,
      FilterSetFilterGroups,
      dispatch,
    } = this.props;
    let totalTickets = 0;
    let expandButton = '';

    let filterItems = filtersList.map((filter) => {
      let count = 0;
      for(let k in filterCounts) {
        if(filterCounts[k].group == filter.id) {
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

      const FilterGroups = FilterSetFilterGroups.FilterSetFilterGroups;
      let groups = [];
      if(FilterGroups.filter_id == filter.id && FilterGroups.data.length > 0) {
        groups = FilterGroups.data.map(group => {
          switch(FilterGroups.grouping) {
          case 'department':
            return (
              <DepartmentGroup
                count={group.count} item={group[FilterGroups.grouping]} />
            );
          case 'urgency':
            return (
              <UrgencyGroup
                count={group.count} urgency={group[FilterGroups.grouping]} />
            );
          case 'agent':
          case 'person':
            return (
              <PeopleGroup
                count={group.count} item={group[FilterGroups.grouping]} />
            );
          case 'agent_team':
            let grouping = 'agent_team_id';
            return (
              <AgentTeamGroup
                count={group.count} item={group[grouping]} />
            );
          case 'all_waiting_time':
          case 'waiting_time':
          case 'open_time':
            return (
              <WaitingTimeGroup
                count= {group.count} item={group[FilterGroups.grouping]} />
            );
          default:
            return (
              <GenericGroup grouping={FilterGroups.grouping}
                count={group.count} item={group[FilterGroups.grouping]} />
            );
          }
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
