import React from "react";

export default class WaitingTimeGroup extends React.Component {
  shortTimeToEnglish(short_time) {
    switch(short_time) {
    case '30s':
      return "less than 30 seconds"
    case '5min':
      return "less than 5 minutes"
    case '1h':
      return "less than 1 hour"
    case '3h':
      return "less than 3 hours"
    case '24h':
      return "less than 24 hours"
    case '3d':
      return "less than 3 days"
    case '1w':
      return "less than 1 week"
    case '1m':
      return "less than 1 month"
    case '>1m':
      return "more than 1 month"
    default:
      return short_time
    }
  }
  
  render() {
    const {grouping, count, item} = this.props;
    console.log(grouping + " " + count + " " + item);
    
    return (
      <li>
        <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
        <a href="#" className="item">{this.shortTimeToEnglish(item)}</a>
      </li>
    );
  }
}
