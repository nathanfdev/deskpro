import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TriggerApp } from '../../Trigger/Components/TriggerApp';
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
        <TriggerApp />
        <WidgetApp isVisible={widgetOpened} />
        <ChatTriggers isVisible={widgetOpened} />
      </div>
    );
  }
}
