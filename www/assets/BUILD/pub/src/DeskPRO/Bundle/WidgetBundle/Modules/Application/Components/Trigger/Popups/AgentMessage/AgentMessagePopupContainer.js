import React from 'react';
import { connect } from 'react-redux';
import {
  helpPopupTitleSelector,
  helpPopupMessageSelector,
  helpPopupHeadingSelector,
  helpPopupSubheadingSelector
} from '../../../../Selectors/dpWindow';
import { AgentMessagePopup } from './AgentMessagePopup';

@connect(state => ({
  helpPopupTitle:   helpPopupTitleSelector(state),
  helpPopupMessage: helpPopupMessageSelector(state),
  helpPopupHeading: helpPopupHeadingSelector(state),
  helpPopupSubheading: helpPopupSubheadingSelector(state)

}))
export class AgentMessagePopupContainer extends React.Component {

  render() {
    return <AgentMessagePopup {...this.props} />;
  }
}
