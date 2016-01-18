import React, { PropTypes } from 'react';
import { meSelector, meStateSelector } from '../../RecordStores/Selectors/meSelectors';
import { NotificationService } from 'DeskPRO/Bundle/AgentBundle/Services/NotificationService';
import { connect } from 'react-redux';

@connect(state => ({
  user: meSelector(state),
  userStatus: meStateSelector.statusSel(state),
  actionAlerts: state.Application.notifications.get('actionAlerts'),
  actionAlertsSetup: state.Application.notifications.get('actionAlertsSetup')
}))
export class NotificationServiceContainer extends React.Component {

  static propTypes = {
    userStatus: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    actionAlerts: PropTypes.object.isRequired,
    actionAlertsSetup: PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    this.started = false;
  }

  componentDidMount() {
    this.setupPolling();
  }

  componentDidUpdate() {
    this.setupPolling();
  }

  componentWillUnmount() {
    if (this.ns) {
      this.ns.stopPolling();
      this.started = false;
    }
  }

  setupPolling() {
    const { user, userStatus, dispatch, actionAlerts, actionAlertsSetup} = this.props;
    if (userStatus.get('isDone') && !actionAlertsSetup && !this.started) {
      this.ns = new NotificationService({ user, dispatch, clients: actionAlerts.clients });
      this.ns.startPolling();
      this.started = true;
    }
  }

  render() {
    return <span/>;
  }
}