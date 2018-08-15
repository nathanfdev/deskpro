import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import GeneralSettingsForm from './GeneralSettingsForm';

class AccountList extends React.Component {

  static propTypes = {
    accounts:      PropTypes.object,
    settings:      PropTypes.object,
    onNewAccount:  PropTypes.func,
    onEditAccount: PropTypes.func,
    saveSettings:  PropTypes.func,
  };

  renderEmpty() {
    const { onNewAccount } = this.props;

    return (
      <div className="page">
        <SectionHeader title="General Settings" dividing />

        You currently have no accounts.
        <br /><br />

        <button className="ui primary button" onClick={onNewAccount}>
          Add new Twilio account
        </button>
      </div>
    );
  }

  renderTable() {
    const { accounts = [], onEditAccount, settings, saveSettings } = this.props;

    return (
      <div className="page">
        {/* disabled for now because we can just support only one account at the moment
        <button className="ui right floated basic button" onClick={onNewAccount} disabled="disabled">
          <i className="icon plus" />
          Add new Twilio account
        </button>*/}
        <SectionHeader title="General Settings" />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column account-name">Name/Note</div>
            <div className="column sid">Account SID</div>
            <div className="column date">Date Added</div>
            <div className="column date">Is synced</div>
          </div>
          {accounts.toArray().map((account, index) =>
            <div className="row" key={index}>
              <div className="info">
                <div className="column account-name">{account.get('account_name')}</div>
                <div className="column sid">{account.get('account_sid')}</div>
                <div className="column date">{account.get('date_created')}</div>
                <div className="column date">
                  <i
                    className={classNames(
                      'icon',
                      account.get('date_sync') === account.get('date_last_sync') ? 'checkmark' : 'wait'
                    )}
                  />
                </div>
                <div className="column options-button">
                  <a onClick={(event) => { event.preventDefault(); onEditAccount(account); }}>
                    <i className="fas fa-cog" />
                  </a>
                </div>
                <div style={{ clear: 'both' }} />
              </div>
            </div>
          )}
        </div>

        <div className="admin-list-options">
          <div className="agent-settings-form">
            <GeneralSettingsForm settings={settings} onSubmit={saveSettings} />
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
