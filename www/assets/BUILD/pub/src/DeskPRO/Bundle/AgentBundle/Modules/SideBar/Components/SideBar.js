import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as actions from '../Actions/sideBarActions';
import * as onboardingActions from '../../Onboarding/Actions/onboardingActions';
import { closeIframes } from '../../Application/Actions/bootstrapActions';

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
  }

  componentWillMount = () => {
    if (window.IS_BILLING_ERROR) {
      this.state.sectionsBadges.push('billing_section');
    }
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
      } else if (e.detail.id === 'reports-interface') {
        this.changeSection('menu_reports2');
      } else if (e.detail.id === 'admin') {
        if (e.detail.path === '/license') {
          this.changeSection('menu_billing');
        } else {
          this.changeSection('menu_admin');
        }
      }
    });
    window.document.addEventListener('dpChangeSection', () => {
      this.changeSection();
    });
    window.document.addEventListener('dpCloseOverlayFrame', () => {
      this.changeSection();
    });
    window.document.addEventListener('dpHashChange', (e) => {
      closeIframes();
      setTimeout(() => {
        window.DeskPRO_Window.loadHashPath(e.detail.hash);
      }, 5);
    });

    // set current section on mount
    this.changeSection();
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
    return window.DESKPRO_PERSON_PERMS['agent_publish.use'] && window.DESKPRO_PERSON_PERMS['agent_publish.validate'];
  }

  static canUsePublish() {
    return window.DESKPRO_PERSON_PERMS['agent_publish.use'];
  }

  static canUseTasks() {
    return window.DESKPRO_PERSON_PERMS['agent_tasks.use'];
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

  static hasAccessToDashboards() {
    return window.DESKPRO_PERSON_PERMS['agent_reports.has_dashboards'];
  }

  openAdmin = () => {
    closeIframes();
    window.DP_FRAME_OVERLAYS.admin.open();
  };

  openReports = () => {
    closeIframes();
    window.DP_FRAME_OVERLAYS.reports.open();
  };

  openReports2 = () => {
    closeIframes();
    window.DP_FRAME_OVERLAYS['reports-interface'].open();
  };

  openBilling = () => {
    closeIframes();
    window.DP_FRAME_OVERLAYS.admin.open('/license');
  };

  changeSection = () => {
    const { dispatch } = this.props;
    const reportsFrame = window.DP_FRAME_OVERLAYS && window.DP_FRAME_OVERLAYS.reports;
    const reports2Frame = window.DP_FRAME_OVERLAYS && window.DP_FRAME_OVERLAYS['reports-interface'];
    const adminFrame = window.DP_FRAME_OVERLAYS && window.DP_FRAME_OVERLAYS.admin;

    try {
      if (reportsFrame && reportsFrame.opened) {
        dispatch(actions.changeSection({ section: 'menu_reports' }));
      } else if (reports2Frame && reports2Frame.opened) {
        dispatch(actions.changeSection({ section: 'menu_reports2' }));
      } else if (adminFrame && adminFrame.opened) {
        if (adminFrame.getFrameWindow().location.hash === '#/license') {
          dispatch(actions.changeSection({ section: 'menu_billing' }));
        } else {
          dispatch(actions.changeSection({ section: 'menu_admin' }));
        }
      } else {
        const section = window.DeskPRO_Window.getOpenSection();
        if (section && section.section_id) {
          const sectionId = section.section_id.replace(/_section/, '');

          dispatch(actions.changeSection({ section: `menu_${sectionId}` }));
        }
      }
    } catch (e) {
      console.log(e);
    }
  };

  resumeOnboarding = () => {
    this.props.dispatch(onboardingActions.resumeOnboarding());
  };

  render() {
    const props = {
      canUseTicket:          SideBarContainer.canUseTicket,
      canUseChat:            SideBarContainer.canUseChat,
      canUsePeople:          SideBarContainer.canUsePeople,
      canUseFeedback:        SideBarContainer.canUseFeedback,
      canUsePublish:         SideBarContainer.canUsePublish,
      canUseTasks:           SideBarContainer.canUseTasks,
      canUseReports:         SideBarContainer.canUseReports,
      canUseAdmin:           SideBarContainer.canUseAdmin,
      canUseBilling:         SideBarContainer.canUseBilling,
      canUsePortal:          SideBarContainer.canUsePortal,
      hasAccessToDashboards: SideBarContainer.hasAccessToDashboards,
      openAdmin:             this.openAdmin,
      openReports:           this.openReports,
      openReports2:          this.openReports2,
      openBilling:           this.openBilling,
      changeSection:         this.changeSection,
      resumeOnboarding:      this.resumeOnboarding,
      sectionsBadges:        this.state.sectionsBadges
    };
    return <SideBar {...this.props} {...props} />;
  }
}

export class SideBar extends React.PureComponent {
  static propTypes = {
    canUseTicket:          PropTypes.func.isRequired,
    canUseChat:            PropTypes.func.isRequired,
    canUsePeople:          PropTypes.func.isRequired,
    canUseFeedback:        PropTypes.func.isRequired,
    canUsePublish:         PropTypes.func.isRequired,
    canUseTasks:           PropTypes.func.isRequired,
    canUseReports:         PropTypes.func.isRequired,
    hasAccessToDashboards: PropTypes.func.isRequired,
    canUseAdmin:           PropTypes.func.isRequired,
    canUseBilling:         PropTypes.func.isRequired,
    canUsePortal:          PropTypes.func.isRequired,
    openAdmin:             PropTypes.func.isRequired,
    openReports:           PropTypes.func.isRequired,
    openReports2:          PropTypes.func.isRequired,
    openBilling:           PropTypes.func.isRequired,
    changeSection:         PropTypes.func.isRequired,
    currentSection:        PropTypes.string.isRequired,
    logoCallback:          PropTypes.func,
    logoActive:            PropTypes.bool,
    resumeOnboarding:      PropTypes.func,
    sectionsBadges:        PropTypes.array
  };

  static openDeskPro() {
    window.open(`${window.DP_BASE_URL}goto/vendor-home`, '_blank');
  }

  constructor(props) {
    super(props);
    this.state = {
      ready: true
    };
  }

  getMenus = () => {
    const menus = [];
    if (this.props.canUseTicket()) {
      menus.push({
        className: 'tickets',
        label:     <FormattedMessage id="agent.general.tickets" />,
        link:      '/agent/#app.tickets',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tickets.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tickets_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUseChat()) {
      menus.push({
        className: 'chat',
        label:     <FormattedMessage id="agent.general.chats" />,
        link:      '/agent/#app.userchat',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/chat.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('chat_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUsePeople()) {
      menus.push({
        className: 'people',
        label:     <FormattedMessage id="agent.general.crm" />,
        link:      '/agent/#app.people',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/crm.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('people_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUseFeedback()) {
      menus.push({
        className: 'feedback',
        label:     <FormattedMessage id="agent.general.feedback" />,
        link:      '/agent/#app.feedback',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/feedback.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('feedback_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUsePublish()) {
      menus.push({
        className: 'publish',
        label:     <FormattedMessage id="agent.general.publish" />,
        link:      '/agent/#app.publish',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/publishing.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('publish_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUseTasks()) {
      menus.push({
        className: 'tasks',
        label:     <FormattedMessage id="agent.general.tasks" />,
        link:      '/agent/#app.tasks',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/tasks.svg`,
        callback:  () => {
          window.DeskPRO_Window.switchToSection('tasks_section');
          closeIframes();
        }
      });
    }
    if (this.props.canUseReports()) {
      menus.push({
        className: 'reports',
        label:     <FormattedMessage id="agent.general.reports" />,
        link:      '/agent/#reports:/',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/reports.svg`,
        callback:  () => {
          this.props.openReports();
        }
      });
    }
    if (window.DP_HAS_NEW_REPORTS && (this.props.canUseReports() || this.props.hasAccessToDashboards())) {
      menus.push({
        className: 'reports2',
        label:     `${<FormattedMessage id="agent.general.new" />} ${<FormattedMessage id="agent.general.reports" />}`,
        link:      '/agent/#r:/',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/reports.svg`,
        callback:  () => {
          this.props.openReports2();
        }
      });
    }
    if (this.props.canUseAdmin()) {
      menus.push({
        className: 'admin',
        label:     <FormattedMessage id="agent.general.admin" />,
        link:      '/agent/#admin:/',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/settings.svg`,
        callback:  () => {
          this.props.openAdmin();
        }
      });
    }
    if (this.props.canUseBilling()) {
      menus.push({
        className: 'billing',
        label:     <FormattedMessage id="agent.general.billing" />,
        link:      '/agent/#admin:/license',
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/billing.svg`,
        callback:  () => {
          this.props.openBilling();
        }
      });
    }
    if (this.props.canUsePortal()) {
      menus.push({
        className: 'portal',
        label:     <span><FormattedMessage id="agent.general.portal" /> <i className="icon external" /></span>,
        icon:      `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/portal.svg`,
        href:      window.DESKPRO_PORTAL_HOME,
        link:      window.DESKPRO_PORTAL_HOME
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
          link={menuItem.link}
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
    this.setState({ ready: false });
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
        className={classNames('sidebar-menu ui vertical menu', { ready: this.state.ready })}
        onMouseEnter={() => { this.setState({ ready: true }); }}
        onMouseLeave={() => { this.setState({ ready: false }); }}
      >
        <div className={classNames('logo', { active: logoActive })} onClick={this.clickLogo}>
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/logo.svg`} />
          <Isvg
            className="logo-text"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/sidebar/logoText.svg`}
          />
        </div>
        {this.getMenuItems()}
      </div>
    );
  }
}
export default SideBarContainer;
