import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import classNames from 'classnames';

class OnlineAgents extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

  toggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  render() {
    const { onlineAgents } = this.props;
    const { expanded } = this.state;

    return (
      <div className="voice-online-agents">
        <FormattedMessage
          id="agent.tickets.count_agents"
          values={{
            count: onlineAgents.size
          }}
        />
        {onlineAgents.size > 0 &&
          <button className="ui basic button expand-button" onClick={this.toggleExpand}>
            <i className="icon users" />
            {expanded ? 'Collapse' : 'Expand'}
          </button>
        }

        <List className={classNames('agents', { hidden: !expanded })}>
          {onlineAgents.toArray().map((agent) => {
            let img = agent.get('avatar').get('default_url_pattern');
            if (agent.get('avatar').get('url_pattern')) {
              img = agent.get('avatar').get('url_pattern');
            }
            img = img.replace(/\{\{IMG_SIZE}}/, 15);
            return <ListElement key={`agent${String(agent.get('id'))}`} label={agent.get('name')} image={img} />;
          })}
        </List>
      </div>
    );
  }
}

export default OnlineAgents;
