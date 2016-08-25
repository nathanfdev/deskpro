import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { SeparateComponent } from '../Common/Components/SeparateComponent';

@connect(state => ({
  me: meSelector(state)
}))
export class SideBarContainer extends SeparateComponent {
  static propTypes = {
    me: PropTypes.object
  };

  render() {
    return <SideBar {...this.props} />;
  }
}

export class SideBar extends React.Component {
  static propTypes = {
    me: PropTypes.object
  };

  static menus = [
    {
      label: 'Tickets',
      icon:  'tickets.svg'
    },
    {
      label: 'Chats',
      icon:  'chat.svg'
    },
    {
      label: 'CRM',
      icon:  'crm.svg'
    },
    {
      label: 'Feedback',
      icon:  'feedback.svg'
    },
    {
      label: 'Publish',
      icon:  'publishing.svg'
    },
    {
      label: 'Tasks',
      icon:  'tasks.svg'
    },
    {
      label: 'Reports',
      icon:  'reports.svg'
    },
    {
      label: 'Admin',
      icon:  'settings.svg'
    },
    {
      label: 'Billing',
      icon:  'billing.svg'
    },
    {
      label: 'Portal',
      icon:  'portal.svg'
    },
  ];

  getMenuItems = () => {
    const menus = [];
    const svgSrc = window.DESKPRO_APP_ASSETS_URL.replace(/\/$/, '');
    this.constructor.menus.map((item, index) => {
      menus.push(<MenuItem key={`menu${index}`}>
        <Isvg src={`${svgSrc}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/${item.icon}`} />
        {item.label}
      </MenuItem>);
      return true;
    });
    return menus;
  };

  render() {
    return (<div className="sidebar vertical ui menu">
      <Menu>
        {this.getMenuItems()}
      </Menu>
    </div>);
  }
}
export default SideBarContainer;
