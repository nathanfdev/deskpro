import React from 'react';
import { TableView } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { TableHeaderContainer } from './TableHeaderContainer';
import { TableBodyContainer } from './TableBodyContainer';

export class FeedbackTable extends React.Component {

  render() {
    return (
      <TableView>
        <TableHeaderContainer/>
        <TableBodyContainer/>
      </TableView>
    );
  }

}