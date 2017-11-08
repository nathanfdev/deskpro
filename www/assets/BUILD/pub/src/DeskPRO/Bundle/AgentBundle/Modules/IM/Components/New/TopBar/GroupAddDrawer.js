import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import Immutable from 'immutable';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Input } from 'DeskPRO/Component/Semantic/Form';
import { AvatarHelper } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

class GroupAddDrawer extends React.Component {
  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    target:        PropTypes.object.isRequired,
    isOpen:        PropTypes.bool.isRequired,
    agentClick:    PropTypes.func,
    clickOut:      PropTypes.func,
    createGroup:   PropTypes.func,
    updateGroup:   PropTypes.func,
    checkedAgents: PropTypes.object,
    editChat:      PropTypes.object
  };

  static defaultProps = {
    agentClick() {

    },
    clickOut() {

    },
    createGroup() {

    },
    updateGroup() {

    },
    checkedAgents: {},
    editChat:      Immutable.fromJS({})
  };

  static recalculateChecked(checked) {
    let count = 0;
    for (const key of Object.keys(checked)) {
      if (checked[key]) {
        count += 1;
      }
    }

    return count;
  }

  constructor(props) {
    super(props);
    this.state = {
      checkedAgents:      props.checkedAgents,
      checkedAgentsCount: GroupAddDrawer.recalculateChecked(props.checkedAgents),
      groupName:          '',
      edited:             false,
      startedSelecting:   false
    };
  }

  componentWillMount() {
    window.addEventListener('keyup', this.onEscape);
  }

  componentWillReceiveProps(props) {
    if (!this.state.startedSelecting) {
      this.setState({
        checkedAgents:      props.checkedAgents,
        checkedAgentsCount: GroupAddDrawer.recalculateChecked(props.checkedAgents),
        groupName:          props.editChat.get('name') && !this.state.edited ? props.editChat.get('name') : this.state.groupName
      });
    }
  }

  componentWillUnmount() {
    window.addEventListener('keyup', this.onEscape);
  }

  onEscape = (e) => {
    if (e.keyCode === 27 && this.props.isOpen) {
      this.clickOut();
    }
  };

  onChange = (value) => {
    this.setState({ groupName: value, edited: true });
  };

  getAgentsHeader() {
    return (
      <span>
        <span>Agents</span>
        <span className="selected counter">{this.state.checkedAgentsCount} selected</span>
      </span>
    );
  }

  getHeaderContent() {
    return (
      <span>
        <i className="fa fa-users" />
        &nbsp;{this.props.editChat.get('id') ? 'Edit group' : 'Create group'}
      </span>
    );
  }

  agentClick = (agent) => {
    this.props.agentClick(agent);
    const alreadyChecked = !!this.state.checkedAgents[agent.get('id')];
    const newCheckedAgents = this.state.checkedAgents;
    newCheckedAgents[agent.get('id')] = !alreadyChecked;

    this.setState(
      {
        startedSelecting:   true,
        checkedAgents:      newCheckedAgents,
        checkedAgentsCount: GroupAddDrawer.recalculateChecked(this.state.checkedAgents)
      }
    );
  };

  clearState(callback) {
    const clearState = {
      checkedAgents:      {},
      checkedAgentsCount: 0,
      groupName:          '',
      edited:             false
    };
    if (callback) {
      this.setState(clearState, callback);
    } else {
      this.setState(clearState);
    }
  }

  clickOut = () => {
    this.clearState(this.props.clickOut);
  };

  createGroup = () => {
    const agents = Immutable.fromJS(this.state.checkedAgents).filter(item => item).toJS();
    this.props.createGroup(Object.keys(agents), this.state.groupName);
    this.clearState();
  };

  updateGroup = () => {
    const agents = Immutable.fromJS(this.state.checkedAgents).filter(item => item).toJS();
    this.props.updateGroup(this.props.editChat.get('id'), Object.keys(agents), this.state.groupName);
    this.clearState();
  };

  renderAgent(agent) {
    if (agent.get('id') === this.props.me.get('id')) {
      return null;
    }
    const classes = [];
    if (!agent.get('online')) {
      classes.push('offline');
    }
    return (
      <ListElement
        key={agent.get('id')}
        className={classNames(classes)}
      >
        <Toggle checkbox active={!!this.state.checkedAgents[agent.get('id')]} onChange={() => this.agentClick(agent)}>
          {AvatarHelper.renderAgentAvatar(agent)}
          <div className="content agent add-in-group">
            <div className="header">{agent.get('name')}</div>
          </div>
        </Toggle>

      </ListElement>
    );
  }

  render() {
    const { isOpen, target, agents, editChat } = this.props;

    return (
      <Detached
        isOpen={isOpen}
        zIndex={99999}
        positionTarget={target}
        positionMy="center-17 top-2"
      >
        <ClickOut onClickOut={this.clickOut} ignoreNodes={['.im.recent .im.wrapper', '.icon.group.add']}>
          <div className="ui popup im center bottom">
            <div className="header">Agent IM</div>
            <div className="im add group">
              <Segment vertical>
                <Header
                  level={5}
                  className="group add"
                  content={this.getHeaderContent()}
                />
                <Header level={4} className="group name" content="Group name" />
                <Input name="groupName" value={this.state.groupName} id="groupName" onChange={this.onChange} />
                <Header level={4} className="group list" content={this.getAgentsHeader()} />
                <List className="im middle aligned selection agent">
                  <Scrollable vertical>
                    {agents.map(agent => this.renderAgent(agent))}
                  </Scrollable>
                </List>
              </Segment>
              <button className="ui create-group primary button" onClick={editChat.get('id') ? this.updateGroup : this.createGroup}>
                { editChat.get('id') ? 'Save' : 'Create group' }
              </button>
            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}

export default GroupAddDrawer;
