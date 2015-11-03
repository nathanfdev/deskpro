import React, {Component, PropTypes} from 'react';
import {ChoiceMenu, ChoiceMenuOption, ChoiceMenuOptionGroup} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  statuses: state.Feedback.nav.get('statuses'),
  filterParams: state.Feedback.list.get('currentListParams').get('filters')
}))

export class StatusesCollectionContainer extends Component {

  static propTypes = {
    statuses: PropTypes.object.isRequired,
    filterParams: PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  setFilter(type, value) {
    let values = [value];
    const {dispatch, filterParams} = this.props;
    if (filterParams && filterParams.get(type)) {
      const types = filterParams.get(type).toJS();
      const index = types.indexOf(value);
      if (index > -1) {
        values = types;
        values.splice(index, 1);
      } else {
        values = types.concat(values);
      }
    }
    dispatch(setFilterValue({ filter: type, value: values }));
    dispatch(loadFeedbackList());
  }

  getChosenValues(type) {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get(type)) {
      return filterParams.get(type).toJS();
    }
    return null;
  }

  render() {
    const {statuses} = this.props;
    const { active, closed, hidden } = statuses.toJS();
    const {newFeedback} = statuses.toJS().new;
    // @ToDo Rename 'new' within statuses
    const items = [
      { ...newFeedback, group: 'new' },
      { ...active, group: 'active' },
      { ...closed, group: 'closed' },
      { ...hidden, group: 'hidden' }
    ];

    return (
      <ChoiceMenu title="Feedback Status">
        <ul>
          {items.map((item, index) =>
              <ChoiceMenuOption
                key={index}
                label={item.group}
                type="status"
                values={this.getChosenValues('status')}
                value={item.group}
                onClick={this.setFilter.bind(this, 'status')}
                >
                <ChoiceMenuOptionGroup
                  node={item}
                  type="status_category"
                  values={this.getChosenValues('status_category')}
                  onClick={this.setFilter.bind(this, 'status_category')}
                  />
              </ChoiceMenuOption>
          )}
        </ul>
      </ChoiceMenu>
    );
  }
}