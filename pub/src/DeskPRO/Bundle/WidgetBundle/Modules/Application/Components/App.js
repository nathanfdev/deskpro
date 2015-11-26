import React, { PropTypes } from 'react';
import TriggerButton from './TriggerButton';
import { WidgetApp } from './WidgetApp';

export class App extends React.Component {

  static propTypes = {
    store: PropTypes.object
  };

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
    return <WidgetApp isVisible={this.state.isOpen} store={this.props.store} />;
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
