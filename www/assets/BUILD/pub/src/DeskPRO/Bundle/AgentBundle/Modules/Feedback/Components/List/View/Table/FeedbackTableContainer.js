import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { SlicedString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SlicedString';
import { Table, Th, Td, TdId, PersonInTable }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { idsSelector, currentListOrderBySelector, currentListOrderDirSelector }
  from '../../../../Selectors/list';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  ids:                      idsSelector(state),
  feedback:                 collectionSelectorFactory('Feedback', 'feedback')(state),
  orderBy:                  currentListOrderBySelector(state),
  orderDir:                 currentListOrderDirSelector(state),
  viewFields:               state.Feedback.list.get('tableVisibleFields'),
  feedbackTypes:            collectionSelectorFactory('FeedbackType', 'feedback')(state),
  feedbackComments:         collectionSelectorFactory('FeedbackComment', 'feedback')(state),
  feedbackStatusCategories: collectionSelectorFactory('FeedbackStatusCategory', 'feedback')(state),
  feedbackCategories:       collectionSelectorFactory('FeedbackCategory', 'feedback')(state),
  people:                   collectionSelectorFactory('Person', 'feedback')(state)
}))
@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    intl:                     intlShape.isRequired,
    ids:                      PropTypes.array.isRequired,
    people:                   PropTypes.object.isRequired,
    feedback:                 PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    feedbackComments:         PropTypes.object.isRequired,
    feedbackCategories:       PropTypes.object.isRequired,
    feedbackTypes:            PropTypes.object.isRequired,
    viewFields:               PropTypes.object,
    orderBy:                  PropTypes.string.isRequired,
    orderDir:                 PropTypes.string.isRequired,
    dispatch:                 PropTypes.func.isRequired
  };

  sortTable = (orderBy, orderDir) => {
    this.props.dispatch(applyParams({ order_by: orderBy, order_dir: orderDir }));
  };

  isVisible(field) {
    const { viewFields } = this.props;
    return viewFields && viewFields.includes(field);
  }

  renderStatus(statusCategory) {
    const { feedbackStatusCategories } = this.props;
    if (feedbackStatusCategories.get(statusCategory)) {
      return feedbackStatusCategories.get(statusCategory).get('title');
    }
    return null;
  }

  renderCategory(id) {
    const { feedbackCategories } = this.props;
    if (feedbackCategories) {
      return feedbackCategories.get(id) ? feedbackCategories.get(id).get('input') : null;
    }
    return null;
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
          <a href="#"><SlicedString string={element.get('title')} /></a>
        </Td>
        <Td className="item-title" visible={this.isVisible('content')}>
          <a href="#"><SlicedString string={element.get('content')} /></a>
        </Td>
        <Td visible={this.isVisible('status_category')}>
          {this.renderStatus(element.get('status_category'))}
        </Td>
        <Td visible={this.isVisible('hidden_status')}>
          {element.get('hidden_status')}
        </Td>
        <Td visible={this.isVisible('person')}>
          <PersonInTable person={people.get(element.get('person'))} />
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
          <div className="dpw--timer"><FormattedRelative value={element.get('date_created')} /></div>
        </Td>
      </tr>
    );
  }

  render() {
    const { ids, orderBy, orderDir } = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th
            sort="id"
            title="ID"
            visible={this.isVisible('id')}
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
          />
          <Th
            sort="title"
            title="Title"
            visible={this.isVisible('title')}
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
          />
          <Th sort="content" title="Content" visible={this.isVisible('content')} />
          <Th sort="status_category" title="Status" visible={this.isVisible('status_category')} />
          <Th sort="hidden_status" title="Hidden" visible={this.isVisible('hidden_status')} />
          <Th
            sort="person"
            title="Author"
            visible={this.isVisible('person')}
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
          />
          <Th sort="type" title="Type" visible={this.isVisible('type')} />
          <Th
            sort="custom_category"
            title="Category"
            visible={this.isVisible('category')}
          />
          <Th
            sort="num_ratings"
            title="Votes"
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
            visible={this.isVisible('num_ratings')}
          />
          <Th sort="num_comments" title="Comments" visible={this.isVisible('num_comments')} />
          <Th
            sort="date_created"
            title="Created"
            orderDir={orderDir}
            orderBy={orderBy}
            onChange={this.sortTable}
            visible={this.isVisible('date_created')}
          />
        </tr>
        </thead>
        <tbody>
        {ids.map(id => this.renderRow(id))}
        </tbody>
      </Table>
    );
  }
}
