import React from "react";

import * as StarActions from "../../Actions/StarsListActions";

export default class TicketsTabStars extends React.Component {
  constructor(props) {
    super(props);
    
    this.state = {
      stars_list: [
        "blue",
        "green",
        "orange",
        "pink",
        "purple",
        "red",
        "yellow"
      ]
    };
    
    const { dispatch } = this.props;
    dispatch(StarActions.loadStarCounts());
  }
  
  render() {
    const { starsCounts, dispatch } = this.props;
    
    const stars = this.state.stars_list.map(star => {
      const classes = "fa fa-star star star-" + star;
      // Need to find the count.
      let count = -1;
      if(starsCounts.StarsCounts.length > 0) {
        for(let k in starsCounts.StarsCounts) {
          let my_count = starsCounts.StarsCounts[k];
          if(my_count.star == star) {
            count = my_count.count;
            break;
          }
        }
      }
      
      return (
        <li>
          <div className="list-counter-bucket">
            {count > -1 ? <a className="list-counter" href="#">{count}</a> : ''}
          </div>
          <a href="#" onClick={() => dispatch(StarActions.loadStarTickets(star))} className="sidebar-label-list-item">
            <span className="label-icon"><i className={classes}></i></span><span className="label-name">{star}</span>
          </a>
        </li>
      );
    });
    
    return (
      <div className="sidebar-list sidebar-list-flags" >
        <div className="sidebar-flag-list">

          <ul>
            {stars}
          </ul>

          <span className="section-info">Stars let you create personal collections of tickets</span>

          <a href="#" className="add-new">Create a new star</a>
        </div>
      </div>
    );
  }
}
