/**
 * Component to toggle view between the two modes: List and Table
 */
import React, {Component, PropTypes} from 'react';
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
    var elem = $(event.target),
      viewModeChoice = elem.closest('div.ticket-control-button').find('div.view-mode-choice');
    viewModeChoice.css('display') === 'none' ? viewModeChoice.show() : viewModeChoice.hide();
  }


  render() {
    const {viewMode, listViewFields, tableViewFields, dispatch, displayFieldsStatus} = this.props;
    listViewFields.sort(function(a, b){
      return a.priority-b.priority
    });
    tableViewFields.sort(function(a, b){
      return a.priority-b.priority
    });
    return (
      <div className="ticket-control-button">
        <span className="title">View:</span>
        <a href="#">
          <span className="focus" onClick={this.showViewModeChoice.bind(this)}>{viewMode}</span>
        </a>
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

  closeDropdown(e) {
    event.preventDefault();
    event.stopPropagation();
    $(e.target).closest('.dropdown-choice').hide();
  }

  render() {
    const {viewMode, listViewFields, tableViewFields, displayFieldsStatus } = this.props;

    return (
      <div className="view-mode-choice dropdown-choice" style={{width:'300px'}}>
        <p>This dropdown is prototype only!</p>

        <div style={{textAlign:'right', width:'100%'}}>
          <a href="#" onClick={this.closeDropdown.bind(this)}><span>X</span></a>
        </div>
        <div style={{width:'50%',float:'left'}}>
          <label>
            <input name="view-mode" type="radio" defaultChecked={viewMode === constants.VIEW_MODE_LIST}
                   onChange={this.toggleView.bind(this)}>
              List View
            </input>
          </label>
          <br/>
          <fieldset>
            <legend>Display fields</legend>
            <DisplayFields fields={listViewFields} displayFieldsStatus={displayFieldsStatus} type="list"/>
          </fieldset>
        </div>
        <div style={{width:'50%',float:'left'}}>
          <label>
            <input name="view-mode" type="radio" defaultChecked={viewMode === constants.VIEW_MODE_TABLE}
                   onChange={this.toggleView.bind(this)}>
              Table View
            </input>
          </label>
          <br/>
          <fieldset>
            <legend>Display fields</legend>
            <DisplayFields fields={tableViewFields} displayFieldsStatus={displayFieldsStatus} type="table"/>
          </fieldset>
        </div>
        <button>Save fields</button>
      </div>
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