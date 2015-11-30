import React, { PropTypes } from 'react';

export class RateAgent extends React.Component {

  static propTypes = {
    onClickHelpful: PropTypes.func,
    onClickNotHelpful: PropTypes.func
  };

  onClickHelpful = event => {
    event.preventDefault();
    this.props.onClickHelpful();
  };

  onClickNotHelpful = event => {
    event.preventDefault();
    this.props.onClickNotHelpful();
  };

  render() {
    return (
      <div className="dpdesignportal-agent-rating">
        <div></div>
        <h1><span>You just completed a chat with</span> Noelle Gray</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>

        <div className="dpdesignportal-agent-rating-buttons">
          <a href="#" className="dpdesignportal-button" onClick={this.onClickHelpful}>
            <i className="fa fa-thumbs-up"></i> Helpful
          </a>
          <a href="#" className="dpdesignportal-button negative" onClick={this.onClickNotHelpful}>
            <i className="fa fa-thumbs-down"></i> Not Helpful
          </a>
        </div>
      </div>
    );
  }
}
