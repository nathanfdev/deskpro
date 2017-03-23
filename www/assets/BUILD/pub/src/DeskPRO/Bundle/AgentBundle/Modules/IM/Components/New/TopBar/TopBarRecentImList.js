import React, { PropTypes } from 'react';
import ReactTooltip from 'react-tooltip';
import { DragDropContextProvider } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import Loader from 'react-loader';
import Immutable from 'immutable';
import {
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import classNames from 'classnames';
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
    hiddenChats:       PropTypes.object,
    startedByMeChats:  PropTypes.object
  };

  static defaultProps = {
    onHideChat(chat) {
      chatActions.hideChat(chat.get('id'), Date.now());
    },
    counts: {
      nested: {}
    },
    hiddenChats: {

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

  static filterByHidden(chat, hiddenChats) {
    const date = chat.get('date_last_message') || chat.get('date_created');
    return !hiddenChats[chat.get('id')] || hiddenChats[chat.get('id')] < (Date.parse(date));
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
    this.dropContext = document.getElementById('react_dp_agent_top_bar');
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
    const { hiddenChats, me, startedByMeChats } = props;
    let filtered = chats;

    if (hiddenChats || startedByMeChats) {
      filtered = filtered.filter((chat) => {
        if (!chat.get('date_last_message') && !((startedByMeChats && startedByMeChats.get(chat.get('id'))) || me.get('id') === chat.get('admin'))) {
          return false;
        }

        return TopBarRecentImList.filterByHidden(chat, hiddenChats);
      });
    }

    if (props.chats) {
      props.chats.filter(chat => TopBarRecentImList.filterByHidden(chat, hiddenChats)).forEach((chat) => {
        const order = filtered.getIn([chat.get('id'), 'order']) || props.imSettings.getIn(['chats_order', `${chat.get('id')}`]) || this.getNextOrder(filtered);
        filtered = filtered.set(chat.get('id'), chat.set('order', order));
      });
    }
    filtered = filtered.filter(chat => props.chats.has(chat.get('id')));

    const sorted = filtered.sort((a, b) => a.get('order') - b.get('order'));

    this.setState({ chats: sorted }, () => {
      this.updateOrder = this.updateOrder || (this.state.chats.size !== sorted.size);
      this.updateChatsOrder(props);
    });
  }

  getItems() {
    const { current, chats, imSettings } = this.props;
    let stateChats = this.state.chats.reverse().slice(0, this.state.slice).reverse();
    if (current.get('id') && !stateChats.has(current.get('id'))) {
      const chat = chats.get(current.get('id'));
      if (chat) { // I'm not sure why current chat could absent, but seems that Lauren somehow reached that
        const order = imSettings.getIn(['chats_order', `${chat.get('id')}`]);
        stateChats = stateChats.set(current.get('id'), chat.set('order', order || this.getNextOrder(stateChats)));
        stateChats = stateChats.sort((a, b) => a.get('order') - b.get('order'));
        stateChats = stateChats.reverse().slice(0, this.state.slice).reverse();
      }
    }

    return stateChats.map(chat => this.getItem(chat));
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

  renderAgent(chat) {
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
      <TopBarRecentImListItem {...this.getItemProps(chat)}>
        <PersonAvatar {...avatarProps} />
        {(active && agent.get('online')) ? <span className="agent-online" /> : null}


        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderDepartment(chat) {
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
      <TopBarRecentImListItem {...this.getItemProps(chat)}>
        <DepartmentAvatar {...avatarProps} />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderTeam(chat) {
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
      <TopBarRecentImListItem {...this.getItemProps(chat)}>
        <AgentTeamAvatar {...avatarProps} />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderEveryone(chat) {
    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)}>
        {AvatarHelper.renderEveryoneAvatar(this.state.tooltips ? this.getNotificationCount(chat) : null)}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </TopBarRecentImListItem>
    );
  }

  renderGroup(chat) {
    return (
      <TopBarRecentImListItem {...this.getItemProps(chat)}>
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
        <DragDropContextProvider backend={HTML5Backend} window={this.dropContext}>
          <div className={classNames(['im', 'recent', { empty: this.state.chats.size < 1 }])}>
            {this.getItems()}
            {this.props.children}
          </div>
        </DragDropContextProvider>
        <ReactTooltip delayShow={1000} id="tooltip-userphoto" effect="solid" place="top" className="im-tooltip" />
      </Loader>
    );
  }
}
