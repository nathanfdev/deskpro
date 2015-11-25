import React from 'react';
import TriggerButton from './TriggerButton';
import WidgetApp from './WidgetApp';

export class App extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      isOpen: false
    };
  }

  openWidget = () => {
    this.setState({
      isOpen: true
    });
  };

  renderTrigger() {
    return <TriggerButton isVisible={!this.state.isOpen} onClick={this.openWidget} />;
  }

  renderWidgetApp() {
    return <WidgetApp isVisible={this.state.isOpen} />;
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
