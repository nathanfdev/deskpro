import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import classNames from 'classnames';
import { AgentAvatars } from '../AgentAvatars';
import ChatPopup from '../ChatPopup';

export class AgentMessagePopup extends React.Component {

  static propTypes = {
    primaryAgent:        PropTypes.object,
    onClick:             PropTypes.func,
    children:            PropTypes.node,
    size:                PropTypes.string,
    popupStyle:          PropTypes.string,
    helpPopupTitle:      PropTypes.string,
    helpPopupMessage:    PropTypes.string,
    helpPopupHeading:    PropTypes.string,
    helpPopupSubheading: PropTypes.string
  };

  render() {
    const { primaryAgent = Immutable.fromJS({}), children, onClick } = this.props;
    const { helpPopupTitle, helpPopupMessage, helpPopupHeading, helpPopupSubheading, popupStyle, size } = this.props;
    const childProps = children.props;
    const showAgent = popupStyle.match(/agent/);
    const h1 = showAgent ? primaryAgent.get('display_name') : helpPopupHeading;

    return (
      <ChatPopup {...this.props}>
        <div className={classNames('preemtive-chat-content', size)} onClick={onClick}>
          <div className="dpdesignportal-chat-header">
            {showAgent && <AgentAvatars primaryAgent={primaryAgent} />}
            <h1><span>{h1}</span></h1>
            {showAgent && <h2>{helpPopupTitle}</h2>}
            {showAgent && helpPopupMessage && <p className="quote">{helpPopupMessage}</p>}
            {!showAgent && helpPopupSubheading && <p>{helpPopupSubheading}</p>}
          </div>
        </div>
        <div className="preemtive-chat-content">
          <div className="preemtive-chat-footer">
            {React.cloneElement(children, { ...childProps, primaryAgent })}
          </div>
        </div>
      </ChatPopup>
    );
  }
}
