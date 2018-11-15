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
import { settingsLoadedSelector, settingsSelector } from '../../../Selectors/settings';
import { loadSettings, updateSettings } from '../../../Actions/settingActions';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  settings:       settingsSelector(state),
  settingsLoaded: settingsLoadedSelector(state)
}))
class AccountListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accountsLoaded: PropTypes.bool,
    settingsLoaded: PropTypes.bool,
  };

  constructor(props) {
    super(props);
    this.state = {
      formOpened:  false,
      editAccount: null,
      accountType: null
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadSettings());
  }

  onNewAccountClick = (accountType) => {
    this.setState({
      formOpened: true,
      accountType
    });
  };

  onEditAccountClick = (account) => {
    this.setState({
      formOpened:  true,
      editAccount: account,
      accountType: account.get('type')
    });
  };

  onCloseClick = () => {
    this.setState({
      formOpened:  false,
      editAccount: null,
      accountType: null
    });
  };

  saveSettings = data => this.props.dispatch(updateSettings(data));

  render() {
    const { accountsLoaded, settingsLoaded } = this.props;
    const { editAccount, accountType, formOpened } = this.state;
    const FormContainer = editAccount ? EditAccountContainer : NewAccountContainer;
    const title = editAccount ? 'Edit account' : 'New account';

    if (!accountsLoaded || !settingsLoaded) {
      return <LoadingPage />;
    }

    return (
      <div>
        <AccountList
          {...this.props}
          onNewAccount={this.onNewAccountClick}
          onEditAccount={this.onEditAccountClick}
          saveSettings={this.saveSettings}
        />
        <Modal isOpen={formOpened} onClose={this.onClose} title={title}>
          <FormContainer account={editAccount} accountType={accountType} onClose={this.onCloseClick}>
            <AccountForm />
          </FormContainer>
        </Modal>
      </div>
    );
  }
}

export default AccountListContainer;
