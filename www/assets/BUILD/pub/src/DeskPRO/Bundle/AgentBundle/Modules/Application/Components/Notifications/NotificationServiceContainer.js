import PropTypes from 'prop-types';
import React from 'react';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { NotificationService } from 'DeskPRO/Bundle/AgentBundle/Services/NotificationService';
import ActionAlertsHandler from 'DeskPRO/Bundle/AgentBundle/Services/ActionAlertsHandler';
import NotificationsHandler from 'DeskPRO/Bundle/AgentBundle/Services/NotificationsHandler';
import { connect } from 'react-redux';
import { SeparateComponent } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/SeparateComponent';

@connect(state => ({
  user:              meSelector(state),
  actionAlerts:      state.Application.notifications.get('actionAlerts'),
  actionAlertsSetup: state.Application.notifications.get('actionAlertsSetup'),
  state
}))
export class NotificationServiceContainer extends SeparateComponent {

  static propTypes = {
    user:              PropTypes.object.isRequired,
    dispatch:          PropTypes.func.isRequired,
    actionAlerts:      PropTypes.object.isRequired,
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

  static getType() {
    return 'NotificationService';
  }

  setupPolling() {
    const { user, dispatch, actionAlerts, actionAlertsSetup } = this.props;

    if (!actionAlertsSetup && !this.started) {
      const aah = new ActionAlertsHandler({ dispatch, me: user.get('id') });
      const nh = new NotificationsHandler({ dispatch, me: user.get('id') });
      this.ns = new NotificationService({
        user,
        clients:              actionAlerts.clients,
        actionAlertsHandler:  aah,
        notificationsHandler: nh
      });
      this.ns.startPolling();
      this.started = true;
    }
  }

  render() {
    return <span />;
  }
}

export default NotificationServiceContainer;
