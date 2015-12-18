import React from 'react';
import { connect } from 'react-redux';
import { helpPopupMessageSelector } from '../../../../Selectors/dpWindow';
import { AgentMessagePopup } from './AgentMessagePopup';

@connect(state => ({
  helpPopupMessage: helpPopupMessageSelector(state)
}))
export class AgentMessagePopupContainer extends React.Component {

  render() {
    return <AgentMessagePopup {...this.props} />;
  }
}
