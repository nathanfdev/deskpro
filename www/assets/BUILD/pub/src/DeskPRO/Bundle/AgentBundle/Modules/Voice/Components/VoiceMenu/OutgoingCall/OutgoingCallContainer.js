import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import OutgoingCall from './OutgoingCall';
import { hangup } from '../../../Actions/clientActions';
import { connectionsSelector } from '../../../Selectors/client';

@connect(state => ({
  people:      allPeopleSelector(state),
  connections: connectionsSelector(state)
}))
class OutgoingCallContainer extends React.Component {

  static propTypes = {
    connections:  PropTypes.object,
    outgoingCall: PropTypes.object,
    dispatch:     PropTypes.func
  };

  onHangup = () => {
    const { connections, outgoingCall, dispatch } = this.props;
    const connection = connections
      .filter(c => c.callId === outgoingCall.getIn(['phoneCall', 'id']))
      .first();

    if (!connection) {
      return;
    }

    dispatch(hangup(connection));
  };

  render() {
    return (
      <OutgoingCall
        {...this.props}
        onHangup={this.onHangup}
      />
    );
  }
}

export default OutgoingCallContainer;
