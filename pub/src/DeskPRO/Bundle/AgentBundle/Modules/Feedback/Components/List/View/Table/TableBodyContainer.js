import React from 'react';
import { TableBody, Row } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  feedback: state.Feedback.list.get('feedback'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS()
}))
export class TableBodyContainer extends React.Component {

  render() {
    const { feedback, tableViewFields } = this.props;
    return (
      <TableBody>
        {feedback.map((element, index) =>
            <Row key={index} element={element} tableViewFields={tableViewFields}/>
        )}
      </TableBody>
    );
  }

}