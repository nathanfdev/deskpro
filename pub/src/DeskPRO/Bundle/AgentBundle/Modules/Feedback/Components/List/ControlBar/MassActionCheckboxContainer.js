import React, {Component, PropTypes} from 'react';
import { MassActionCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';
import { toggleMassAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';

@connect(state => ({
  massAction: state.Feedback.list.get('massAction'),
  count: state.Feedback.list.get('feedback').size
}))

export class MassActionCheckboxContainer extends Component {

  handleClick = (e) => {
    e.preventDefault();
    this.props.dispatch(toggleMassAction());
  };


  render() {
    const {count, massAction} = this.props;

    return (
      <MassActionCheckbox count={count} massAction={massAction} onClick={this.handleClick}/>
    );
  }
}