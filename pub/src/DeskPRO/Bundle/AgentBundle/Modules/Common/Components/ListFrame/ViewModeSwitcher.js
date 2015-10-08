import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';

export class ViewModeSwitcher extends Component {

  static propTypes = {
    currentViewMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
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