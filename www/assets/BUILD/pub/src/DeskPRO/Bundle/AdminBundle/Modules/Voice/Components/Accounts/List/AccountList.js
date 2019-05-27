import PropTypes from 'prop-types';
import React from 'react';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import GeneralSettingsForm from './GeneralSettingsForm';

class AccountList extends React.Component {

  static propTypes = {
    accounts:         PropTypes.object,
    numbers:          PropTypes.object,
    settings:         PropTypes.object,
    createNewAccount: PropTypes.func,
    editAccount:      PropTypes.func,
    saveSettings:     PropTypes.func,
  };

  renderEmpty() {
    const { createNewAccount } = this.props;

    if (window.DP_IS_CLOUD) {
      return (
        <div className="page">
          <SectionHeader title="General Settings" dividing />

          Voice has not been enabled on your account yet.
          <br /><br />

          <button className="ui primary button" onClick={() => createNewAccount('cloud')}>
            Enable Voice
          </button>
        </div>
      );
    }

    return (
      <div className="page">
        <SectionHeader title="General Settings" dividing />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={() => createNewAccount('twilio')}>
          Add new account
        </button>
      </div>
    );
  }

  renderTable() {
    const { accounts = [], numbers, editAccount, settings, saveSettings } = this.props;

    return (
      <div className="page">
        {/* disabled for now because we can just support only one account at the moment
        <button className="ui right floated basic button" onClick={createNewAccount} disabled="disabled">
          <i className="icon plus" />
          Add new account
        </button>*/}
        <SectionHeader title="General Settings" />

        {/* disable list on cloud -- we manage it */}
        {!window.DP_IS_CLOUD && (
          <div className="admin-list-table">
            <div className="row header">
              <div className="column account-name">Name/Note</div>
              <div className="column sid">Account SID</div>
              <div className="column date">Date Added</div>
            </div>
            {accounts.toArray().map((account, index) =>
              <div className="row" key={index}>
                <div className="info">
                  <div className="column account-name">{account.get('account_name')}</div>
                  <div className="column sid">{account.get('account_id')}</div>
                  <div className="column date">{account.get('date_created')}</div>
                  <div className="column options-button">
                    <a onClick={(event) => { event.preventDefault(); editAccount(account); }}>
                      <i className="fas fa-cog" />
                    </a>
                  </div>
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
