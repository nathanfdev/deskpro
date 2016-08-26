import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';
import { Header } from 'DeskPRO/Component/Semantic/Common';

class DepartmentsList extends React.Component {

  static propTypes = {
    agents:      PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  static getAvatar(department) {
    return <DepartmentAvatar department={department} size={24} classes={['ui avatar image im']} />;
  }

  static getItem(department) {
    const classes = ['im', 'department'];

    return (
      <ListElement  key={department.get('id')} classes={classes} imageNode={DepartmentsList.getAvatar(department)}>
        <div className="content department">
          <span className="name">{department.get('title')}</span>
          <span className="ui knuckles label message-counter">3</span>
        </div>
      </ListElement>
    );
  }

  getItems() {
    return this.props.departments.map(department => DepartmentsList.getItem(department));
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
      
  }
}

export default DepartmentsList;