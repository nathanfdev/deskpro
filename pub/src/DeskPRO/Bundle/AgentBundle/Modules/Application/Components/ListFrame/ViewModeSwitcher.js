import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';

export class ViewModeSwitcher extends Component {

  static propTypes = {
    currentViewMode: PropTypes.object.isRequired
  };

  render() {
    const {viewModeOptions, currentViewMode} = this.props;

    let label = currentViewMode.label.substring(0, currentViewMode.label.indexOf(' '));

    return (
      <li>
        <ControlButton title="View:" icon={currentViewMode.icon} dropdownClass="view-mode-dropdown"
                       label={label}/>
      </li>
    );
  }


}