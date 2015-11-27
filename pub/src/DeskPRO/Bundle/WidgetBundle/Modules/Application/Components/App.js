import React from 'react';
import TriggerButton from './TriggerButton';
import { WidgetApp } from './WidgetApp';

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

  renderTrigger() {
    return (
      <TriggerButton isVisible={!this.state.isOpen}
                     onClick={this.onOpenWidget} />
    );
  }

  renderWidgetApp() {
    return (
      <WidgetApp isVisible={this.state.isOpen}
                 onClose={this.onCloseWidget} />
    );
  }

  render() {
    return (
      <div>
        {this.renderTrigger()}
        {this.renderWidgetApp()}
      </div>
    );
  }
}
