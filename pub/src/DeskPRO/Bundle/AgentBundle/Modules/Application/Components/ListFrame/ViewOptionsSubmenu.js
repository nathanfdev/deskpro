import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class ViewOptionsSubmenu extends Component {

  render() {
    const {viewModeOptions, listViewFields, tableViewFields, displayFieldsStatus, currentViewMode} = this.props;
    return (
      <div className="dpw-navigation-dropdown dpw-navigation-dropdown-secondary">
        <ul>
          {viewModeOptions.map((option, index)=>
              <ViewConfigOption key={index} active={currentViewMode.field === option.field} option={option}
                                listViewFields={listViewFields} tableViewFields={tableViewFields}
                                displayFieldsStatus={displayFieldsStatus}/>
          )}
        </ul>
      </div>
    );
  }
}

export class ViewConfigOption extends Component {

  render() {
    const {active, option, listViewFields, tableViewFields, displayFieldsStatus} = this.props;
    var classes = classNames('dpw-navigation-dropdown-item-disc', {
      'dpw-navigation-dropdown-item-disc-active': active
    });
    switch (option.field) {
      case constants.VIEW_MODE_TABLE:
        var fields = tableViewFields;
        break;
      default:
        fields = listViewFields;
    }
    return (
      <li>
        <a href="#" className="dpw-navigation-dropdown-item">
              <span className="dpw-navigation-dropdown-item-mark">
                <span className={classes}></span>
              </span>
          <span className="dpw-navigation-dropdown-item-title">{option.label}</span>
        </a>
        {active ?
         <FieldsList fields={fields} displayFieldsStatus={displayFieldsStatus}/>
          : ''}
      </li>
    );
  }
}

export class FieldsList extends Component {

  render() {
    const {fields,displayFieldsStatus} = this.props;
    fields.sort(function (a, b) {
      return a.priority - b.priority
    });
    return (
      <div className="dpw-navigation-dropdown-column-list">
        <ul>
          {fields.map((field, index)=>
              <Field key={index} field={field} displayFieldsStatus={displayFieldsStatus}/>
          )}
        </ul>
      </div>
    );
  }

}

export class Field extends Component {

  render() {
    const {field, displayFieldsStatus} = this.props;
    var classes = classNames('dpw-navigation-dropdown-column-list-item', {
      'disabled': field.status === constants.FIELD_REQUIRED
    });
    var icon    = classNames('fa', {
      'fa-navicon': field.status !== constants.FIELD_REQUIRED,
      'fa-minus': field.status === constants.FIELD_REQUIRED
    });
    return (
      <li>
        <a className={classes} href="" onClick={this.handleChange.bind(this, field.name)}>
          <span className="dpw-navigation-dropdown-column-list-status">
            {field.status !== constants.FIELD_HIDDEN ? <i className="fa fa-check"></i> : ''}
          </span>
          <span className="dpw-navigation-dropdown-column-list-move"><i className={icon}></i></span>
          <span className="dpw-navigation-dropdown-column-list-title">{field.label}</span>
        </a>
      </li>
    );
  }

  handleChange(field, e) {
    e.preventDefault();
    const {displayFieldsStatus} = this.props;
    displayFieldsStatus('tableViewFields', field, constants.FIELD_SHOWN);
  }
}
