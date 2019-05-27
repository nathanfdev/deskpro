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
import { loadNumbers } from '../../../Actions/numberActions';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  settings:       settingsSelector(state),
  settingsLoaded: settingsLoadedSelector(state),
  numbers:        allNumbersSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state),
}))
class AccountListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
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
    dispatch(loadNumbers());
  }

  createNewAccount = (accountType) => {
    this.setState({
      formOpened: true,
      accountType
    });
  };

  editAccount = (account) => {
    this.setState({
      formOpened:  true,
      editAccount: account,
      accountType: account.get('type')
    });
  };

  closeEditPopup = () => {
    this.setState({
      formOpened:  false,
      editAccount: null,
      accountType: null
    });
  };

  saveSettings = data => this.props.dispatch(updateSettings(data));

  render() {
    const { accountsLoaded, settingsLoaded, numbersLoaded } = this.props;
    const { editAccount, accountType, formOpened } = this.state;
    const FormContainer = editAccount ? EditAccountContainer : NewAccountContainer;
    const title = editAccount ? 'Edit account' : 'New account';

    if (!accountsLoaded || !settingsLoaded || !numbersLoaded) {
      return <LoadingPage />;
    }

    return (
      <div>
        <AccountList
          {...this.props}
          createNewAccount={this.createNewAccount}
          editAccount={this.editAccount}
          saveSettings={this.saveSettings}
        />
        <Modal isOpen={formOpened} onClose={this.onClose} title={title} onCloseButtonClick={this.closeEditPopup}>
          <FormContainer account={editAccount} accountType={accountType} onClose={this.closeEditPopup}>
            <AccountForm />
          </FormContainer>
        </Modal>
      </div>
    );
  }
}

export default AccountListContainer;
