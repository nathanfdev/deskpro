import React, { PropTypes } from 'react';
import { meSelector, meStateSelector } from '../../RecordStores/Selectors/meSelectors';
import { NotificationService } from 'DeskPRO/Bundle/AgentBundle/Services/NotificationService';
import { ActionAlertsHandler } from 'DeskPRO/Bundle/AgentBundle/Services/ActionAlertsHandler';
import { connect } from 'react-redux';

@connect(state => ({
  user: meSelector(state),
  userStatus: meStateSelector.statusSel(state),
  actionAlerts: state.Application.notifications.get('actionAlerts'),
  actionAlertsSetup: state.Application.notifications.get('actionAlertsSetup'),
  state: state
}))
export class NotificationServiceContainer extends React.Component {

  static propTypes = {
    userStatus: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    state: PropTypes.object.isRequired,
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
      const aah = new ActionAlertsHandler({dispatch: dispatch, me: user.get('id')});

      this.ns = new NotificationService({ user, clients: actionAlerts.clients, actionAlertsHandler: aah });
      this.ns.startPolling();
      this.started = true;
    }
  }

  render() {
    return <span/>;
  }
}