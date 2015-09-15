/**
 * Component to toggle view between the two modes: List and Table
 */
import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import * as actions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions";
import $ from "jquery";

export class ListTableViewSwitcher extends Component {

  static propTypes = {
    listViewFields: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  showViewModeChoice(event) {
    event.preventDefault();
    event.stopPropagation();
    var elem           = $(event.target),
        viewModeChoice = elem.closest('.control-button').find('.dpw-navigation-dropdown');
    viewModeChoice.toggle();
  }


  render() {
    const {viewMode, listViewFields, tableViewFields, dispatch, displayFieldsStatus} = this.props;
    listViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    tableViewFields.sort(function (a, b) {
      return a.priority - b.priority
    });
    return (
      <div className="control-button">
        <span className="title">View:</span>
        <a href="#"><span className="focus" onClick={this.showViewModeChoice.bind(this)}>{viewMode}</span></a>
        <ListTableViewDropdown listViewFields={listViewFields} tableViewFields={tableViewFields} viewMode={viewMode}
                               dispatch={dispatch} displayFieldsStatus={displayFieldsStatus}/>
      </div>
    );
  }
}

export class ListTableViewDropdown extends Component {

  static propTypes = {
    listViewFields: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  toggleView(event) {
    event.stopPropagation();
    const {dispatch} = this.props;
    dispatch(actions.toggleViewMode());
  }


  render() {
    const {viewMode, listViewFields, tableViewFields, displayFieldsStatus } = this.props;

    return (
      <div className="dpw-navigation-dropdown">
        <ul>
          <ViewSelector type="list" active={viewMode === constants.VIEW_MODE_LIST}
                        toggleView={this.toggleView.bind(this)}/>
          <ViewSelector type="table" active={viewMode === constants.VIEW_MODE_TABLE}
                        toggleView={this.toggleView.bind(this)}/>
          <li>
            <div className="dpw-navigation-dropdown-item dpw-navigation-dropdown-footer">
              <div className="dpw-navigation-dropdown-options-link">
                <a href="#">View Options <i className="fa fa-cog"></i></a>
              </div>
            </div>
          </li>
        </ul>
      </div>
    );
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