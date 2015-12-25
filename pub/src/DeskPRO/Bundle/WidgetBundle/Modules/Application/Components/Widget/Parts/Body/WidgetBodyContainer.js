import React from 'react';
import { connect } from 'react-redux';
import { isBubbleSelector } from '../../../../Selectors/dpWindow';
import { WidgetBody } from './WidgetBody';

@connect(state => ({
  isBubble: isBubbleSelector(state)
}))
export class WidgetBodyContainer extends React.Component {

  render() {
    return <WidgetBody {...this.props} />;
  }
}
