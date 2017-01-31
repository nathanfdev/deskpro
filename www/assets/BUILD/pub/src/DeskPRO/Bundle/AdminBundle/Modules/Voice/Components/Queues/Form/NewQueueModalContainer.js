import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueForm from './QueueForm';
import { createQueue } from '../../../Actions/queueActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { allAgentsSelector, isAgentsLoadedSelector } from '../../../../Application/Selectors/people';

@connect(state => ({
  agents:         allAgentsSelector(state),
  agentsLoaded:   isAgentsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state)
}))
class NewQueueModalContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    agents:         PropTypes.object,
    agentsLoaded:   PropTypes.bool,
    accounts:       PropTypes.object,
    accountsLoaded: PropTypes.bool,
    onClose:        PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAccounts());
  }

  onSubmit = (data) => {
    const { dispatch, onClose } = this.props;

    this.setState({
      saving: true,
      errors: {}
    });

    const promise = dispatch(createQueue(data));
    promise.success(() => onClose());
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  renderContent() {
    const { agents, accounts, agentsLoaded, accountsLoaded } = this.props;

    if (!agentsLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueForm
        {...this.state}
        agents={agents}
        accounts={accounts}
        onSubmit={this.onSubmit}
        onReturnBack={this.onReturnBack}
      />
    );
  }

  render() {
    return (
      <div className="page page-modal-window" style={{ height: '530px' }}>
        {this.renderContent()}
      </div>
    );
  }
}

export default NewQueueModalContainer;
