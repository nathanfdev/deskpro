import React, {Component, PropTypes} from 'react';
import {ChoiceMenu, ChoiceMenuOption} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { isCommentsSelector } from '../../../Selectors/list';
import { loadCommentsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

import { connect } from 'react-redux';
@connect(state => ({
  types: state.Feedback.nav.get('types'),
  isComments: isCommentsSelector(state),
  filterParams: state.Feedback.list.get('currentListParams').get('filters')
}))

export class TypesCollectionContainer extends Component {

  static propTypes = {
    types: PropTypes.object.isRequired,
    filterParams: PropTypes.object,
    isComments: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  setFilter(value) {
    let values = [value];
    const {dispatch, filterParams, isComments} = this.props;
    if (filterParams && filterParams.get('category')) {
      const types = filterParams.get('category').toJS();
      const index = types.indexOf(value);
      if (index > -1) {
        values = types;
        values.splice(index, 1);
      } else {
        values = types.concat(values);
      }
    }
    dispatch(setFilterValue({ filter: 'category', value: values }));
    if (isComments) {
      dispatch(loadCommentsList());
    } else {
      dispatch(loadFeedbackList());
    }
  }

  render() {
    const {types, filterParams} = this.props;
    const values = filterParams && filterParams.get('category') ? filterParams.get('category').toJS() : null;
    return (
      <ChoiceMenu title="Feedback Type">
        <ul>
          {types.toJS().nested.map((item, index) =>
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