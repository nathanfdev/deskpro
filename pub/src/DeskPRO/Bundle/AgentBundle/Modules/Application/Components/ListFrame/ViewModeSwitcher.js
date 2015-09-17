import React, {Component, PropTypes} from 'react';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';

export class ViewModeSwitcher extends Component {

  static propTypes = {
    viewModeOptions: PropTypes.array.isRequired,
    listViewFields: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired
  };

  render() {
    const {viewModeOptions} = this.props;

    let currentViewMode = viewModeOptions.find((option)=>option.current === true),
        label = currentViewMode.label.substring(0, currentViewMode.label.indexOf(' '));

    return (
      <li>
        <ControlButton title="View:" icon={currentViewMode.icon} dropdownClass="view-mode-dropdown"
                       label={label}/>
      </li>
    );
  }


}