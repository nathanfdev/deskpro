import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueList from './QueueList';
import { replaceRoute } from '../../../../../Services/history';
import { loadQueues } from '../../../Actions/queueActions';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';

@connect(state => ({
  queues: allQueuesSelector(state),
  loaded: isQueuesLoadedSelector(state)
}))
class QueueListContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    queues:   PropTypes.object,
    loaded:   PropTypes.bool
  };

  componentDidMount() {
    this.props.dispatch(loadQueues());
  }

  onAddQueue = (event) => {
    event.preventDefault();
    replaceRoute('/voice_channel/queues/new');
  };

  onEditQueue = (queue) => {
    replaceRoute(`/voice_channel/queues/${queue.get('id')}`);
  };

  render() {
    const { loaded, queues } = this.props;

    if (!loaded) {
      return <LoadingPage />;
    }

    return (
      <QueueList
        queues={queues}
        onAddQueue={this.onAddQueue}
        onEditQueue={this.onEditQueue}
      />
    );
  }
}

export default QueueListContainer;
