import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

export class AgentsList extends React.Component {

  static propTypes = {
    selected: PropTypes.array,
    agents: PropTypes.object,
    onChange: PropTypes.func.isRequired
  };

  static renderLabel(agent) {
    return (
      <span>
          <span style={{position: 'relative'}}>
            <PersonAvatar person={agent} size="16" />
          </span>
          {agent.get('name')}
      </span>
    );
  }

  render() {
    const { agents = [], selected, onChange } = this.props;
    const options = agents.map(agent => ({
      label: AgentsList.renderLabel(agent),
      value: agent.get('id')
    }));

    return (
      <CheckboxList options={options}
                    selected={selected}
                    onChange={onChange} />
    );
  }
}
