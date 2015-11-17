import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Row, Td, IdContainer, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { feedbackSelector } from '../../../../Selectors/list';
import { feedbackTypesSelector, feedbackCommentsSelector, feedbackCategoriesSelector, peopleSelector } from '../../../../Selectors/list';
import { defaultTableFields } from '../../../List/ControlBar/FeedbackViewOptions';
import { applyParams } from '../../../../Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  comments: state.Feedback.list.get('elements'),
  viewFields: state.Feedback.list.get('viewFields'),
  feedbackFromStore: feedbackSelector(state),
  feedbackComments: feedbackCommentsSelector(state),
  feedbackCategories: feedbackCategoriesSelector(state),
  feedbackTypes: feedbackTypesSelector(state),
  people: peopleSelector(state),
  commentsTableViewFields: state.Feedback.list.get('commentsTableViewFields')
}))

@injectIntl
export class FeedbackCommentTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    comments: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    commentsTableViewFields: PropTypes.array.isRequired,
    people: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    feedbackComments: PropTypes.object.isRequired,
    feedbackFromStore: PropTypes.object.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      order: '',
      sort: '',
      ownViewFields: {
        comment_id: { isShown: true },
        comment_content: { isShown: true },
        comment_author: { isShown: true }
      }
    };
  }

  sortTable(param, order) {
    this.props.dispatch(applyParams({ sort: param, order }));
  }

  renderStatus(id) {
    const { feedbackStatuses } = this.props;
    if (feedbackStatuses) {
      return feedbackStatuses.get(id) ? feedbackStatuses.get(id).get('title') : null;
    }
  }

  renderCategory(id) {
    const { feedbackCategories } = this.props;
    if (feedbackCategories) {
      return feedbackCategories.get(id) ? feedbackCategories.get(id).get('input') : null;
    }
  }

  renderCommentsCounter(id) {
    const { feedbackComments } = this.props;
    if (feedbackComments && feedbackComments.get(id)) {
      return feedbackComments.get(id).get('counter');
    }
    return 0;
  }

  renderLongString(string) {
    let content = string.substr(0, 40);
    if (string.length > 40) {
      content += '...';
    }
    return (
      <a href="#">{content}</a>
    );
  }


  render() {
    const { comments, viewFields, people, feedbackFromStore, feedbackTypes } = this.props;
    let tableFields = (viewFields && viewFields.get('table')) ? viewFields.get('table').toJS() : defaultTableFields;
    const {ownViewFields} = this.state;
    tableFields = { ...ownViewFields, ...tableFields };

    return (
      <Table>
        <thead>
          <tr>
            {tableFields.comment_id.isShown ?
              <Th value="id" label="ID" className="id-col sortable"
                  order={this.state.sort === 'id' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.comment_author.isShown ?
              <Th value="author" label="Author" className="sortable"
                  order={this.state.sort === 'id' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.comment_content.isShown ?
              <Th value="content" label="Content" className="id-col sortable"
                  order={this.state.sort === 'id' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.id.isShown ?
              <Th value="id" label="ID" className="id-col sortable"
                  order={this.state.sort === 'id' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.title.isShown ?
              <Th value="title" label="Title" order={this.state.sort === 'title' ? this.state.order : false}
                  className="sortable" sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.content.isShown ? <Th value="content" label="Content"/> : null }
            {tableFields.status_category.isShown ?
              <Th value="status_category" label="Status"/> : null }
            {tableFields.hidden_status.isShown ?
              <Th value="hidden_status" label="Hidden"/> : null }
            {tableFields.author_name.isShown ?
              <Th value="author_name" label="Author"/> : null }
            {tableFields.type.isShown ?
              <Th value="type" label="Type"/> : null }
            {tableFields.custom_category.isShown ?
              <Th value="custom_category" label="Category"/> : null }
            {tableFields.num_ratings.isShown ?
              <Th value="num_ratings" label="Votes" className="sortable"
                  order={this.state.sort === 'num_ratings' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
            {tableFields.num_comments.isShown ?
              <Th value="num_comments" label="Comments"/> : null }
            {tableFields.date_created.isShown ?
              <Th value="date_created" label="Created" className="sortable"
                  order={this.state.sort === 'date_created' ? this.state.order : false}
                  sortTable={this.sortTable.bind(this)}/> : null }
          </tr>
        </thead>
        <tbody>
          {comments.map(
            (element, index) => {
              const feedback = feedbackFromStore.get(element.feedback_id).toJS();

              return (
                <Row key={index}>
                  {tableFields.comment_id.isShown ?
                    <Td className="id-col"><IdContainer id={element.id}/></Td> : null }
                  {tableFields.comment_author.isShown ?
                    <Td>
                      <PersonInTable person={people.get(feedback.person)}/>
                    </Td>
                    : null }
                  {tableFields.comment_content.isShown ?
                    <Td className="item-title">{this.renderLongString(element.content)}</Td>
                    : null }
                  {tableFields.id.isShown ?
                    <Td className="id-col"><IdContainer id={feedback.id}/></Td> : null }
                  {tableFields.title.isShown ?
                    <Td className="item-title">{this.renderLongString(feedback.title)}</Td> : null }
                  {tableFields.content.isShown ?
                    <Td className="item-title">{this.renderLongString(feedback.content)}</Td> : null }
                  {tableFields.status_category.isShown ?
                    <Td>{this.renderStatus(feedback.id)}</Td> : null }
                  {tableFields.hidden_status.isShown ?
                    <Td>{feedback.hidden_status}</Td> : null }
                  {tableFields.author_name.isShown ?
                    <Td>
                      <PersonInTable person={people.get(feedback.person)}/>
                    </Td>
                    : null }
                  {tableFields.type.isShown ?
                    <Td>{feedbackTypes.get(feedback.category_id) ? feedbackTypes.get(feedback.category_id).get('title') : ''}</Td> : null }
                  {tableFields.custom_category.isShown ?
                    <Td>{this.renderCategory(feedback.id)}</Td> : null }
                  {tableFields.num_ratings.isShown ?
                    <Td>{feedback.num_ratings}</Td> : null }
                  {tableFields.num_comments.isShown ?
                    <Td>{this.renderCommentsCounter(feedback.id)}</Td> : null }
                  {tableFields.date_created.isShown ?
                    <Td>
                      <div className="dpw--timer"><FormattedRelative value={feedback.date_created}/></div>
                    </Td> : null }
                </Row>
              );
            }
          )}
        </tbody>
      </Table>
    );
  }
}