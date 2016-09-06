import React, { PropTypes } from 'react';
import Isvg from 'react-inlinesvg';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import ticketsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tickets.svg';
import chatSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/chat.svg';
import crmSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/crm.svg';
import feedbackSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/feedback.svg';
import publishingSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/publishing.svg';
import tasksSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tasks.svg';
import reportsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/reports.svg';
import settingsSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/settings.svg';
import billingSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/billing.svg';
import portalSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/portal.svg';
import logoSvg from 'DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/logo.svg';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/sideBarActions';
import * as onboardingActions from '../../Onboarding/Actions/onboardingActions';

@connect(state => ({
  me:             meSelector(state),
  currentSection: state.SideBar.sections.get('current'),
  logoCallback:   state.Onboarding.onboarding.get('logoCallback'),
  logoActive:     state.Onboarding.onboarding.get('logoActive')
}))
export class SideBarContainer extends SeparateComponent {
  static propTypes = {
    me:             PropTypes.object.isRequired,
    currentSection: PropTypes.string.isRequired,
    logoCallback:   PropTypes.func,
    logoActive:     PropTypes.bool,
    dispatch:       PropTypes.func.isRequired
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

  openAdmin = () => {
    this.closeIframes();
    window.DP_FRAME_OVERLAYS.admin.open();
  };

  openReports = () => {
    this.closeIframes();
    window.DP_FRAME_OVERLAYS.reports.open();
  };

  openBilling = () => {
    this.closeIframes();
    window.DP_FRAME_OVERLAYS.billing.open();
  };

  changeSection = (section) => {
    this.props.dispatch(actions.changeSection({ section }));
  };

  resumeOnboarding = () => {
    this.props.dispatch(onboardingActions.resumeOnboarding());
  };

  render() {
    const props = {
      canUseTicket:     this.canUseTicket(),
      canUseChat:       this.canUseChat(),
      canUsePeople:     this.canUsePeople(),
      canUseFeedback:   this.canUseFeedback(),
      canUsePublish:    this.canUsePublish(),
      canUseTasks:      this.canUseTasks(),
      canUseReports:    this.canUseReports(),
      canUseAdmin:      this.canUseAdmin(),
      canUseBilling:    this.canUseBilling(),
      canUsePortal:     this.canUsePortal(),
      closeIframes:     this.closeIframes,
      openAdmin:        this.openAdmin,
      openReports:      this.openReports,
      openBilling:      this.openBilling,
      changeSection:    this.changeSection,
      resumeOnboarding: this.resumeOnboarding
    };
    return <SideBar {...this.props} {...props} />;
  }
}

export class SideBar extends React.Component {
  static propTypes = {
    me:               PropTypes.object,
    canUseTicket:     PropTypes.bool.isRequired,
    canUseChat:       PropTypes.bool.isRequired,
    canUsePeople:     PropTypes.bool.isRequired,
    canUseFeedback:   PropTypes.bool.isRequired,
    canUsePublish:    PropTypes.bool.isRequired,
    canUseTasks:      PropTypes.bool.isRequired,
    canUseReports:    PropTypes.bool.isRequired,
    canUseAdmin:      PropTypes.bool.isRequired,
    canUseBilling:    PropTypes.bool.isRequired,
    canUsePortal:     PropTypes.bool.isRequired,
    closeIframes:     PropTypes.func.isRequired,
    openAdmin:        PropTypes.func.isRequired,
    openReports:      PropTypes.func.isRequired,
    openBilling:      PropTypes.func.isRequired,
    changeSection:    PropTypes.func.isRequired,
    currentSection:   PropTypes.string.isRequired,
    logoCallback:     PropTypes.func,
    logoActive:       PropTypes.bool,
    resumeOnboarding: PropTypes.func
  };

  getMenus = () => {
    const menus = [];
    if (this.props.canUseTicket) {
      menus.push({
        className: 'tickets',
        label:     'Tickets',
        icon:      ticketsSvg,
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
        icon:      chatSvg,
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
        icon:      crmSvg,
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
        icon:      feedbackSvg,
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
        icon:      publishingSvg,
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
        icon:      tasksSvg,
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
        icon:      reportsSvg,
        callback:  () => {
          this.props.openReports();
        }
      });
    }
    if (this.props.canUseAdmin) {
      menus.push({
        className: 'admin',
        label:     'Admin',
        icon:      settingsSvg,
        callback:  () => {
          this.props.openAdmin();
        }
      });
    }
    if (this.props.canUseBilling) {
      menus.push({
        className: 'billing',
        label:     'Billing',
        icon:      billingSvg,
        callback:  () => {
          this.props.openBilling();
        }
      });
    }
    if (this.props.canUsePortal) {
      menus.push({
        className: 'portal',
        label:     'Portal',
        icon:      portalSvg,
        href:      window.DESKPRO_PORTAL_HOME
      });
    }
    return menus;
  };

  getMenuItems = () => {
    const menus = [];
    const currentSection = this.props.currentSection;
    this.getMenus().map((item) => {
      const menuItem = item;
      menuItem.key = `menu_${menuItem.className}`;
      menus.push(
        <MenuItem
          key={menuItem.key}
          classes={classNames(menuItem.className, { active: currentSection === menuItem.key })}
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
      this.openDeskPro();
    }
  };

  openDeskPro() {
    window.open('http://deskpro.com', '_blank');
  }

  render() {
    const { logoActive } = this.props;
    return (
      <div
        className={classNames('sidebar-menu', 'ui', 'vertical', 'menu')}
      >
        <div className={classNames('logo', { active: logoActive })} onClick={this.clickLogo}>
          <Isvg src={logoSvg} />
        </div>
        {this.getMenuItems()}
      </div>
    );
  }
}
export default SideBarContainer;
