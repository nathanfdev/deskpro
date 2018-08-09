import PropTypes from 'prop-types';
import React from 'react';
import SectionHeader from '../../../../Common/Components/SectionHeader';

class OAuthClientList extends React.Component {

  static propTypes = {
    oauthClients: PropTypes.object,
    addClient:    PropTypes.func,
    editClient:   PropTypes.func
  };

  render() {
    const { oauthClients, addClient, editClient } = this.props;
    const customClients = oauthClients.filter(client => !client.get('sys_name'));
    const builtInClients = oauthClients.filter(client => client.get('sys_name'));

    return (
      <div className="page">
        <SectionHeader title="Apps: OAuth" dividing />

        <button className="ui right floated basic button" onClick={addClient}>
          <i className="icon plus" />
          Add client
        </button>

        <h3>Custom oAuth Clients</h3>
        <ListTable oauthClients={customClients} editClient={editClient} />

        {builtInClients.size > 0 &&
          <div>
            <br />
            <h3>Built-In oAuth Clients</h3>
            <ListTable oauthClients={builtInClients} editClient={editClient} />
          </div>}
      </div>
    );
  }
}

class ListTable extends React.Component {

  static propTypes = {
    oauthClients: PropTypes.object,
    editClient:   PropTypes.func
  };

  render() {
    const { oauthClients, editClient } = this.props;

    return (
      <div className="admin-list-table">
        <div className="row header">
          <div className="column name">Client Name</div>
          <div className="column">Authorized redirect URIs</div>
        </div>
        {oauthClients.map((client, index) =>
          <div className="row" key={index}>
            <div className="info">
              <div className="column name">{client.get('name')}</div>
              <div className="column">{client.get('redirect_uris').join(', ')}</div>
              <div className="column options-button">
                <a onClick={(event) => { event.preventDefault(); editClient(client); }}>
                  <i className="fas fa-cog" />
                </a>
              </div>
              <div className="column status-tag">
                {!client.get('is_enabled') && <em className="tag tag-warn">Disabled</em>}
              </div>
            </div>
          </div>
        )}
      </div>
    );
  }
}

export default OAuthClientList;
