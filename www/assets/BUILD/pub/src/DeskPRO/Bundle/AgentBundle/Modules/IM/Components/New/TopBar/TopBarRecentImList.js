import PropTypes from 'prop-types';
import React from 'react';
import ReactTooltip from 'react-tooltip';
import Loader from '@deskpro/react-loader';
import Immutable from 'immutable';
import {
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import classNames from 'classnames';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import ScrollArea from '@deskpro/react-scrollbar';
import { chooseColor, darkerColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { RecentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import TopBarRecentImListItem from './TopBarRecentImListItem';
import AvatarHelper from '../IMTabs/AvatarHelper';
import HeaderHelper from '../ChatWindow/HeaderHelper';
import * as chatActions from '../../../Actions/chatsActions';
import { getDepartmentAgents } from '../../../../Application/Actions/departmentActions';

export default class TopBarRecentImList extends RecentList {

  static propTypes = {
    chats:             PropTypes.object.isRequired,
    imSettings:        PropTypes.object.isRequired,
    children:          PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onRecentClick:     PropTypes.func.isRequired,
    updateChatsOrder:  PropTypes.func.isRequired,
    me:                PropTypes.object.isRequired,
    current:           PropTypes.object.isRequired,
    agents:            PropTypes.object.isRequired,
    people:            PropTypes.object.isRequired,
    departments:       PropTypes.object.isRequired,
    teams:             PropTypes.object.isRequired,
    recentLoaded:      PropTypes.bool.isRequired,
    teamsLoaded:       PropTypes.bool.isRequired,
    departmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:      PropTypes.bool.isRequired,
    counts:            PropTypes.object,
    onHideChat:        PropTypes.func,
    startedByMeChats:  PropTypes.object
  };

  static defaultProps = {
    onHideChat(chat) {
      chatActions.hideChat(chat.get('id'), Date.now());
    },
    counts: {
      nested: {}
    }
  };


  isLoaded(props) { // eslint-disable-line class-methods-use-this
    // it conflicts with @decorator when static
    return props.agentsLoaded && props.teamsLoaded && props.departmentsLoaded && props.recentLoaded;
  }

  getNextOrder(chats) {
    this.updateOrder = false;
    const order = chats.size > 0 ? chats.sort((a, b) => b.get('order') - a.get('order')).first().get('order') : 0;
    return order + 1;
  }

  constructor(props) {
    super(props);
    this.state = {
      chats:    Immutable.OrderedMap({}),
      slice:    chatActions.countSlice(),
      tooltips: true
    };
    this.resizeTimeout = null;
    this.updateOrder = true;
  }

  componentWillMount() {
    this.setState({ slice: chatActions.countSlice() });
    window.addEventListener('resize', this.resizeHandler);
  }

  resizeHandler = () => {
    if (!this.resizeTimeout) {
      this.resizeTimeout = setTimeout(
        () => {
          this.setState({ slice: chatActions.countSlice() });
          this.resizeTimeout = null;
        },
        33
      );
    }
  };

  updateChatsOrder = (props) => {
    if (this.updateOrder && this.isLoaded(props)) {
      this.updateOrder = false;
      props.updateChatsOrder(this.state.chats);
    }
  };

  componentWillReceiveProps(props) {
    const { chats } = this.state;
    const { me, startedByMeChats } = props;
    let filtered = chats;


    filtered = filtered.filter((chat) => {
      if (!chat.get('date_last_message') && !((startedByMeChats && startedByMeChats.get(chat.get('id'))) || me.get('id') === chat.get('admin'))) {
        return false;
      }

      return chat.get('is_pinned');
    });

    let propsChats = props.chats;

    if (propsChats) {
      propsChats = propsChats.filter(chat => chat.get('is_pinned'));
      propsChats.forEach((chat) => {
        const order = filtered.getIn([chat.get('id'), 'order']) || props.imSettings.getIn(['chats_order', `${chat.get('id')}`]) || this.getNextOrder(filtered);
        filtered = filtered.set(chat.get('id'), chat.set('order', order));
      });
    }
    filtered = filtered.filter(chat => propsChats.has(chat.get('id')));

    const sorted = filtered.sort((a, b) => a.get('order') - b.get('order'));

    this.setState({ chats: sorted }, () => {
      this.updateOrder = this.updateOrder || (this.state.chats.size !== sorted.size);
      this.updateChatsOrder(props);
    });
  }

  getItem(chat, draggable) {
    switch (chat.get('chat_type')) {
      case 'agent':
        return this.renderAgent(chat, draggable);
      case 'department':
        return this.renderDepartment(chat, draggable);
      case 'team':
        return this.renderTeam(chat, draggable);
      case 'group':
        return this.renderGroup(chat, draggable);
      case 'everyone':
        return this.renderEveryone(chat, draggable);
      default:
        return null;
    }
  }

  getItems(remained = false) {
    return this.calculateChats(remained).map(chat => this.getItem(chat, !remained));
  }

  calculateChats(remained = false) {
    let beginNum = 0;
    let endNum = this.state.slice;
    if (remained === true) {
      beginNum = this.state.slice;
      endNum = this.state.chats.size;
    }

    const slice = this.state.chats.reverse().slice(beginNum, endNum);

    return remained ? slice : slice.reverse();
  }

  getHeaderHelper(chat) {
    const { agents, people, departments, teams, me } = this.props;
    const props = { agents, people, departments, teams, me, current: chat };

    if (!this.headerHelper) {
      this.headerHelper = new HeaderHelper(props);
    } else {
      this.headerHelper.setProps(props);
    }

    return this.headerHelper;
  }

  renderNotificationsBalloon(chat) {
    const notificationCount = this.getNotificationCount(chat);
    return notificationCount ? <div className="ui knuckles label message-counter">{notificationCount}</div> : null;
  }

  renderRemoveButton(chat) { // eslint-disable-line class-methods-use-this
    return (
      <i
        className="close icon remove circle"
        onClick={(event) => {
          event.stopPropagation();
          this.props.onHideChat(chat);
        }
    } />);
  }

  getClassNames(chat) {
    return classNames('im wrapper', { current: chat.get('id') === this.props.current.get('id') && this.props.chating });
  }

  toggleTooltips = (tooltips = true) => {
    this.setState({ tooltips });
  };

  getItemProps(chat) {
    return {
      chat,
      className:      this.getClassNames(chat),
      onClick:        () => this.props.onRecentClick(chat.get('id')),
      swapHeads:      this.swapHeads,
      toggleTooltips: this.toggleTooltips
    };
  }

  renderAgent(chat, draggable = false) {
    const { me, agents, people } = this.props;
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== me.get('id')) {
        agentId = id;
        break;
      }
    }
    let agent = agents.get(agentId);
    let active = true;
    const person = people.get(agentId);
    if (!agent && !person) {
      return null;
    }

    if (!agent) {
      agent = person;
      active = false;
    }
    const className = ['ui avatar image im'];
    if (!active || !agent.get('online')) {
      className.push('offline');
    }

    const avatarProps = {
      color:       chooseColor(agent.get('id')),
      borderColor: darkerColor(agent.get('id')),
      person:      agent,
      size:        24,
      className:   classNames(className)
    };

    if (this.state.tooltips) {
      const header = this.getHeaderHelper(chat).getHeaderText(true);
      const notificationsCount = this.getNotificationCount(chat);
      avatarProps.title = `${header}. ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
      avatarProps.tooltipId = 'userphoto';
    }

    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)} draggable={draggable}>
        <PersonAvatar {...avatarProps} />
        {(active && agent.get('online')) ? <span className="agent-online" /> : null}


        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderDepartment(chat, draggable = false) {
    const department = this.props.departments.getIn(chat.get('departments', 0));

    const avatarProps = {
      department,
      size:      24,
      className: 'ui avatar image im'
    };

    if (this.state.tooltips) {
      const header = this.getHeaderHelper(chat).getHeaderText(true);
      const notificationsCount = this.getNotificationCount(chat);
      const participantsCount = getDepartmentAgents(department).size;
      avatarProps.title = `${header} (${participantsCount} participant${participantsCount === 1 ? '' : 's'}). ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
      avatarProps.tooltipId = 'userphoto';
    }

    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)} draggable={draggable}>
        <DepartmentAvatar {...avatarProps} />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderTeam(chat, draggable = false) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));

    const avatarProps = {
      agentTeam: team,
      size:      24,
      className: 'ui avatar image im'
    };

    if (this.state.tooltips) {
      const header = this.getHeaderHelper(chat).getHeaderText(true);
      const notificationsCount = this.getNotificationCount(chat);
      const participantsCount = team.get('agents').size;
      avatarProps.title = `${header} (${participantsCount} participant${participantsCount === 1 ? '' : 's'}). ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
      avatarProps.tooltipId = 'userphoto';
    }

    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)} draggable={draggable}>
        <AgentTeamAvatar {...avatarProps} />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  toggleOverflow = () => {
    this.setState({ overflowShown: !this.state.overflowShown });
  };

  closeOverflow = () => {
    this.setState({ overflowShown: false });
  };

  renderRemainedChats() {
    let size = this.state.chats.size - this.state.slice;
    const chats = this.calculateChats(true);
    if (size === 1) {
      return this.getItem(chats.first(), true);
    }
    size = size > 0 ? size : 0;
    size = size > 99 ? 99 : size;
    size = size > 9 ? size : `+${size}`;

    let containerHeight = (size * 30) + 30;
    let containerWidth = 44;
    if (containerHeight > 330) {
      containerHeight = 330;
      containerWidth = 52;
    }


    const overallCount = chats.reduce((carry, chat) => carry + this.getNotificationCount(chat), 0);

    return (size > 0 ?
      <span
        id="im-overflow"
        className="im wrapper overflow"
        onClick={this.toggleOverflow}
        ref={(c) => { this.overflowButton = c; }}
      >
        <span className={classNames('ui im avatar image overflow', { active: this.state.overflowShown })}>
          <span className="text ui avatar image im">{size}</span>
          <Detached
            positionMy="center-20 top"
            zIndex={99999}
            isOpen={this.state.overflowShown}
            positionTarget={this.overflowButton}
            {...this.props}
          >
            <ClickOut onClickOut={this.closeOverflow} ignoreNodes={['.im.recent .im.wrapper']}>
              <div
                style={{ height: `${containerHeight}px`, width: `${containerWidth}px` }}
                className={classNames('ui overflow popup im center bottom', { visible: this.state.overflowShown })}
              >
                <ScrollArea
                  className="dpscrollarea overflow-inner"
                  contentClassName="dpscrollarea overflow-content"
                  vertical
                >
                  {chats.map(chat => this.getItem(chat, false))}
                </ScrollArea>
              </div>
            </ClickOut>
          </Detached>
        </span>
        {overallCount ? <div className="ui knuckles label message-counter">{overallCount}</div> : null}
      </span> : null
    );
  }

  renderEveryone(chat, draggable = false) {
    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)} draggable={draggable}>
        {AvatarHelper.renderEveryoneAvatar(this.state.tooltips ? this.getNotificationCount(chat) : null)}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderGroup(chat, draggable = false) {
    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)} draggable={draggable}>
        {AvatarHelper.renderGroupAvatar(chat, this.state.tooltips ? this.getNotificationCount(chat) : false, null)}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  swapHeads = (dragId, hoverId, dragOrder, hoverOrder, updateOrder = false) => {
    const { chats } = this.state;
    this.setState({ chats: chats.setIn([dragId, 'order'], hoverOrder).setIn([hoverId, 'order'], dragOrder).sort((a, b) => a.get('order') - b.get('order')) },
      () => {
        this.updateOrder = updateOrder;
        this.updateChatsOrder(this.props);
      }
    );
  };

  render() {
    return (
      <Loader loaded={this.isLoaded(this.props)} opacity={0} width={3} scale={0.5} color="#4696dc">
        <div className={classNames(['im', 'recent', { empty: this.state.chats.size < 1 }])}>
          {this.renderRemainedChats()}
          {this.getItems()}
          {this.props.children}
        </div>
        <ReactTooltip delayShow={1000} id="tooltip-userphoto" effect="solid" place="top" className="im-tooltip" />
      </Loader>
    );
  }
}
