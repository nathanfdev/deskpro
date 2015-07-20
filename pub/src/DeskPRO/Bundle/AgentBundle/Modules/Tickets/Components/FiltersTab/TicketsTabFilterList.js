import React from "react";

export default class TicketsTabFilterList extends React.Component {
  render() {
    const { filtersList, filterSet, filterCounts, loadFilterTickets } = this.props;
    let filterItems = filtersList.map((filter) => {
      let count = 0;
      for(let k in filterCounts) {
        if(filterCounts[k].filter == filter.id) {
          count = filterCounts[k].count;
          break;
        }
      }
      return (
        <li key={filter.id}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" onClick={() => loadFilterTickets(filter.id)} className="item">{filter.title}</a>
        </li>
      );
    });
    
    return (
      <div>
       {filterItems}
      </div>
    );
  }
}
