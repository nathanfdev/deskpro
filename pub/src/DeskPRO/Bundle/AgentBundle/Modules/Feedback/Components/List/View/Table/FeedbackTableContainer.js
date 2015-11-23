import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { peopleSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackStatusCategoriesSelector, feedbackCategoriesSelector }
  from '../../../../Selectors/list';
import { Table, Th, Td, TdId, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { defaultTableFields } from '../../../List/ControlBar/FeedbackViewOptions';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => ({
  feedback: state.Feedback.list.get('elements'),
  viewFields: state.Feedback.list.get('tableVisibleFields'),
  feedbackTypes: feedbackTypesSelector(state),
  feedbackComments: feedbackCommentsSelector(state),
  feedbackStatusCategories: feedbackStatusCategoriesSelector(state),
  feedbackCategories: feedbackCategoriesSelector(state),
  people: peopleSelector(state)
}))
@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.object.isRequired,
    feedback: PropTypes.array.isRequired,
    feedbackStatusCategories: PropTypes.object,
    feedbackComments: PropTypes.object.isRequired,
    feedbackCategories: PropTypes.object.isRequired,
    feedbackTypes: PropTypes.object.isRequired,
    viewFields: PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      order: '',
      sort: ''
    };
  }

  sortTable(param, order) {
    this.props.dispatch(applyParams({ sort: param, order }));
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

  renderCommentsCounter(id) {
    const { feedbackComments } = this.props;
    if (feedbackComments.get(id)) {
      return feedbackComments.get(id).get('counter');
    }
    return 0;
  }

  isVisible(field) {
    return this.props.viewFields.includes(field);
  }

  render() {
    const { feedback, people, feedbackTypes } = this.props;

    return (
      <Table>
        <thead>
        <tr>
          <Th sort="id"
              title="ID"
              visible={this.isVisible('id')}
              order={this.state.sort === 'id' ? this.state.order : false}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="title"
              title="Title"
              visible={this.isVisible('title')}
              order={this.state.sort === 'title' ? this.state.order : false}
              onChange={this.sortTable.bind(this)}/>
          <Th sort="content"
              title="Content"
              visible={this.isVisible('content')}/>
          <Th sort="status_category" title="Status"
              visible={this.isVisible('status_category')}/>
          <Th sort="hidden_status" title="Hidden"
              visible={this.isVisible('hidden_status')}/>
          <Th sort="author_name" title="Author"
              visible={this.isVisible('person')}/>
          <Th sort="type" title="Type"
              visible={this.isVisible('type')}/>
          <Th sort="custom_category" title="Category"
              visible={this.isVisible('custom_category')}/>
          <Th sort="num_ratings"
              title="Votes"
              order={this.state.sort === 'num_ratings' ? this.state.order : false}
              onChange={this.sortTable.bind(this)}
              visible={this.isVisible('num_ratings')}/>
          <Th sort="num_comments" title="Comments"
              visible={this.isVisible('num_coments')}/>
          <Th sort="date_created" title="Created"
              order={this.state.sort === 'date_created' ? this.state.order : false}
              onChange={this.sortTable.bind(this)}
              visible={this.isVisible('date_created')}/>
        </tr>
        </thead>
        <tbody>
        {feedback.map((element, index) =>
            <tr key={index}>
              <TdId visible={this.isVisible('id')}>
                {element.id}
              </TdId>
              <Td className="item-title" visible={this.isVisible('title')}>
                {this.renderLongString(element.title)}
              </Td>
              <Td className="item-title" visible={this.isVisible('content')}>
                {this.renderLongString(element.content)}
              </Td>
              <Td visible={this.isVisible('status_category')}>
                {this.renderStatus(element.status_category)}
              </Td>
                <Td visible={this.isVisible('hidden_status')}>
                  {element.hidden_status}
                </Td>
                <Td visible={this.isVisible('person')}>
                  <PersonInTable person={people.get(element.person)}/>
                </Td>
                <Td visible={this.isVisible('type')}>
                  {feedbackTypes.get(element.category_id).get('title')}
                </Td>
                <Td visible={this.isVisible('custom_category')}>
                  {this.renderCategory(element.id)}
                </Td>
                <Td visible={this.isVisible('num_ratings')}>
                  {element.num_ratings}
                </Td>
                <Td visible={this.isVisible('num_comments')}>
                  {this.renderCommentsCounter(element.id)}
                </Td>
                <Td visible={this.isVisible('date_created')}>
                  <div className="dpw--timer"><FormattedRelative value={element.date_created}/></div>
                </Td>
            </tr>
        )}
        </tbody>
      </Table>
    );
  }

}