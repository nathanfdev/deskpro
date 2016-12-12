import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NewExtensionList from './NewExtensionList';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { addExtension, addExtensions } from '../../../Actions/extensionActions';
import { voicePeopleSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  agents:       voicePeopleSelector(state),
  agentsLoaded: isAgentsLoadedSelector(state),
  queues:       allQueuesSelector(state),
  queuesLoaded: isQueuesLoadedSelector(state)
}))
class NewExtensionListContainer extends React.Component {

  static propTypes = {
    agentsLoaded: PropTypes.bool,
    queuesLoaded: PropTypes.bool,
    dispatch:     PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: false
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadQueues());
  }

  onClickBack = () => {
    replaceRoute('/voice_channel/extensions');
  };

  onAddExtension = (agent, extension) => {
    const { dispatch } = this.props;
    this.setState({
      loading: true
    });

    const promise = dispatch(addExtension(agent.get('id'), extension));
    promise.success(() => {
      this.setState({
        loading: false
      });
    });
    promise.error(() => {
      this.setState({
        loading: false
      });
    });
  };

  onAddAllSuggested = (data) => {
    const { dispatch } = this.props;
    this.setState({
      loading: true
    });

    const promise = dispatch(addExtensions(data));
    promise.success(() => {
      this.setState({
        loading: false
      });
    });
    promise.error(() => {
      this.setState({
        loading: false
      });
    });
  };

  render() {
    const { agentsLoaded, queuesLoaded } = this.props;

    if (!agentsLoaded || !queuesLoaded) {
      return <LoadingPage />;
    }

    return (
      <NewExtensionList
        {...this.props}
        {...this.state}
        onClickBack={this.onClickBack}
        onAddExtension={this.onAddExtension}
        onAddAllSuggested={this.onAddAllSuggested}
      />
    );
  }
}

export default NewExtensionListContainer;
