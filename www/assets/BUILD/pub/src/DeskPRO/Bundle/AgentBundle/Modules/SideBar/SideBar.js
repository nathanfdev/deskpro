import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { SeparateComponent } from '../Common/Components/SeparateComponent';

@connect(state => ({
  me: meSelector(state)
}))
export class SideBarContainer extends SeparateComponent {
  static propTypes = {
    me: PropTypes.object
  };

  static getType() {
    return 'SideBarContainer';
  }

  canUseTicket() {
    return window.DESKPRO_PERSON_PERMS['agent_tickets.use'];
  }

  canUseChat() {
    return window.DESKPRO_PERSON_PERMS['agent_chat.use'] && window.DESKPRO_APP_SETTINGS['core.apps_chat'];
  }

  canUsePeople() {
    return window.DESKPRO_PERSON_PERMS['agent_people.use'];
  }

  canUseFeedback() {
    return window.DESKPRO_APP_SETTINGS['core.apps_feedback'];
  }
  canUsePublish() {
    return window.DESKPRO_APP_SETTINGS['core.apps_kb']
      || window.DESKPRO_APP_SETTINGS['core.apps_news']
      || window.DESKPRO_APP_SETTINGS['core.apps_downloads'];
  }
  canUseTasks() {
    return window.DESKPRO_APP_SETTINGS['core.apps_tasks']
      && window.DESKPRO_PERSON_PERMS['agent_tasks.use'];
  }
  canUseReports() {
    return window.DESKPRO_PERSON_PERMS['agent_reports.use'];
  }
  canUseAdmin() {
    return window.DESKPRO_PERSON_PERMS['agent_admin.use'];
  }
  canUseBilling() {
    return window.DESKPRO_PERSON_PERMS['agent_admin.use'];
  }
  canUsePortal() {
    return true;
  }
  closeIframes() {
    for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
      const iframe = window.DP_FRAME_OVERLAYS[key];
      if (iframe.opened) {
        iframe.close();
      }
    }
  }

  render() {
    const props = {
      hoverMode:      false,
      canUseTicket:   this.canUseTicket(),
      canUseChat:     this.canUseChat(),
      canUsePeople:   this.canUsePeople(),
      canUseFeedback: this.canUseFeedback(),
      canUsePublish:  this.canUsePublish(),
      canUseTasks:    this.canUseTasks(),
      canUseReports:  this.canUseReports(),
      canUseAdmin:    this.canUseAdmin(),
      canUseBilling:  this.canUseBilling(),
      canUsePortal:   this.canUsePortal(),
      closeIframes:   this.closeIframes
    };
    return <SideBar {...this.props} {...props} />;
  }
}

export class SideBar extends React.Component {
  static propTypes = {
    me:             PropTypes.object,
    hoverMode:      PropTypes.bool.isRequired,
    canUseTicket:   PropTypes.bool.isRequired,
    canUseChat:     PropTypes.bool.isRequired,
    canUsePeople:   PropTypes.bool.isRequired,
    canUseFeedback: PropTypes.bool.isRequired,
    canUsePublish:  PropTypes.bool.isRequired,
    canUseTasks:    PropTypes.bool.isRequired,
    canUseReports:  PropTypes.bool.isRequired,
    canUseAdmin:    PropTypes.bool.isRequired,
    canUseBilling:  PropTypes.bool.isRequired,
    canUsePortal:   PropTypes.bool.isRequired,
    closeIframes:   PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      activeMenu: 'menu_tickets'
    };
  }

  getMenus = () => {
    const menus = [];
    if (this.props.canUseTicket) {
      menus.push({
        className: 'tickets',
        label:     'Tickets',
        icon:      'tickets.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tickets_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseChat) {
      menus.push({
        className: 'chats',
        label:     'Chats',
        icon:      'chat.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('chat_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUsePeople) {
      menus.push({
        className: 'crm',
        label:     'CRM',
        icon:      'crm.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('people_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseFeedback) {
      menus.push({
        className: 'feedback',
        label:     'Feedback',
        icon:      'feedback.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('feedback_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUsePublish) {
      menus.push({
        className: 'publish',
        label:     'Publish',
        icon:      'publishing.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('publish_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseTasks) {
      menus.push({
        className: 'tasks',
        label:     'Tasks',
        icon:      'tasks.svg',
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tasks_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseReports) {
      menus.push({
        className: 'reports',
        label:     'Reports',
        icon:      'reports.svg'
      });
    }
    if (this.props.canUseAdmin) {
      menus.push({
        className: 'admin',
        label:     'Admin',
        icon:      'settings.svg'
      });
    }
    if (this.props.canUseBilling) {
      menus.push({
        className: 'billing',
        label:     'Billing',
        icon:      'billing.svg'
      });
    }
    if (this.props.canUsePortal) {
      menus.push({
        className: 'portal',
        label:     'Portal',
        icon:      'portal.svg'
      });
    }
    return menus;
  };

  getMenuItems = () => {
    const menus = [];
    const { activeMenu } = this.state;
    const svgSrc = window.DESKPRO_APP_ASSETS_URL.replace(/\/$/, '');
    this.getMenus().map((item) => {
      const menuItem = item;
      const title = this.props.hoverMode ? '' : menuItem.label;
      menuItem.key = `menu_${menuItem.className}`;
      menus.push(
        <MenuItem
          key={menuItem.key}
          classes={classNames(menuItem.className, { active: activeMenu === menuItem.key })}
          onClick={() => this.clickMenu(menuItem)}
        >
          <span className="menu-icon" title={title}>
            <Isvg src={`${svgSrc}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/${menuItem.icon}`} />
          </span>
          <span className="menu-label">{menuItem.label}</span>
        </MenuItem>
      );
      return true;
    });
    return menus;
  };

  clickMenu = (item) => {
    this.setState({
      activeMenu: item.key
    });
    if (item.callback) {
      item.callback();
    }
  };

  render() {
    const { hoverMode } = this.props;
    return (
      <div
        className={classNames('sidebar-menu', 'ui', 'vertical', 'menu', { 'hover-mode': hoverMode })}
      >
        {this.getMenuItems()}
      </div>
    );
  }
}
export default SideBarContainer;
