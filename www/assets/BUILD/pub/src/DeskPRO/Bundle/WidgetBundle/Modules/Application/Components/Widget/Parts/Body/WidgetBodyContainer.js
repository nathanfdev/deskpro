import React from 'react';
import { connect } from 'react-redux';
import { isBubbleSelector, windowDimensionsSelector, widgetDimensionsSelector } from '../../../../Selectors/dpWindow';
import { WidgetBody } from './WidgetBody';

@connect(state => ({
  isBubble:         isBubbleSelector(state),
  windowDimensions: windowDimensionsSelector(state),
  widgetDimensions: widgetDimensionsSelector(state)
}))
export class WidgetBodyContainer extends React.Component {

  render() {
    return <WidgetBody {...this.props} />;
  }
}
