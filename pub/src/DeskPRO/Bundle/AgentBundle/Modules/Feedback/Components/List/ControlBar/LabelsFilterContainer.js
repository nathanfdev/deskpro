import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import { LabelsFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/LabelsFilter';
import { setLabelsFilterMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { connect } from 'react-redux';
@connect(state => ({
  filterParams: state.Feedback.list.get('currentListParams').get('filters')
}))

export class LabelsFilterContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    filterParams: PropTypes.object
  };

  changeMode(mode) {
    const {dispatch} = this.props;
    dispatch(setLabelsFilterMode(mode));
  }

  render() {
    const {filterParams} = this.props;
    const labelsParams = filterParams && filterParams.get('labels') ? filterParams.get('labels') : Immutable.fromJS({ mode: 'all' });
    return (
      <LabelsFilter params={labelsParams} changeMode={this.changeMode.bind(this)}/>
    );
  }
}
