import React from 'react';
import { Table, TableBody } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { TableHeader } from './TableHeader';
import { Row } from './Row';

export class ChatsTable extends React.Component {

  render() {
    return (
      <Table>
        <TableHeader />
        <TableBody>
          {this.props.elements.map((element, index) => <Row key={index} element={element}/>)}
        </TableBody>
      </Table>
    );
  }

}