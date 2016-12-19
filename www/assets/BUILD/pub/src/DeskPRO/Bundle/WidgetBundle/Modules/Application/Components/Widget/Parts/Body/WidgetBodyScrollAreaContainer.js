import React from 'react';
import { connect } from 'react-redux';
import { WidgetBodyScrollArea } from './WidgetBodyScrollArea';
import { widgetBodyHeightSelector } from '../../../../Selectors/dpWindow';

@connect(state => ({
  height: widgetBodyHeightSelector(state)
}))
export class WidgetBodyScrollAreaContainer extends React.Component {

  render() {
    return <WidgetBodyScrollArea {...this.props} />;
  }
}
