import React from "react";
import * as TicketActions from "../../Actions/TicketsListActions";

import TicketsTabFilterSetItem from "./TicketsTabFilterSetItem";

export default class TicketsTabFilterSetList extends React.Component {
  render() {
    const { filterSetsList, filterSetsCounts, loadFilterTickets, dispatch } = this.props;

    const filtersets = filterSetsList.filter_sets_list.map((filter_set) => {
      let total = 0;
      let my_filter_counts = null;
      if(typeof filterSetsCounts.filter_sets_counts !== 'undefined') {
        for(let k in filterSetsCounts.filter_sets_counts) {
          if(filterSetsCounts.filter_sets_counts[k].filter_set == filter_set.id) {
            total = filterSetsCounts.filter_sets_counts[k].count;
            my_filter_counts = filterSetsCounts.filter_sets_counts[k].filters;
            break;
          }
        }
      }

      return (
        <TicketsTabFilterSetItem filterSet={filter_set} totalTickets={total} filterCounts={my_filter_counts} {...this.props} />
      );
    });
    return (
      <div className="filter-set">
        {filtersets}
      </div>
    );
  }
}
