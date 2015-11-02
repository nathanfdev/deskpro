import React, {Component, PropTypes} from 'react';
import {ChoiceMenuOption, ChoiceMenuOptionGroup} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  statuses: state.Feedback.nav.get('statuses')
}))

export class StatusesCollectionContainer extends Component {

  static propTypes = {
    statuses: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  setFilter(model) {
    const {dispatch} = this.props;
    dispatch(setFilterValue(model));
    dispatch(loadFeedbackList());
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
      <ul>
        {items.map((item, index) =>
            <ChoiceMenuOption key={index} label={item.group} type="status" value={item.group}
                              onClick={this.setFilter.bind(this)}>
              <ChoiceMenuOptionGroup node={item} type="status_category" onClick={this.setFilter.bind(this)}/>
            </ChoiceMenuOption>
        )}
      </ul>
    );
  }
}