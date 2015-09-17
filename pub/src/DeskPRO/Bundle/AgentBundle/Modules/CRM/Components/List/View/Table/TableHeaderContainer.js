import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';
import { TableHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'redux/react';
@connect(state => ({
  order: state.CrmNav.order,
  tableViewFields: state.CrmNav.tableViewFields
}))
export class TableHeaderContainer extends React.Component {

  render() {
    const { tableViewFields } = this.props;

    return (
      <TableHeader tableViewFields={tableViewFields}/>
    );
  }

}