/**
 * Component to toggle view between the two modes: List and Table
 */
import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import * as actions from "DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions";
import $ from "jquery";
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ControlButton } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';


export class ListTableViewSwitcher extends Component {

  static propTypes = {
    viewModeOptions: PropTypes.array.isRequired,
    listViewFields: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const {viewMode, viewModeOptions, listViewFields, tableViewFields, dispatch, displayFieldsStatus} = this.props;
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
              <Option key={index} active={viewMode === option.field} option={option} onClick={this.toggleView.bind(this, option)}/>
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

  toggleView(newView, event) {
    event.stopPropagation();
    const {dispatch} = this.props;
    dispatch(actions.toggleViewMode());
  }

  showViewModeChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    var elem           = $(event.target),
        viewModeChoice = elem.closest('.control-button').find('.dpw-navigation-dropdown');
    viewModeChoice.toggle();
  }

}


export class ViewSelector extends Component {
  render() {
    const {active, type, toggleView } = this.props;
    var classes = classNames('dpw-navigation-dropdown-item', {
      'active': active
    });
    var toggle;
    toggle      = active ?
                  (e)=> {
                    e.preventDefault()
                  }
      : toggleView;

    return (
      <li>
        <a href="#" className={classes} onClick={toggle}>
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-icon dpw-navigation-dropdown-item-icon-2x"><i
                  className="fa fa-list"></i></span>
              </span>
          <span className="dpw-navigation-dropdown-item-title">{type.charAt(0).toUpperCase() + type.slice(1)}
            View</span>
          {active ?
           <span className="dpw-navigation-dropdown-item-status"><i className="fa fa-check"></i></span>
            : ''
          }
        </a>
      </li>
    );
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