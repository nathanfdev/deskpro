import React from 'react';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { Row } from './Row';
import { TableHeaderContainer } from './TableHeaderContainer';

export class CrmTable extends React.Component {

  render() {
    return (
      <Table>
        <TableHeaderContainer/>
        <tbody>
          {this.props.elements.map((element, index) => <Row key={index} element={element}/>)}
        </tbody>
      </Table>
    );
  }
}
