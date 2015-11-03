import React, {Component, PropTypes} from 'react';
import {ChoiceMenu, ChoiceMenuOption} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { isCommentsSelector } from '../../../Selectors/list';
import { loadCommentsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  categories: state.Feedback.nav.get('customCategories'),
  isComments: isCommentsSelector(state),
  filterParams: state.Feedback.list.get('currentListParams').get('filters')
}))

export class CategoriesCollectionContainer extends Component {

  static propTypes = {
    categories: PropTypes.object.isRequired,
    filterParams: PropTypes.object,
    isComments: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  setFilter(value) {
    let values = [value];
    const {dispatch, filterParams, isComments} = this.props;
    if (filterParams && filterParams.get('custom_category')) {
      const types = filterParams.get('custom_category').toJS();
      const index = types.indexOf(value);
      if (index > -1) {
        values = types;
        values.splice(index, 1);
      } else {
        values = types.concat(values);
      }
    }
    dispatch(setFilterValue({ filter: 'custom_category', value: values }));
    if (isComments) {
      dispatch(loadCommentsList());
    } else {
      dispatch(loadFeedbackList());
    }
  }

  render() {
    const {categories, filterParams} = this.props;
    const values = filterParams && filterParams.get('custom_category') ? filterParams.get('custom_category').toJS() : null;
    return (
      <ChoiceMenu title="Feedback Category">
        <ul>
          {categories.toJS().map((item, index) =>
              <ChoiceMenuOption
                key={index}
                values={values}
                label={item.group}
                value={item.group}
                onClick={this.setFilter.bind(this)}
                />
          )}
        </ul>
      </ChoiceMenu>
    );
  }
}