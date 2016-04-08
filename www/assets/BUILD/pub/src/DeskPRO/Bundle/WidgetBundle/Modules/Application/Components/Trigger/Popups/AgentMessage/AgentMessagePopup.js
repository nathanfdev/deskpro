import React, { PropTypes } from 'react';
import { AgentAvatars } from '../AgentAvatars';
import Immutable from 'immutable';
import { ChatPopup } from '../ChatPopup';

export class AgentMessagePopup extends React.Component {

  static propTypes = {
    primaryAgent: PropTypes.object,
    onClick: PropTypes.func,
    children: PropTypes.node,
    helpPopupTitle: PropTypes.string,
    helpPopupMessage: PropTypes.string
  };

  render() {
    const { primaryAgent = Immutable.fromJS({}), children, onClick } = this.props;
    const { helpPopupTitle, helpPopupMessage } = this.props;
    const childProps = children.props;

    return (
      <ChatPopup {...this.props}>
        <div className="preemtive-chat-content" onClick={onClick}>
          <div className="dpdesignportal-chat-header">
            <AgentAvatars primaryAgent={primaryAgent} />

            <h1><span>{primaryAgent.get('name')}</span></h1>
            <h2>{helpPopupTitle}</h2>
            {helpPopupMessage && <p className="quote">{helpPopupMessage}</p>}
          </div>
        </div>
        <hr />
        <div className="preemtive-chat-content">
          <div className="preemtive-chat-footer">
            {React.cloneElement(children, { ...childProps, primaryAgent })}
          </div>
        </div>
      </ChatPopup>
    );
  }
}
