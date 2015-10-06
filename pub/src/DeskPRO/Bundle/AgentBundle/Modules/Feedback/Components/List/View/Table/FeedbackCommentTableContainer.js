import React, {Component, PropTypes} from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { TableView, TableHeader, Th, TableBody, Row, Td, IdContainer, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { feedbackSelector } from '../../../../Selectors/list';
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { peopleSelector } from '../../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => ({
  comments: state.Feedback.list.get('comments'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS(),
  feedbackFromStore: feedbackSelector(state),
  people: peopleSelector(state),
  commentsTableViewFields: state.Feedback.list.get('commentsTableViewFields').toJS()
}))

@injectIntl
export class FeedbackCommentTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    comments: PropTypes.array.isRequired,
    tableViewFields: PropTypes.array.isRequired,
    people: PropTypes.array.isRequired,
    feedbackFromStore: PropTypes.array.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    commentsTableViewFields: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  sortTable(param, order) {
    const {dispatch} = this.props;
    dispatch(setTableSort(param, order));
  }

  tdContent(field, element) {
    const {people, feedbackStatuses} = this.props;
    let content = element[field.name];
    if (field.name === 'id') {
      return (
        <IdContainer id={element.id}/>
      );
    } else if (field.name === 'author_name') {
      return (
        <PersonInTable person={people[element.person_id]}/>
      );
    } else if (field.name === 'title') {
      content = element.title.substr(0, 40);
      if (element.title.length > 40) {
        content += '...';
      }
      return (
        <a href="#">{content}</a>
      );
    } else if (field.name === 'content') {
      content = element.content.substr(0, 40);
      if (element.content.length > 40) {
        content += '...';
      }
    } else if (field.name === 'date_created') {
      return (
        <FormattedRelative value={element.date_created}/>
      );
    } else if (field.name === 'date_published') {
      return (
        <FormattedRelative value={element.date_published}/>
      );
    } else if (field.name === 'status_category' && feedbackStatuses[element.id]) {
      content = feedbackStatuses[element.id].title;
    }
    return content;
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
            {commentsTableViewFieldsFiltered.map((field, index) => {
                if (field.name === 'date_created') {
                  return (
                    <Th key={index} field={field} sortable sortTable={this.sortTable.bind(this)}/>
                  );
                }
                return (
                  <Th key={index} field={field}/>
                );
              }
            )}
            {tableViewFieldsFiltered.map((field, index) =>
                <Th key={index} field={field}/>
            )}
          </tr>
        </TableHeader>
        <TableBody>
          {comments.map((element, index) => {
              let key = 0;
              return (
                <Row key={index}>
                  {commentsTableViewFieldsFiltered.map(field =>
                      <Td key={key++} className={field.className}>{this.tdContent(field, element)}</Td>
                  )}
                  {tableViewFieldsFiltered.map(field =>
                      <Td key={key++}
                          className={field.className}>{this.tdContent(field, feedbackFromStore[element.feedback_id])}</Td>
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