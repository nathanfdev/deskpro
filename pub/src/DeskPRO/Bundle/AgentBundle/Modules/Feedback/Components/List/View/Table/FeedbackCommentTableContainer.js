import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { feedbackSelector } from '../../../../Selectors/list';
import { feedbackTypesSelector, feedbackCommentsSelector, feedbackCategoriesSelector, peopleSelector } from '../../../../Selectors/list';
import { defaultTableFields } from '../../../List/ControlBar/FeedbackViewOptions';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';

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
            <Th sort="id"
                title="ID"
                order={this.state.sort === 'id' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.comment_author.isShown ?
            <Th sort="author"
                title="Author"
                order={this.state.sort === 'id' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.comment_content.isShown ?
            <Th sort="content"
                title="Content"
                order={this.state.sort === 'id' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.id.isShown ?
            <Th sort="id"
                title="ID"
                order={this.state.sort === 'id' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.title.isShown ?
            <Th sort="title"
                title="Title"
                order={this.state.sort === 'title' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.content.isShown ? <Th sort="content" title="Content"/> : null }
          {tableFields.status_category.isShown ?
            <Th sort="status_category" title="Status"/> : null }
          {tableFields.hidden_status.isShown ?
            <Th sort="hidden_status" title="Hidden"/> : null }
          {tableFields.author_name.isShown ?
            <Th sort="author_name" title="Author"/> : null }
          {tableFields.type.isShown ?
            <Th sort="type" title="Type"/> : null }
          {tableFields.custom_category.isShown ?
            <Th sort="custom_category" title="Category"/> : null }
          {tableFields.num_ratings.isShown ?
            <Th sort="num_ratings"
                title="Votes"
                order={this.state.sort === 'num_ratings' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
          {tableFields.num_comments.isShown ?
            <Th sort="num_comments" title="Comments"/> : null }
          {tableFields.date_created.isShown ?
            <Th sort="date_created"
                title="Created"
                order={this.state.sort === 'date_created' ? this.state.order : false}
                onChange={this.sortTable.bind(this)}/> : null }
        </tr>
        </thead>
        <tbody>
        {comments.map(
          (element, index) => {
            const feedback = feedbackFromStore.get(element.feedback).toJS();

            return (
              <tr key={index}>
                {tableFields.comment_id.isShown ?
                  <TdId>{element.id}</TdId> : null }
                {tableFields.comment_author.isShown ?
                  <Td>
                    <PersonInTable person={people.get(feedback.person)}/>
                  </Td>
                  : null }
                {tableFields.comment_content.isShown &&
                <Td className="item-title"><a href="#"><SlicedString string={element.content}/></a></Td>}
                {tableFields.id.isShown ?
                  <TdId>{feedback.id}</TdId> : null }
                {tableFields.title.isShown &&
                  <Td className="item-title"><a href="#"><SlicedString string={feedback.title}/></a></Td>}
                {tableFields.content.isShown &&
                  <Td className="item-title"><a href="#"><SlicedString string={feedback.content}/></a></Td> }
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
              </tr>
            );
          }
        )}
        </tbody>
      </Table>
    );
  }
}