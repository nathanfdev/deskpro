import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import ExtensionForm from './ExtensionForm';
import { loadAgents, editAgent } from '../../../../Application/Actions/peopleActions';
import { replaceRoute } from '../../../../../Services/history';
import { voicePeopleSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';

@connect(state => ({
  agents:       voicePeopleSelector(state),
  agentsLoaded: isAgentsLoadedSelector(state)
}))
class EditExtensionContainer extends React.Component {

  static propTypes = {
    params:       PropTypes.object,
    agents:       PropTypes.object,
    agentsLoaded: PropTypes.bool,
    dispatch:     PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      deleting: false
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
  }

  onSubmit = (data) => {
    const agent = this.getAgent();
    const { dispatch } = this.props;

    const promise = dispatch(editAgent(agent.get('id'), data));
    promise.success(() => {
      replaceRoute('/voice_channel/extensions');
    });

    return promise;
  };

  onDelete = () => {
    const agent = this.getAgent();
    const { dispatch } = this.props;

    this.setState({
      deleting: true
    });

    const promise = dispatch(editAgent(agent.get('id'), {
      agent_data: {
        extension_number: null
      }
    }));

    promise.success(() => {
      this.setState({
        deleting: false
      }, () => replaceRoute('/voice_channel/extensions'));
    });
  };

  onReturnBack = () => {
    replaceRoute('/voice_channel/extensions');
  };

  getAgent() {
    const { params, agents } = this.props;
    const agentId = parseInt(params.agentId, 10);

    return agents && agents.get(agentId);
  }

  render() {
    const { agentsLoaded } = this.props;
    const agent = this.getAgent();

    if (!agent || !agentsLoaded) {
      return <LoadingPage />;
    }

    return (
      <ExtensionForm
        {...this.props}
        {...this.state}
        agent={this.getAgent()}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
        onReturnBack={this.onReturnBack}
      />
    );
  }
}

export default EditExtensionContainer;
