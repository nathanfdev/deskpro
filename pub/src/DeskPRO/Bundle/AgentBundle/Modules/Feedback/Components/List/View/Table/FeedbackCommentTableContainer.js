import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { feedbackSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackCategoriesSelector, peopleSelector }
  from '../../../../Selectors/recordStores';
import { idsSelector, currentListSortSelector, currentListOrderSelector }
  from '../../../../Selectors/list';
import { applyParams } from '../../../../Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  ids: idsSelector(state),
  comments: feedbackCommentsSelector(state),
  feedback: feedbackSelector(state),
  feedbackCategories: feedbackCategoriesSelector(state),
  feedbackTypes: feedbackTypesSelector(state),
  people: peopleSelector(state),
  viewFields: state.Feedback.list.get('commentsTableVisibleFields'),
  currentSort: currentListSortSelector(state),
  currentOrder: currentListOrderSelector(state)
}))

@injectIntl
export class FeedbackCommentTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    ids: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired,
    comments: PropTypes.object.isRequired,
    viewFields: PropTypes.array.isRequired,
    people: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired
  };

  isVisible(field) {
    const {viewFields} = this.props;
    return viewFields && viewFields.includes(field);
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

  renderRow(id) {
    const { comments, people, feedback, feedbackTypes } = this.props;
    const element = comments.get(id);
    const parent = feedback.get(element.get('feedback'));

    return (
      <tr key={id}>
        <TdId visible={this.isVisible('comment_id')}>
          {element.get('id')}
        </TdId>
        <Td visible={this.isVisible('comment_author')}>
          <PersonInTable person={people.get(element.get('person'))}/>
        </Td>
        <Td visible={this.isVisible('comment_content')}
            className="item-title">
          <a href="#"><SlicedString string={element.get('content')}/></a>
        </Td>
        <TdId visible={this.isVisible('id')}>
          {parent.get('id')}
        </TdId>
        <Td className="item-title"
            visible={this.isVisible('title')}>
          <a href="#"><SlicedString string={parent.get('title')}/></a>
        </Td>
        <Td className="item-title"
            visible={this.isVisible('content')}>
          <a href="#"><SlicedString string={parent.get('content')}/></a>
        </Td>
        <Td visible={this.isVisible('status_category')}>
          {this.renderStatus(parent.get('id'))}
        </Td>
        <Td visible={this.isVisible('hidden_status')}>
          {parent.get('hidden_status')}
        </Td>
        <Td visible={this.isVisible('author_name')}>
          <PersonInTable person={people.get(parent.get('person'))}/>
        </Td>
        <Td visible={this.isVisible('type')}>
          {feedbackTypes.get(parent.get('category')) ? feedbackTypes.get(parent.get('category')).get('title') : ''}
        </Td>
        <Td visible={this.isVisible('custom_category')}>
          {this.renderCategory(parent.get('id'))}
        </Td>
        <Td visible={this.isVisible('num_ratings')}>
          {parent.get('num_ratings')}
        </Td>
        <Td visible={this.isVisible('num_comments')}>
          {parent.get('num_comments')}
        </Td>
        <Td visible={this.isVisible('date_created')}>
          <div className="dpw--timer"><FormattedRelative value={parent.get('date_created')}/></div>
        </Td>
      </tr>
    );
  }

  render() {
    const { ids, currentOrder, currentSort } = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              visible={this.isVisible('comment_id')}
              title="ID"
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="author"
              title="Author"
              visible={this.isVisible('comment_author')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="content"
              title="Content"
              visible={this.isVisible('comment_content')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="id"
              title="Feedback ID"
              visible={this.isVisible('id')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="title"
              title="Feedback title"
              visible={this.isVisible('title')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th visible={this.isVisible('content')}
              title="Feedback content"/>
          <Th sort="status_category"
              visible={this.isVisible('status_category')}
              title="Status"/>
          <Th sort="hidden_status"
              visible={this.isVisible('hidden_status')}
              title="Hidden"/>
          <Th sort="author_name"
              visible={this.isVisible('author_name')}
              title="Author"/>
          <Th sort="type"
              visible={this.isVisible('type')}
              title="Type"/>
          <Th sort="custom_category"
              visible={this.isVisible('custom_category')}
              title="Category"/>
          <Th sort="num_ratings"
              visible={this.isVisible('num_ratings')}
              title="Votes"
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="num_comments"
              visible={this.isVisible('num_comments')}
              title="Comments"/>
          <Th sort="date_created"
              visible={this.isVisible('date_created')}
              title="Created"
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
        </tr>
        </thead>
        <tbody>
        {ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}