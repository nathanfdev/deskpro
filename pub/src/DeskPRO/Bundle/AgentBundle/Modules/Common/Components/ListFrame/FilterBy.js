import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';

export class FilterBy extends Component {

  static propTypes = {
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {currentFilterMode, toggleDropdown} = this.props;
    return (
      <li>
        <ControlButton
          title="Filter by:"
          icon={currentFilterMode.icon}
          label={currentFilterMode.label}
          toggleDropdown={toggleDropdown}
          />
        {this.props.children}
      </li>
    );
  }
}
