import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import StatusForm from './StatusForm';
import { editAgent } from '../../../Agent/Actions/agentActions';
import { isVoiceEnabledSelector } from '../../../Voice/Selectors/client';

@connect(state => ({
  me:           meSelector(state),
  voiceEnabled: isVoiceEnabledSelector(state)
}))
class StatusFormContainer extends React.Component {

  static propTypes = {
    me:       PropTypes.object,
    dispatch: PropTypes.func
  };

  onChange = (data) => {
    const { me, dispatch } = this.props;
    const agentData = me.get('agent_data') ? me.get('agent_data').toJS() : {};

    dispatch(editAgent(me.get('id'), {
      agent_data: {
        ...agentData,
        available_status:    data.status,
        agent_calls_enabled: data.calls
      }
    }));
  };

  render() {
    return (
      <StatusForm
        {...this.props}
        onChange={this.onChange}
      />
    );
  }
}

export default StatusFormContainer;
