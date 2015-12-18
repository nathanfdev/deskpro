import React from 'react';
import { connect } from 'react-redux';
import { HelpButton } from './HelpButton';
import { helpButtonSizeSelector } from '../../../Selectors/dpWindow';

@connect(state => ({
  size: helpButtonSizeSelector(state)
}))
export class HelpButtonContainer extends React.Component {

  render() {
    return <HelpButton {...this.props} />;
  }
}
