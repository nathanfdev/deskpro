import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAutoAttendantsLoadedSelector } from '../../../Selectors/autoAttendant';

@connect(state => ({
  agentsLoaded:         isAgentsLoadedSelector(state),
  queuesLoaded:         isQueuesLoadedSelector(state),
  autoAttendantsLoaded: isAutoAttendantsLoadedSelector(state)
}))
class LoadingContainer extends React.Component {

  static propTypes = {
    objectLoaded:         PropTypes.bool,
    agentsLoaded:         PropTypes.bool,
    queuesLoaded:         PropTypes.bool,
    autoAttendantsLoaded: PropTypes.bool,
    dispatch:             PropTypes.func,
    children:             PropTypes.node
  };

  static defaultProps = {
    objectLoaded: true
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadQueues());
    dispatch(loadAutoAttendants());
  }

  render() {
    const { objectLoaded, agentsLoaded, queuesLoaded, autoAttendantsLoaded } = this.props;
    const { children } = this.props;

    if (!objectLoaded || !agentsLoaded || !queuesLoaded || !autoAttendantsLoaded) {
      return <LoadingPage />;
    }

    return React.cloneElement(children, { ...children.props, ...this.props });
  }
}

export default LoadingContainer;
