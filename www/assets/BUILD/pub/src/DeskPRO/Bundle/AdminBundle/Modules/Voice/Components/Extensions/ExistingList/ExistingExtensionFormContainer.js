import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import ExistingExtensionForm from './ExistingExtensionForm';
import { editAgent } from '../../../../Application/Actions/peopleActions';

@connect()
class ExistingExtensionFormContainer extends React.Component {

  static propTypes = {
    agent:    PropTypes.object,
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving:   false,
      deleting: false,
      errors:   {}
    };
  }

  onSubmit = (data) => {
    const { agent, dispatch } = this.props;
    if (this.state.deleting) {
      return;
    }

    this.setState({
      saving:   true,
      deleting: false,
      errors:   {}
    });

    const promise = dispatch(editAgent(agent.get('id'), data));
    promise.success(() => {
      this.setState({
        saving: false
      });
    });
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  onDelete = () => {
    const { agent, dispatch } = this.props;
    if (this.state.saving) {
      return;
    }

    this.setState({
      saving:   false,
      deleting: true,
      errors:   {}
    });

    const agentData = agent.get('agent_data') ? agent.get('agent_data').toJS() : {};
    const promise = dispatch(editAgent(agent.get('id'), {
      agent_data: {
        ...agentData,
        extension_number: null
      }
    }));

    promise.success(() => {
      this.setState({
        deleting: false
      });
    });
    promise.error((result) => {
      this.setState({
        errors:   result.errors,
        deleting: false
      });
    });
  };

  render() {
    return (
      <ExistingExtensionForm
        {...this.props}
        {...this.state}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
      />
    );
  }
}

export default ExistingExtensionFormContainer;
