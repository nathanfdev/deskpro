import React, {Component, PropTypes} from 'react';
import { intlShape, injectIntl, FormattedRelative } from 'react-intl';
import { peopleSelector, feedbackTypesSelector, feedbackCommentsSelector, feedbackStatusesSelector, feedbackCategoriesSelector }
  from '../../../../Selectors/list';
import { Table, Th, Td, IdContainer, PersonInTable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { defaultTableFields } from '../../../List/ControlBar/FeedbackViewOptions';
import { applyParams } from '../../../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => ({
  feedback: state.Feedback.list.get('elements'),
  viewFields: state.Feedback.list.get('viewFields'),
  feedbackTypes: feedbackTypesSelector(state),
  feedbackComments: feedbackCommentsSelector(state),
  feedbackStatuses: feedbackStatusesSelector(state),
  feedbackCategories: feedbackCategoriesSelector(state),
  people: peopleSelector(state)
}))
@injectIntl
export class FeedbackTableContainer extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    people: PropTypes.object.isRequired,
    feedback: PropTypes.array.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
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
    if (feedbackComments.get(id)) {
      return feedbackComments.get(id).get('counter');
    }
    return 0;
  }

  render() {
    const { feedback, viewFields, people, feedbackTypes } = this.props;
    let tableFields = (viewFields && viewFields.table) ? viewFields.table : defaultTableFields;

    return (
      <Table>
        <thead>
          <tr>
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
          {feedback.map((element, index) =>
              <tr key={index}>
                {tableFields.id.isShown ?
                  <Td className="id-col"><IdContainer id={element.id}/></Td> : null }
                {tableFields.title.isShown ?
                  <Td className="item-title">{this.renderLongString(element.title)}</Td> : null }
                {tableFields.content.isShown ?
                  <Td className="item-title">{this.renderLongString(element.content)}</Td> : null }
                {tableFields.status_category.isShown ?
                  <Td>{this.renderStatus(element.id)}</Td> : null }
                {tableFields.hidden_status.isShown ?
                  <Td>{element.hidden_status}</Td> : null }
                {tableFields.author_name.isShown ?
                  <Td>
                    <PersonInTable person={people.get(element.person)}/>
                  </Td>
                  : null }
                {tableFields.type.isShown ?
                  <Td>{feedbackTypes.get(element.category_id).get('title')}</Td> : null }
                {tableFields.custom_category.isShown ?
                  <Td>{this.renderCategory(element.id)}</Td> : null }
                {tableFields.num_ratings.isShown ?
                  <Td>{element.num_ratings}</Td> : null }
                {tableFields.num_comments.isShown ?
                  <Td>{this.renderCommentsCounter(element.id)}</Td> : null }
                {tableFields.date_created.isShown ?
                  <Td>
                    <div className="dpw--timer"><FormattedRelative value={element.date_created}/></div>
                  </Td> : null }
              </tr>
          )}
        </tbody>
      </Table>
    );
  }

}