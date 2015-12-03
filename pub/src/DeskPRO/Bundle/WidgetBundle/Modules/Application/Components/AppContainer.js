import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TriggerButtonContainer } from './TriggerButton';
import { WidgetApp } from './WidgetApp';
import { ChatTriggers } from './ChatTriggers';
import { widgetOpenedSelector } from '../Selectors/dpWindow';

@connect(state => ({
  widgetOpened: widgetOpenedSelector(state)
}))
export class AppContainer extends React.Component {

  static propTypes = {
    widgetOpened: PropTypes.bool
  };

  render() {
    const { widgetOpened } = this.props;

    return (
      <div>
        <TriggerButtonContainer isVisible={!widgetOpened} />
        <WidgetApp isVisible={widgetOpened} />
        <ChatTriggers isVisible={widgetOpened} />
      </div>
    );
  }
}
