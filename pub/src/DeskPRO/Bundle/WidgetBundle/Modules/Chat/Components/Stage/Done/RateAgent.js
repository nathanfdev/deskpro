import React from 'react';

export class RateAgent extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-agent-rating">
        <div></div>
        <h1><span>You just completed a chat with</span> Noelle Gray</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>

        <div className="dpdesignportal-agent-rating-buttons">
          <a href="#" className="dpdesignportal-button"><i className="fa fa-thumbs-up"></i> Helpful</a>
          <a href="#" className="dpdesignportal-button negative"><i className="fa fa-thumbs-down"></i> Not Helpful</a>
        </div>
      </div>
    );
  }
}
