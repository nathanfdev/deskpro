import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class FilterBy extends Component {

  static propTypes = {
    children: PropTypes.any,
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
