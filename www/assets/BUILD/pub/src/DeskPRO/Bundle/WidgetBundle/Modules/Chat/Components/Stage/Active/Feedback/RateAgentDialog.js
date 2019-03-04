import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class RateAgentDialog extends React.Component {

  static propTypes = {
    agentName:         PropTypes.string,
    onClickHelpful:    PropTypes.func,
    onClickNotHelpful: PropTypes.func
  };

  onClickHelpful = (event) => {
    event.preventDefault();
    this.props.onClickHelpful();
  };

  onClickNotHelpful = (event) => {
    event.preventDefault();
    this.props.onClickNotHelpful();
  };

  render() {
    const { agentName } = this.props;

    return (
      <div className="dpdesignportal-agent-rating">
        <div />
        <h1><span>{portalPhrases.get('portal.chat.rate_agent_title', { agentName })}</span></h1>

        <div className="dpdesignportal-agent-rating-buttons">
          <button className="dpdesignportal-button" onClick={this.onClickHelpful}>
            <i className="fa fa-thumbs-up" /> {portalPhrases.get('portal.chat.helpful')}
          </button>
          <button className="dpdesignportal-button negative" onClick={this.onClickNotHelpful}>
            <i className="fa fa-thumbs-down" /> {portalPhrases.get('portal.chat.not_helpful')}
          </button>
        </div>
      </div>
    );
  }
}
export default RateAgentDialog;
