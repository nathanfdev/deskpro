import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableView, TableHeader, Th, TableBody, Row, Td } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  feedback: state.Feedback.list.get('feedback'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS()
}))

export class FeedbackTableContainer extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  sortTable(param, order) {
    const {dispatch} = this.props;
    dispatch(setTableSort(param, order));
  }

  render() {
    const {feedback, tableViewFields} = this.props;
    const filteredFields = tableViewFields.filter(field => field.status !== constants.FIELD_HIDDEN);
    filteredFields.sort((a, b) => a.priority - b.priority);

    return (
      <TableView>
        <TableHeader>
          <tr>
            {filteredFields.map((field, index) =>
                <Th key={index} field={field} sortTable={this.sortTable.bind(this)}/>
            )}
          </tr>
        </TableHeader>
        <TableBody>
          {feedback.map((element, index) =>
              <Row key={index}>
                {filteredFields.map((field, ind) =>
                    <Td key={ind} field={field} element={element}/>
                )}
                </Row>
          )}
        </TableBody>
      </TableView>
    );
  }

}