import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { isAgentsLoadedSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import Notifications from './Notifications';
import { loadAgents } from '../../../Application/Actions/peopleActions';
import { generateNotifications } from '../../Actions/devActions';

@connect(state => ({
  isAgentsLoaded: isAgentsLoadedSelector(state)
}))
class NotificationsContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    isAgentsLoaded: PropTypes.bool
  };

  componentDidMount() {
    this.props.dispatch(loadAgents());
  }

  onSubmit = value => this.props.dispatch(generateNotifications(value));

  render() {
    const { isAgentsLoaded } = this.props;
    if (!isAgentsLoaded) {
      return <LoadingPage />;
    }

    return <Notifications onSubmit={this.onSubmit} />;
  }
}

export default NotificationsContainer;
