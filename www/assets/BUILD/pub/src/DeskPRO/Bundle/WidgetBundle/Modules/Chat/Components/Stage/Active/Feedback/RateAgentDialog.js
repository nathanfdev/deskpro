import React, { PropTypes } from 'react';

export class RateAgentDialog extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
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
    const { agentName } = this.props;

    return (
      <div className="dpdesignportal-agent-rating">
        <div></div>
        <h1><span>You just completed a chat with</span> {agentName}</h1>

        <div className="dpdesignportal-agent-rating-buttons">
          <a href="#" className="dpdesignportal-button" onClick={this.onClickHelpful}>
            <i className="fa fa-thumbs-up" /> Helpful
          </a>
          <a href="#" className="dpdesignportal-button negative" onClick={this.onClickNotHelpful}>
            <i className="fa fa-thumbs-down" /> Not Helpful
          </a>
        </div>
      </div>
    );
  }
}
