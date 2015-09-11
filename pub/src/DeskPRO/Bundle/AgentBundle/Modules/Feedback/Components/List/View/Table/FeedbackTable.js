import React from 'react';
import { TableView, TableBody } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { Row } from './Row';
import { TableHeaderContainer } from './TableHeaderContainer';

export class FeedbackTable extends React.Component {

  render() {
    return (
      <div>
        <TableView>
          <TableHeaderContainer/>
          <TableBody>
            {this.props.elements.map((element, index) => <Row key={index} element={element}/>)}
          </TableBody>
        </TableView>
      </div>
    );
  }

}