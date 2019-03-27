import PropTypes from 'prop-types';
import React from 'react';
import Timer from 'DeskPRO/Component/Timer';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import WarmTransferAudio from './WarmTransferAudio';
import Avatar from '../Common/Avatar';

class TransferStatus extends React.Component {

  static propTypes = {
    connection:  PropTypes.object,
    title:       PropTypes.string,
    cancelLabel: PropTypes.string,
    type:        PropTypes.string,
    target:      PropTypes.object,
    onCancel:    PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      rejected: false
    };
  }

  componentDidMount() {
    if (window.DeskPRO_Window) {
      const messageBroker = window.DeskPRO_Window.getMessageBroker();
      messageBroker.addMessageListener('agent.voice.conference.participant-ignore', this.onIgnore);
    }
  }

  componentWillUnmount() {
    if (window.DeskPRO_Window) {
      const messageBroker = window.DeskPRO_Window.getMessageBroker();
      messageBroker.removeMessageListener('agent.voice.conference.participant-ignore', this.onExternalSetHold);
    }
  }

  onIgnore = (event) => {
    const { target, connection } = this.props;

    // connection was lost
    if (!connection) {
      return;
    }

    // call id does not match
    if (parseInt(connection.callId, 10) !== parseInt(event.call_id, 10)) {
      return;
    }

    // requesting agent does not match
    if (target.get('id') !== parseInt(event.agent_id, 10)) {
      return;
    }

    this.setState({
      rejected: true
    });
  };

  onCancel = (event) => {
    event.preventDefault();

    const { target, type, onCancel } = this.props;
    onCancel(target, type);
  };

  render() {
    const { title, type, target, cancelLabel } = this.props;
    const { rejected } = this.state;

    return (
      <div>
        <div className="voice-transfer-status">
          <span className="voice-transfer-status-title">
            {type === 'warm' ? 'Calling ...' : title}
            {type === 'warm' ? <WarmTransferAudio /> : null}
          </span>
          <span className="voice-transfer-status-timer">
            <Timer />
          </span>
          <div className="voice-transfer-status-target">
            {target.has('avatar') && <Avatar person={target} size={24} />}
            <span className="target-name">
              {target.get('name')}
            </span>
          </div>
        </div>

        {rejected
          ? <div className="voice-transfer-status-buttons">
            Agent rejected your request
          </div>
          : <div className="voice-transfer-status-buttons">
            {type === 'warm' &&
              <div>
                <Button className="red" onClick={this.onCancel}>
                  Cancel add
                </Button>
              </div>}
            {type === 'cold' &&
              <Button className="basic" onClick={this.onCancel}>
                <i className="icon remove" />
                {cancelLabel}
              </Button>
            }
          </div>
        }
      </div>
    );
  }
}

export default TransferStatus;
