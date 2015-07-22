import React from "react";

export default class UrgencyGroup extends React.Component {
  render() {
    const {count, urgency} = this.props;
    const classes = "slider level-" + urgency;
    
    return (
      <li className={classes}>
        <div className="slider-container">
          <span className="slider-grabber-wrapper"><span className="slider-grabber">{urgency}</span></span>
        </div>
        <div className="list-counter-bucket"><a className="list-counter" href="#counter">{count}</a></div>
      </li>
    );
  }
}

