import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';
import { connect } from 'react-redux';
import Modal from 'deskpro-components/lib/Components/Modal';
import Avatar from 'deskpro-components/lib/Components/Avatar';
import Icon from 'deskpro-components/lib/Components/Icon';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import * as actions from '../Actions/snippetsActions';
import { ComparisonModal } from './ComparisonModal';

@connect(state => ({
  me:     meSelector(state),
  agents: agentsSelector(state)
}))
export class ChangeLogModal extends React.Component {
  static propTypes = {
    me:          PropTypes.object.isRequired,
    agents:      PropTypes.object.isRequired,
    snippet:     PropTypes.object,
    translation: PropTypes.object,
    closeModal:  PropTypes.func,
    dispatch:    PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      loading:             true,
      comparisonModalOpen: false,
      changes:             [],
      version:             0,
    };
    this.handleChangeClick = this.handleChangeClick.bind(this);
  }

  componentWillMount() {
    this.props.dispatch(actions.getChangelog(this.props.snippet.get('id')))
      .success(({ data }) => {
        this.setState({
          loading: false,
          changes: data,
        });
      });
  }

  getAvatar(personId) {
    if (!personId) {
      return '';
    }

    const agent = this.props.agents.get(personId);

    let url = agent.getIn(['avatar', 'url_pattern'], false);
    if (!url) {
      url = agent.getIn(['avatar', 'default_url_pattern'], false);
    }
    let name = agent.get('display_name');
    if (agent.get('id') === this.props.me.get('id')) {
      name = agentPhrases.get('agent.general.me');
    }
    return <Avatar src={url.replace(/{{IMG_SIZE}}/, 18)} title={name} />;
  }

  getChanges = () => this.state.changes
    .map((change, key) => {
      const version = this.state.changes.length + 1 - key;
      return (<div key={change.id} className="change" onClick={() => this.handleChangeClick(version)}>
        <Icon name="file-text" size="s" />
          Content change (#{version})
          {this.getAvatar(change.person)}
        <span className="date"><TimeAgo date={change.date_created} /></span>
      </div>);
    }
    );

  getComparisonModal = () => {
    if (!this.state.comparisonModalOpen) {
      return null;
    }
    return (
      <ComparisonModal
        snippet={this.props.snippet}
        changes={this.state.changes}
        version={this.state.version}
        translation={this.props.translation}
        closeModal={this.closeComparisonModal}
      />
    );
  };

  handleChangeClick(version) {
    this.setState({
      version,
      comparisonModalOpen: true,
    });
  }

  closeComparisonModal = () => {
    this.setState({
      comparisonModalOpen: false,
    });
  };

  render() {
    const { snippet, closeModal } = this.props;
    return (
      <div id="change_log_modal">
        <Modal
          title={agentPhrases.get('agent.general.changelog')}
          closeModal={closeModal}
        >
          {this.state.loading ?
            <div className="ui active inverted dimmer">
              <div className="ui text loader">{agentPhrases.get('agent.general.loading_dot')}</div>
            </div>
            : <div>
              {this.getChanges()}
              <div className="change creation">
                <Icon name="file-text" size="s" />
                Snippet created (#1)
                {this.getAvatar(snippet.get('person'))}
                <span className="date"><TimeAgo date={snippet.get('date_created')} /></span>
              </div>
            </div>
          }
        </Modal>
        {this.getComparisonModal()}
      </div>
    );
  }
}
