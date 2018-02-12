import PropTypes from 'prop-types';
import React from 'react';
import * as actions from '../../Actions/chatsActions';
import { connect } from 'react-redux';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import TimeAgo from '@deskpro/react-timeago';

@connect()
export class AgentsListItem extends React.Component {
  static propTypes = {
    agent:     PropTypes.object.isRequired,
    dispatch:  PropTypes.func.isRequired,
    highlight: PropTypes.oneOfType([PropTypes.string, PropTypes.bool]).isRequired
  };

  startChat = () => {
    this.props.dispatch(actions.startChat(this.props.agent.get('id'), 'agent'));
  };

  render() {
    let name = this.props.agent.get('name');
    if (this.props.highlight) {
      const escape = this.props.highlight.replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&');
      const tagStr = '<span class="search-matched-word">$&</span>';
      name = name.replace(
        new RegExp(escape, 'gi'),
        tagStr
      );
    }

    return (
      <li>
        <a href="#" onClick={this.startChat}>
          <PersonAvatar person={this.props.agent} size={22} />
          <span className="agent">
            <span dangerouslySetInnerHTML={{ __html: name }} />
            <span className="datestamp">
              {this.props.agent.get('last_seen') ? <TimeAgo date={this.props.agent.get('last_seen')} /> : 'never'}
            </span>
          </span>
        </a>
      </li>
    );
  }


}
