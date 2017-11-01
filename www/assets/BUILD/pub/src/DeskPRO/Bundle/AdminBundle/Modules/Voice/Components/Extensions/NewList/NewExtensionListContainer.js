import PropTypes from 'prop-types';
import React from 'react';
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
      loading: false,
      errors:  {}
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
      loading: true,
      errors:  {}
    });

    const agentId = agent.get('id');
    const promise = dispatch(addExtension(agentId, extension));
    promise.success(() => {
      this.setState({
        loading: false
      });
    });
    promise.error((result) => {
      this.setState({
        errors: {
          [agentId]: result.errors
        },
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
    promise.success(({ responses }) => {
      const errors = {};

      Object.keys(responses).forEach((agentId) => {
        const response = responses[agentId];
        const statusCode = response.headers['status-code'];

        if (statusCode !== 204) {
          errors[agentId] = response.errors;
        }
      });

      this.setState({
        errors,
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
