import React from "react";

export default class TicketsTabFilterList extends React.Component {
  render() {
    const { filtersList, filterSet, filterCounts, loadFilterTickets } = this.props;
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
            <a className="list-counter-dropdown active" href="#">
              &nbsp;<i className={classes}></i>
            </a>
            <a href="#" className="list-counter active">{count}</a>
          </div>
        );
      }
      
      return (
        <li className="sidebar-item" key={filter.id}>
          {expandButton}
          <a href="#" onClick={() => loadFilterTickets(filter.id)} className="item">{filter.title}</a>

          {/* Grouping goes here */}
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
