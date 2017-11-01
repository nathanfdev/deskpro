import PropTypes from 'prop-types';
import React from 'react';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AccountList from './AccountList';
import { loadAccounts } from '../../../Actions/accountActions';
import { allAccountsSelector, isAccountsLoadedSelector } from '../../../Selectors/account';
import NewAccountContainer from '../Form/NewAccountContainer';
import EditAccountContainer from '../Form/EditAccountContainer';
import AccountForm from '../Form/AccountForm';

@connect(state => ({
  accounts: allAccountsSelector(state),
  loaded:   isAccountsLoadedSelector(state)
}))
class AccountListContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    loaded:   PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened:  false,
      editAccount: null
    };
  }

  componentDidMount() {
    this.props.dispatch(loadAccounts());
  }

  onNewAccount = () => {
    this.setState({
      formOpened: true
    });
  };

  onEditAccount = (account) => {
    this.setState({
      formOpened:  true,
      editAccount: account
    });
  };

  onClose = () => {
    this.setState({
      formOpened:  false,
      editAccount: null
    });
  };

  render() {
    const { loaded } = this.props;
    const FormContainer = this.state.editAccount ? EditAccountContainer : NewAccountContainer;
    const title = this.state.editAccount ? 'Edit account' : 'New account';

    if (!loaded) {
      return <LoadingPage />;
    }

    return (
      <div>
        <AccountList {...this.props} onNewAccount={this.onNewAccount} onEditAccount={this.onEditAccount} />
        <Modal isOpen={this.state.formOpened} onClose={this.onClose} title={title}>
          <FormContainer account={this.state.editAccount} onClose={this.onClose}>
            <AccountForm />
          </FormContainer>
        </Modal>
      </div>
    );
  }
}

export default AccountListContainer;
