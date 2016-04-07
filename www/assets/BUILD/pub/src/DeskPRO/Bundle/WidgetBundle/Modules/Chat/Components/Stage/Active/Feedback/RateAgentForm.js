import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class RateAgentForm extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    onSubmit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      comment: ''
    };
  }

  onSubmit = event => {
    event.preventDefault();
    this.props.onSubmit(this.state.comment);
  };

  onChangeComment = event => {
    this.setState({
      comment: event.target.value
    });
  };

  render() {
    const { agentName } = this.props;
    // portal.chat.feedback_not_helpful_title
    return (
      <div className="dpdesignportal-agent-rating">
        <h1>You have rated <div></div> {agentName} as <span className="negative">Not Helpful</span></h1>

        <div className="dpdesignportal-agent-rating-form">
          <form className="dpdesignportal-form" onSubmit={this.onSubmit}>
            <label>
              <span className="dpdesignportal-form-item-label-title">{portalPhrases.get('portal.chat.feedback_label')}</span>
              <textarea placeholder={portalPhrases.get('portal.chat.feedback_enter_message')}
                        value={this.state.comment}
                        onChange={this.onChangeComment} />
            </label>

            <div className="label button-label">
              <input type="submit" className="dpdesignportal-button" value={portalPhrases.get('portal.chat.feedback_action')} />
            </div>

            </form>
          </div>
        </div>
    );
  }
}
