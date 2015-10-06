import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { peopleSelector } from '../../../../Selectors/list';
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableView, TableHeader, Th, TableBody, Row, Td, IdContainer, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  feedback: state.Feedback.list.get('feedback'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS(),
  people: peopleSelector(state)
}))

@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.array.isRequired,
    feedback: PropTypes.array.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    tableViewFields: PropTypes.array.isRequired,
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
    } else if (field.name === 'status_category') {
      content = feedbackStatuses[element.id].title;
    }
    return content;
  }

  render() {
    const {feedback, tableViewFields} = this.props;
    const filteredFields = tableViewFields.filter(field => field.status !== constants.FIELD_HIDDEN);
    filteredFields.sort((prev, next) => prev.priority - next.priority);

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
                {filteredFields.map((field, key) =>
                    <Td key={key} className={field.className}>{this.tdContent(field, element)}</Td>
                )}
              </Row>
          )}
        </TableBody>
      </TableView>
    );
  }

}