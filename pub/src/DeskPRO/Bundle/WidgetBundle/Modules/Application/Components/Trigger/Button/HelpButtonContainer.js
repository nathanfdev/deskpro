import React from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { helpButtonSizeSelector, helpPopupSelector } from '../../../Selectors/dpWindow';

@connect(state => ({
  size: helpButtonSizeSelector(state),
  popup: helpPopupSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  render() {
    return <HelpButton {...this.props} />;
  }
}
