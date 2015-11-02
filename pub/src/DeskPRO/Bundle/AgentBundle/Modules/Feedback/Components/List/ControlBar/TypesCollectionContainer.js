import React, {Component, PropTypes} from 'react';
import {ChoiceMenuOption} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  types: state.Feedback.nav.get('types')
}))

export class TypesCollectionContainer extends Component {

  static propTypes = {
    types: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  setFilter(model) {
    const {dispatch} = this.props;
    dispatch(setFilterValue(model));
    dispatch(loadFeedbackList());
  }

  render() {
    const {types} = this.props;
    return (
      <ul>
        {types.toJS().map((item, index) =>
            <ChoiceMenuOption
              key={index}
              label={item.title}
              type="category"
              value={item.title}
              onClick={this.setFilter.bind(this)}
              />
        )}
      </ul>
    );
  }
}