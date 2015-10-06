import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { TableView, TableHeader, Th, TableBody, Row, Td } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { feedbackSelector } from '../../../../Selectors/list';
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  comments: state.Feedback.list.get('comments'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS(),
  feedbackFromStore: feedbackSelector(state),
  commentsTableViewFields: state.Feedback.list.get('commentsTableViewFields').toJS()
}))

export class FeedbackCommentTableContainer extends Component {

  static propTypes = {
    comments: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    feedbackFromStore: PropTypes.array.isRequired,
    commentsTableViewFields: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  sortTable(param, order) {
    const {dispatch} = this.props;
    dispatch(setTableSort(param, order));
  }

  render() {
    const { comments, tableViewFields, commentsTableViewFields, feedbackFromStore } = this.props;
    const tableViewFieldsFiltered = tableViewFields.filter(field => field.status !== constants.FIELD_HIDDEN);
    tableViewFieldsFiltered.sort((prev, next) => prev.priority - next.priority);
    const commentsTableViewFieldsFiltered = commentsTableViewFields.filter(field => field.status !== constants.FIELD_HIDDEN);
    commentsTableViewFieldsFiltered.sort((prev, next) => prev.priority - next.priority);

    return (
      <TableView>
        <TableHeader>
          <tr>
            <th colSpan={commentsTableViewFieldsFiltered.length}>Comment</th>
            <th colSpan={tableViewFieldsFiltered.length}>Feedback</th>
          </tr>
          <tr>
            {commentsTableViewFieldsFiltered.map((field, index) =>
                <Th key={index} field={field} sortTable={this.sortTable.bind(this)}/>
            )}
            {tableViewFieldsFiltered.map((field, index) =>
                <Th key={index} field={field} sortTable={this.sortTable.bind(this)}/>
            )}
          </tr>
        </TableHeader>
        <TableBody>
          {comments.map((element, index) => {
              let key = 0;
              return (
                <Row key={index}>
                  {commentsTableViewFieldsFiltered.map(field =>
                      <Td key={key++} field={field} element={element}/>
                  )}
                  {tableViewFieldsFiltered.map(field =>
                      <Td key={key++} field={field} element={feedbackFromStore[element.feedback_id]}/>
                  )}
                </Row>
              );
            }
          )}
        </TableBody>
      </TableView>
    );
  }
}