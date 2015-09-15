import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';


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
              <a href="#">View Options <i className="fa fa-cog"></i></a>
            </div>
          </DropdownMenuFooter>
        </DropdownMenu>
      </div>
    );
  }

  /** Change sort option (Order By ...)*/
  handleClick(newView, event) {
    event.stopPropagation();
    const {toggleView} = this.props;
    toggleView(newView);
  }
}


export class DisplayFields extends Component {
  static propTypes = {
    fields: PropTypes.array.isRequired,
    type: PropTypes.string.isRequired,
    displayFieldsStatus: PropTypes.func.isRequired
  };

  render() {
    const {fields, type, displayFieldsStatus} = this.props;
    return (
      <div style={{overflowY:'scroll', height:'200px'}} className="fields-container">
        {type === 'table' ?
         fields.map((field, index) =>
           <FieldForTableView key={index} field={field} displayFieldsStatus={displayFieldsStatus}/>)
          :
         fields.map((field, index) =>
           <FieldForListView key={index} field={field} displayFieldsStatus={displayFieldsStatus}/>)
        }
      </div>
    );
  }
}

export class FieldForTableView extends Component {
  static propTypes = {
    field: PropTypes.object.isRequired,
    displayFieldsStatus: PropTypes.func.isRequired
  };

  render() {
    const {field, displayFieldsStatus} = this.props;
    return (
      <div>
        <input type="checkbox" defaultChecked={field.status === constants.FIELD_SHOWN}
               onChange={this.handleChange.bind(this, displayFieldsStatus, field.name)}/>
        <span>{field.label}</span>
      </div>
    );
  }

  handleChange(displayFieldsStatus, field, e) {
    let status = $(e.target).prop('checked') ? constants.FIELD_SHOWN : constants.FIELD_HIDDEN;
    displayFieldsStatus('tableViewFields', field, status);
  }
}

export class FieldForListView extends Component {
  static propTypes = {
    field: PropTypes.object.isRequired,
    displayFieldsStatus: PropTypes.func.isRequired
  };

  render() {
    const {field, displayFieldsStatus} = this.props;
    return (
      <div>
        {field.status === constants.FIELD_REQUIRED ?
         <input type="checkbox" checked="true" readOnly/>
          :
         <input type="checkbox" defaultChecked={field.status === constants.FIELD_SHOWN}
                onChange={this.handleChange.bind(this, displayFieldsStatus, field.name)}/>}
        <span>{field.label}</span>
      </div>
    );
  }

  handleChange(displayFieldsStatus, field, e) {
    let status = $(e.target).prop('checked') ? constants.FIELD_SHOWN : constants.FIELD_HIDDEN;
    displayFieldsStatus('listViewFields', field, status);
  }
}