import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';
import { TableHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  order: state.CrmNav.order,
  tableViewFields: state.CrmNav.tableViewFields
}))
export class TableHeaderContainer extends React.Component {

  render() {
    return (
      <thead />
    );
  }

}