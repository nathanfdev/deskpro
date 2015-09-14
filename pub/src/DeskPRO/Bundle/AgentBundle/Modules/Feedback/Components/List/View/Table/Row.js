import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { Td } from './DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'redux/react';
@connect(state => ({
  tableViewFields: state.FeedbackList.tableViewFields
}))
export class Row extends React.Component {

  render() {
    const { element,tableViewFields } = this.props;
    let filteredFields = tableViewFields.filter(function (field) {
      return field.status !== constants.FIELD_HIDDEN
    });
    filteredFields.sort(function (a, b) {
      return a.priority - b.priority
    });

    return (
      <tr className="single-row">
        {filteredFields.map((field, index) =>
          <Td key={index} field={field} element={element}/>
        )}
      </tr>);
  }
}