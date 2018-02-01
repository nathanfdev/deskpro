import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import CallForward from './CallForward';
import { editAgentProfile } from '../../../../Agent/Actions/agentActions';

@connect(state => ({
  me: meSelector(state)
}))
class CallForwardContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onSubmit = data => this.props.dispatch(editAgentProfile(data.agent_data));

  render() {
    return (
      <CallForward
        {...this.props}
        onSubmit={this.onSubmit}
      />
    );
  }
}

export default CallForwardContainer;
