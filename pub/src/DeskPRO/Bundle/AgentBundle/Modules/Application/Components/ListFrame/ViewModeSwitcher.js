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
    toggleView: PropTypes.func.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const {viewMode, viewModeOptions, listViewFields, tableViewFields, displayFieldsStatus} = this.props;
    listViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    tableViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    return (
      <div className="control-button">
        <span className="title">View:</span>
        <ControlButton title={viewMode}/>
        <DropdownMenu>
          {viewModeOptions.map((option, index)=>
              <Option key={index} active={viewMode === option.field} option={option}
                      onClick={this.handleClick.bind(this, option)}/>
          )}
          <DropdownMenuFooter>
            <div className="dpw-navigation-dropdown-options-link">
              <a href="#" onClick={this.openOptionsSubmenu.bind(this)}>View Options <i className="fa fa-cog"></i></a>
            </div>
          </DropdownMenuFooter>
        </DropdownMenu>
        <ViewOptionsSubmenu viewMode={viewMode} viewModeOptions={viewModeOptions}
                            listViewFields={listViewFields} tableViewFields={tableViewFields}
                            displayFieldsStatus={ displayFieldsStatus}/>
      </div>
    );
  }

  /** Change sort option (Order By ...)*/
  handleClick(newView, event) {
    event.stopPropagation();
    const {toggleView} = this.props;
    toggleView(newView);
  }

  openOptionsSubmenu(event) {
    event.stopPropagation();
    var controlButton = $(event.target).closest('.control-button');
    controlButton.find('.dpw-navigation-dropdown').not('.dpw-navigation-dropdown-secondary').hide();
    controlButton.find('.dpw-navigation-dropdown-secondary').show();
  }
}