import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/sideBarActions';
import * as onboardingActions from '../../Onboarding/Actions/onboardingActions';

@connect(state => ({
  currentSection: state.SideBar.sections.get('current'),
  logoCallback:   state.Onboarding.onboarding.get('logoCallback'),
  logoActive:     state.Onboarding.onboarding.get('logoActive')
}))
export class SideBarContainer extends SeparateComponent {
  static propTypes = {
    currentSection: PropTypes.string.isRequired,
    logoCallback:   PropTypes.func,
    logoActive:     PropTypes.bool,
    dispatch:       PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      sectionsBadges: []
    };
    window.document.addEventListener('dpUpdateSideBarBadge', (e) => {
      const sectionsBadges = this.state.sectionsBadges;
      if (e.detail.count > 0) {
        if (sectionsBadges.indexOf(e.detail.sectionId) === -1) {
          sectionsBadges.push(e.detail.sectionId);
          this.setState({
            sectionsBadges
          });
        }
      } else {
        const index = sectionsBadges.indexOf(e.detail.sectionId);
        if (index > -1) {
          sectionsBadges.splice(index, 1);
          this.setState({
            sectionsBadges
          });
        }
      }
    });
    window.document.addEventListener('dpOpenOverlayFrame', (e) => {
      if (e.detail.id === 'reports') {
        this.changeSection('menu_reports');
      } else if (e.detail.id === 'admin') {
        if (e.detail.path === '/license') {
          this.changeSection('menu_billing');
        } else {
          this.changeSection('menu_admin');
        }
      }
    });
  }

  componentWillMount = () => {
    if (window.DP_FRAME_OVERLAYS) {
      if (window.DP_FRAME_OVERLAYS.reports.opened) {
        this.changeSection('menu_reports');
      }
      if (window.DP_FRAME_OVERLAYS.admin.opened) {
        if (window.DP_FRAME_OVERLAYS.admin.frame[0].baseURI.match(/#admin:\/license$/)) {
          this.changeSection('menu_billing');
        } else {
          this.changeSection('menu_admin');
        }
      }
    }
  };

  static getType() {
    return 'SideBarContainer';
  }

  static canUseTicket() {
    return window.DESKPRO_PERSON_PERMS['agent_tickets.use'];
  }

  static canUseChat() {
    return window.DESKPRO_PERSON_PERMS['agent_chat.use'] && window.DESKPRO_APP_SETTINGS['core.apps_chat'];
  }

  static canUsePeople() {
    return window.DESKPRO_PERSON_PERMS['agent_people.use'];
  }

  static canUseFeedback() {
    return window.DESKPRO_APP_SETTINGS['core.apps_feedback'];
  }

  static canUsePublish() {
    return window.DESKPRO_APP_SETTINGS['core.apps_kb']
      || window.DESKPRO_APP_SETTINGS['core.apps_news']
      || window.DESKPRO_APP_SETTINGS['core.apps_downloads'];
  }

  static canUseTasks() {
    return window.DESKPRO_APP_SETTINGS['core.apps_tasks']
      && window.DESKPRO_PERSON_PERMS['agent_tasks.use'];
  }

  static canUseReports() {
    return window.DESKPRO_PERSON_PERMS['agent_reports.use'];
  }

  static canUseAdmin() {
    return window.DESKPRO_PERSON_PERMS['agent_admin.use'];
  }

  static canUseBilling() {
    return window.DESKPRO_PERSON_PERMS['agent_admin.use'];
  }

  static canUsePortal() {
    return true;
  }

  static closeIframes() {
    for (const key of Object.keys(window.DP_FRAME_OVERLAYS)) {
      const iframe = window.DP_FRAME_OVERLAYS[key];
      if (iframe.opened) {
        iframe.close();
      }
    }
  }

  openAdmin = () => {
    SideBarContainer.closeIframes();
    window.DP_FRAME_OVERLAYS.admin.open();
  };

  openReports = () => {
    SideBarContainer.closeIframes();
    window.DP_FRAME_OVERLAYS.reports.open();
  };

  openBilling = () => {
    SideBarContainer.closeIframes();
    window.DP_FRAME_OVERLAYS.admin.open('/license');
  };

  changeSection = (section) => {
    this.props.dispatch(actions.changeSection({ section }));
  };

  resumeOnboarding = () => {
    this.props.dispatch(onboardingActions.resumeOnboarding());
  };

  render() {
    const props = {
      canUseTicket:     SideBarContainer.canUseTicket,
      canUseChat:       SideBarContainer.canUseChat,
      canUsePeople:     SideBarContainer.canUsePeople,
      canUseFeedback:   SideBarContainer.canUseFeedback,
      canUsePublish:    SideBarContainer.canUsePublish,
      canUseTasks:      SideBarContainer.canUseTasks,
      canUseReports:    SideBarContainer.canUseReports,
      canUseAdmin:      SideBarContainer.canUseAdmin,
      canUseBilling:    SideBarContainer.canUseBilling,
      canUsePortal:     SideBarContainer.canUsePortal,
      closeIframes:     SideBarContainer.closeIframes,
      openAdmin:        this.openAdmin,
      openReports:      this.openReports,
      openBilling:      this.openBilling,
      changeSection:    this.changeSection,
      resumeOnboarding: this.resumeOnboarding,
      sectionsBadges:   this.state.sectionsBadges
    };
    return <SideBar {...this.props} {...props} />;
  }
}

export class SideBar extends React.Component {
  static propTypes = {
    canUseTicket:     PropTypes.func.isRequired,
    canUseChat:       PropTypes.func.isRequired,
    canUsePeople:     PropTypes.func.isRequired,
    canUseFeedback:   PropTypes.func.isRequired,
    canUsePublish:    PropTypes.func.isRequired,
    canUseTasks:      PropTypes.func.isRequired,
    canUseReports:    PropTypes.func.isRequired,
    canUseAdmin:      PropTypes.func.isRequired,
    canUseBilling:    PropTypes.func.isRequired,
    canUsePortal:     PropTypes.func.isRequired,
    closeIframes:     PropTypes.func.isRequired,
    openAdmin:        PropTypes.func.isRequired,
    openReports:      PropTypes.func.isRequired,
    openBilling:      PropTypes.func.isRequired,
    changeSection:    PropTypes.func.isRequired,
    currentSection:   PropTypes.string.isRequired,
    logoCallback:     PropTypes.func,
    logoActive:       PropTypes.bool,
    resumeOnboarding: PropTypes.func,
    sectionsBadges:   PropTypes.array
  };

  static openDeskPro() {
    window.open('http://deskpro.com', '_blank');
  }

  getMenus = () => {
    const menus = [];
    if (this.props.canUseTicket()) {
      menus.push({
        className: 'tickets',
        label:     'Tickets',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tickets.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tickets_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseChat()) {
      menus.push({
        className: 'chats',
        label:     'Chats',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/chat.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('chat_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUsePeople()) {
      menus.push({
        className: 'crm',
        label:     'CRM',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/crm.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('people_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseFeedback()) {
      menus.push({
        className: 'feedback',
        label:     'Feedback',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/feedback.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('feedback_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUsePublish()) {
      menus.push({
        className: 'publish',
        label:     'Publish',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/publishing.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('publish_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseTasks()) {
      menus.push({
        className: 'tasks',
        label:     'Tasks',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tasks.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tasks_section');
          this.props.closeIframes();
        }
      });
    }
    if (this.props.canUseReports()) {
      menus.push({
        className: 'reports',
        label:     'Reports',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/reports.svg`,
        callback:  () => {
          this.props.openReports();
        }
      });
    }
    if (this.props.canUseAdmin()) {
      menus.push({
        className: 'admin',
        label:     'Admin',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/settings.svg`,
        callback:  () => {
          this.props.openAdmin();
        }
      });
    }
    if (this.props.canUseBilling()) {
      menus.push({
        className: 'billing',
        label:     'Billing',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/billing.svg`,
        callback:  () => {
          this.props.openBilling();
        }
      });
    }
    if (this.props.canUsePortal()) {
      menus.push({
        className: 'portal',
        label:     'Portal',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/portal.svg`,
        href:      window.DESKPRO_PORTAL_HOME
      });
    }
    return menus;
  };

  getMenuItems = () => {
    const menus = [];
    const { currentSection, sectionsBadges } = this.props;
    this.getMenus().map((item) => {
      const menuItem = item;
      menuItem.key = `menu_${menuItem.className}`;
      const badge = sectionsBadges.indexOf(`${menuItem.className}_section`) > -1;
      menus.push(
        <MenuItem
          key={menuItem.key}
          className={classNames(menuItem.className, { active: currentSection === menuItem.key, badge })}
          onClick={() => this.clickMenu(menuItem)}
        >
          <span className="menu-icon">
            <Isvg src={menuItem.icon} />
          </span>
          <span className="menu-label">{menuItem.label}</span>
        </MenuItem>
      );
      return true;
    });
    return menus;
  };

  clickMenu = (item) => {
    if (item.callback) {
      this.props.changeSection(item.key);
      item.callback();
    } else if (item.href) {
      window.open(item.href, '_blank');
    }
  };

  clickLogo = () => {
    if (this.props.logoActive) {
      this.props.logoCallback();
      this.props.resumeOnboarding();
    } else {
      SideBar.openDeskPro();
    }
  };

  render() {
    const { logoActive } = this.props;
    return (
      <div
        className={classNames('sidebar-menu', 'ui', 'vertical', 'menu')}
      >
        <div className={classNames('logo', { active: logoActive })} onClick={this.clickLogo}>
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/logo.svg`} />
        </div>
        {this.getMenuItems()}
      </div>
    );
  }
}
export default SideBarContainer;
