import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import ExistingExtensionList from './ExistingExtensionList';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { voicePeopleSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  agents:       voicePeopleSelector(state),
  agentsLoaded: isAgentsLoadedSelector(state),
  queues:       allQueuesSelector(state),
  queuesLoaded: isQueuesLoadedSelector(state)
}))
class ExistingExtensionListContainer extends React.Component {

  static propTypes = {
    agentsLoaded: PropTypes.bool,
    queuesLoaded: PropTypes.bool,
    dispatch:     PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadQueues());
  }

  onAddExtensions = () => {
    replaceRoute('/voice_channel/extensions/new');
  };

  onEditExtension = (agent) => {
    replaceRoute(`/voice_channel/extensions/${agent.get('id')}`);
  };

  render() {
    const { agentsLoaded, queuesLoaded } = this.props;

    if (!agentsLoaded || !queuesLoaded) {
      return <LoadingPage />;
    }

    return (
      <ExistingExtensionList
        {...this.props}
        onAddExtensions={this.onAddExtensions}
        onEditExtension={this.onEditExtension}
      />
    );
  }
}

export default ExistingExtensionListContainer;
