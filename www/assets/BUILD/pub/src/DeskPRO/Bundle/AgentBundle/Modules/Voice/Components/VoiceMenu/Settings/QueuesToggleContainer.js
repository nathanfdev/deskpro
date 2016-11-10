import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { loadQueues, updateQueue } from '../../../Actions/queueActions';
import { allQueuesSelector } from '../../../Selectors/queue';
import Queues from './Queues';

@connect(state => ({
  me:     meSelector(state),
  queues: allQueuesSelector(state),
  agents: agentsSelector(state)
}))
class QueuesToggleContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadQueues());
  }

  onChange = (queue, agents) => {
    if (this.state.saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const { dispatch } = this.props;
    const promise = dispatch(updateQueue(queue.get('id'), { agents }));
    promise.success(() => {
      this.setState({
        saving: false
      });
    });
    promise.error(() => {
      this.setState({
        saving: false
      });
    });
  };

  render() {
    return (
      <Queues
        {...this.props}
        {...this.state}
        onChange={this.onChange}
      />
    );
  }
}

export default QueuesToggleContainer;
