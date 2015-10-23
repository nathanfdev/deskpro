import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class ViewModeSwitcher extends Component {

  static propTypes = {
    currentViewMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    children: PropTypes.any
  };

  render() {
    const { currentViewMode, toggleDropdown } = this.props;
    const label = currentViewMode.label.substring(0, currentViewMode.label.indexOf(' '));

    return (
      <li>
        <ControlButton title="View:" icon={currentViewMode.icon}
                       label={label} toggleDropdown={toggleDropdown}/>
        {this.props.children}
      </li>
    );
  }


}