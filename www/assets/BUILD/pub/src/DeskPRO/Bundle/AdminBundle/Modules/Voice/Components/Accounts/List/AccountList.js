import PropTypes from 'prop-types';
import React from 'react';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import GeneralSettingsForm from './GeneralSettingsForm';

class AccountList extends React.Component {

  static propTypes = {
    accounts:            PropTypes.object,
    numbers:             PropTypes.object,
    settings:            PropTypes.object,
    openNewAccountForm:  PropTypes.func,
    openEditAccountForm: PropTypes.func,
    saveSettings:        PropTypes.func,
  };

  renderEmpty() {
    const { openNewAccountForm, settings } = this.props;

    return (
      <div className="page">
        <SectionHeader title="General Settings" dividing />

        Voice has not been enabled yet.
        <br /><br />

        <button className="ui primary button" onClick={() => openNewAccountForm('twilio', true)}>
          Begin Setup &rarr;
        </button>

        {settings.get('private_accounts_enabled') &&
          <button className="ui primary button" onClick={() => openNewAccountForm('twilio')}>
            Add your own Twilio account
          </button>
        }
      </div>
    );
  }

  renderTable() {
    const { accounts = [], numbers, openEditAccountForm, settings, saveSettings } = this.props;

    const privateAccounts = [];
    const managedAccounts = [];

    accounts.toArray().forEach((account) => {
      if (account.get('account_id') === '__ACCOUNT_ID__') {
        managedAccounts.push(account);
      } else {
        privateAccounts.push(account);
      }
    });

    return (
      <div className="page">
        {/* disabled for now because we can just support only one account at the moment
        <button className="ui right floated basic button" onClick={createNewAccount} disabled="disabled">
          <i className="icon plus" />
          Add new account
        </button>*/}
        <SectionHeader title="General Settings" />

        { (privateAccounts.length || settings.get('private_accounts_enabled')) && (
          <div className="admin-list-table">
            <div className="row header">
              <div className="column account-name">Name/Note</div>
              <div className="column sid">Account SID</div>
              <div className="column date">Date Added</div>
            </div>
            {privateAccounts.map((account) =>
              <div className="row" key={account.get('id')}>
                <div className="info">
                  <div className="column account-name">{account.get('account_name')}</div>
                  <div className="column sid">{account.get('account_id')}</div>
                  <div className="column date">{account.get('date_created')}</div>
                  <div className="column options-button">
                    <a onClick={(event) => { event.preventDefault(); openEditAccountForm(account); }}>
                      <i className="fas fa-cog" />
                    </a>
                  </div>
                  <div style={{ clear: 'both' }} />
                </div>
              </div>
            )}
            {managedAccounts.map((account) =>
              <div className="row" key={account.get('id')}>
                <div className="info">
                  <div className="column account-name">Managed Account #{account.get('id')}</div>
                  <div className="column sid">-</div>
                  <div className="column date">{account.get('date_created')}</div>
                  <div className="column options-button">-</div>
                  <div style={{ clear: 'both' }} />
                </div>
              </div>
            )}
          </div>
        )}

        <div className="admin-list-options">
          <div className="voice-general-settings-form">
            <GeneralSettingsForm settings={settings} numbers={numbers} onSubmit={saveSettings} />
          </div>
        </div>
      </div>
    );
  }

  render() {
    const { accounts } = this.props;

    return accounts && accounts.size ? this.renderTable() : this.renderEmpty();
  }
}

export default AccountList;
