import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class DepartmentsList extends React.Component {

  static propTypes = {
    me:          PropTypes.object.isRequired,
    agents:      PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  static getAvatar(department) {
    return <DepartmentAvatar department={department} size={24} classes={['ui avatar image im']} />;
  }

  getItem(department) {
    const classes = ['im', 'department'];

    return (
      <ListElement key={department.get('id')} classes={classes} imageNode={DepartmentsList.getAvatar(department)}>
        <div className="content department">
          <div className="name">
            {department.get('title')}
            <span className="agents-counter">({department.get('agents').length - 1})</span>
            <span className="agents-list">{this.getAgents(department)}</span>
          </div>
        </div>
      </ListElement>
    );
  }

  getAgents(department) {
    return department.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const classes = ['ui avatar image im'];
        const agent = this.props.agents.get(`${agentId}`);
        if (!agent.get('online')) {
          classes.push('offline');
        }

        return (<PersonAvatar
          person={agent}
          size={12}
          classes={classes}
          color={chooseColor(agent)}
        />);
      }
    );
  }

  getItems() {
    return this.props.departments.map(department => this.getItem(department));
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
  }
}

export default DepartmentsList;
