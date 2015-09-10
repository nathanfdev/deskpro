import React from 'react';
import { TableView, TableBody } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { TableHeader } from './TableHeader';
import { Row } from './Row';

export class ChatsTable extends React.Component {

  render() {
    return (
      <div>
        <TableView>
          <TableHeader />
          <TableBody>
            {this.props.elements.map((element, index) => <Row key={index} element={element}/>)}
          </TableBody>
        </TableView>
      </div>
    );
  }

}