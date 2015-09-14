import React from 'react';
import { TableBody, Row } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'redux/react';
@connect(state => ({
  feedback: state.FeedbackList.feedback,
  tableViewFields: state.FeedbackList.tableViewFields
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