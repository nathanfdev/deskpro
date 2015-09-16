import React, {Component, PropTypes} from 'react';
import $ from "jquery";
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';
import { ViewOptionsSubmenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ViewOptionsSubmenu';


export class ViewModeSwitcher extends Component {

  static propTypes = {
    viewModeOptions: PropTypes.array.isRequired,
    listViewFields: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    toggleView: PropTypes.func.isRequired
  };

  render() {
    const {viewModeOptions, listViewFields, tableViewFields, displayFieldsStatus} = this.props;
    listViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    tableViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    let currentViewMode = viewModeOptions.find((option)=>option.current === true);
    return (
      <li>
        <ControlButton title="View:" icon={currentViewMode.icon} dropdownClass="view-mode-dropdown"
                       currentMode={currentViewMode.label.substring(0, currentViewMode.label.indexOf(' '))}/>
      </li>
    );
  }


}