import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { feedbackSelector, peopleSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackStatusCategoriesSelector, feedbackCategoriesSelector }
  from '../../../../Selectors/recordStores';
import { idsSelector, currentListSortSelector, currentListOrderSelector }
  from '../../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => ({
  ids: idsSelector(state),
  feedback: feedbackSelector(state),
  viewFields: state.Feedback.list.get('tableVisibleFields'),
  feedbackTypes: feedbackTypesSelector(state),
  feedbackComments: feedbackCommentsSelector(state),
  feedbackStatusCategories: feedbackStatusCategoriesSelector(state),
  feedbackCategories: feedbackCategoriesSelector(state),
  people: peopleSelector(state),
  currentSort: currentListSortSelector(state),
  currentOrder: currentListOrderSelector(state)
}))
@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    ids: PropTypes.array.isRequired,
    people: PropTypes.object.isRequired,
    feedback: PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    feedbackComments: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    currentSort: PropTypes.string.isRequired,
    currentOrder: PropTypes.string.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  sortTable(param, order) {
    this.props.dispatch(applyParams({ sort: param, order }));
  }

  isVisible(field) {
    const {viewFields} = this.props;
    return viewFields && viewFields.includes(field);
  }

  renderStatus(statusCategory) {
    const { feedbackStatusCategories } = this.props;
    if (feedbackStatusCategories.get(statusCategory)) {
      return feedbackStatusCategories.get(statusCategory).get('title');
    }
  }

  renderCategory(id) {
    const { feedbackCategories } = this.props;
    if (feedbackCategories) {
      return feedbackCategories.get(id) ? feedbackCategories.get(id).get('input') : null;
    }
  }

  renderRow(id) {
    const { feedback, people, feedbackTypes } = this.props;
    const element = feedback.get(id);

    return (
      <tr key={id}>
        <TdId visible={this.isVisible('id')}>
          {element.get('id')}
        </TdId>
        <Td className="item-title" visible={this.isVisible('title')}>
          <a href="#"><SlicedString string={element.get('title')}/></a>
        </Td>
        <Td className="item-title" visible={this.isVisible('content')}>
          <a href="#"><SlicedString string={element.get('content')}/></a>
        </Td>
        <Td visible={this.isVisible('status_category')}>
          {this.renderStatus(element.get('status_category'))}
        </Td>
        <Td visible={this.isVisible('hidden_status')}>
          {element.get('hidden_status')}
        </Td>
        <Td visible={this.isVisible('person')}>
          <PersonInTable person={people.get(element.get('person'))}/>
        </Td>
        <Td visible={this.isVisible('type')}>
          {feedbackTypes.get(element.get('category')).get('title')}
        </Td>
        <Td visible={this.isVisible('category')}>
          {this.renderCategory(element.get('id'))}
        </Td>
        <Td visible={this.isVisible('num_ratings')}>
          {element.get('num_ratings')}
        </Td>
        <Td visible={this.isVisible('num_comments')}>
          {element.get('num_comments')}
        </Td>
        <Td visible={this.isVisible('date_created')}>
          <div className="dpw--timer"><FormattedRelative value={element.get('date_created')}/></div>
        </Td>
      </tr>
    );
  }

  render() {
    const { ids, currentSort, currentOrder } = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              title="ID"
              visible={this.isVisible('id')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="title"
              title="Title"
              visible={this.isVisible('title')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="content"
              title="Content"
              visible={this.isVisible('content')}/>
          <Th sort="status_category" title="Status"
              visible={this.isVisible('status_category')}/>
          <Th sort="hidden_status" title="Hidden"
              visible={this.isVisible('hidden_status')}/>
          <Th sort="person"
              title="Author"
              visible={this.isVisible('person')}
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="type" title="Type"
              visible={this.isVisible('type')}/>
          <Th sort="custom_category" title="Category"
              visible={this.isVisible('category')}/>
          <Th sort="num_ratings"
              title="Votes"
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}
              visible={this.isVisible('num_ratings')}/>
          <Th sort="num_comments" title="Comments"
              visible={this.isVisible('num_comments')}/>
          <Th sort="date_created" title="Created"
              currentOrder={currentOrder}
              currentSort={currentSort}
              onChange={this.sortTable.bind(this)}
              visible={this.isVisible('date_created')}/>
        </tr>
        </thead>
        <tbody>
        {ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }

}