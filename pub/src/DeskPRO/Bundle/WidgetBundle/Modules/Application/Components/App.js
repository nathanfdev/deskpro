import React from 'react';
import TriggerButton from './TriggerButton';
import { WidgetApp } from './WidgetApp';
import { ChatTriggers } from './ChatTriggers';

export class App extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      isOpen: false
    };
  }

  onOpenWidget = () => {
    this.setState({
      isOpen: true
    });
  };

  onCloseWidget = () => {
    this.setState({
      isOpen: false
    });
  };

  render() {
    const widgetOpened = this.state.isOpen;

    return (
      <div>
        <TriggerButton isVisible={!widgetOpened} onClick={this.onOpenWidget} />
        <WidgetApp ref="widget" isVisible={widgetOpened} onClose={this.onCloseWidget} />
        <ChatTriggers onOpen={url => this.refs.widget.onOpen(url)} isVisible={widgetOpened} />
      </div>
    );
  }
}
