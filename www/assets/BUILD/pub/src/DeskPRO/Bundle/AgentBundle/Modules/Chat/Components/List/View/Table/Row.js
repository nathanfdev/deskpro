import React, { Component, PropTypes } from 'react';
import { Td, TdId, PersonInTable }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';

export class Row extends Component {
  static propTypes = {
    element:    PropTypes.object.isRequired,
    author:     PropTypes.object.isRequired,
    agent:      PropTypes.object.isRequired,
    department: PropTypes.object.isRequired
  };

  render() {
    const { element, author, agent, department } = this.props;

    return (
      <tr>
        <TdId>
          {element.get('id')}
        </TdId>
        <Td>
          <PersonInTable person={author} />
        </Td>
        <Td className="agent-col">
          <div className="agent">
            <span className="dpw--avatar-face" style={{ backgroundImage: 'url(../img/avatars/avatar4.png)' }}></span>
            {agent.get('name')}
          </div>
        </Td>
        <Td />
        <Td className="item-title">{element.get('subject')}</Td>
        <Td>{department.get('title')}</Td>
        <Td />
      </tr>);
  }
}
