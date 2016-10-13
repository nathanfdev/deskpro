import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Input } from 'DeskPRO/Component/Semantic/Form';
import { AvatarHelper } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

class GroupAddDrawer extends React.Component
{
  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    target:        PropTypes.object.isRequired,
    isOpen:        PropTypes.bool.isRequired,
    agentClick:    PropTypes.func,
    clickOut:      PropTypes.func,
    createGroup:   PropTypes.func,
    checkedAgents: PropTypes.object
  };

  static defaultProps = {
    agentClick() {

    },
    clickOut() {

    },
    createGroup() {

    },
    checkedAgents: {}
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
      groupName:          ''
    };
    this.agentClick  = this.agentClick.bind(this);
    this.createGroup = this.createGroup.bind(this);
    this.clickOut    = this.clickOut.bind(this);
    this.onChange    = this.onChange.bind(this);
  }

  onChange(event) {
    this.setState({ groupName: event.target.value });
  }

  getAgentsHeader() {
    return (
      <span>
        <span>Agents</span>
        <span className="selected counter">{this.state.checkedAgentsCount} selected</span>
      </span>
    );
  }

  agentClick(agent) {
    this.props.agentClick(agent);
    const alreadyChecked = !!this.state.checkedAgents[agent.get('id')];
    const newCheckedAgents = this.state.checkedAgents;
    newCheckedAgents[agent.get('id')] = !alreadyChecked;

    this.setState(
      {
        checkedAgents:      newCheckedAgents,
        checkedAgentsCount: GroupAddDrawer.recalculateChecked(this.state.checkedAgents)
      }
    );
  }

  createGroup() {
    this.props.createGroup(Object.keys(this.state.checkedAgents), this.state.groupName);
  }

  clickOut() {
    this.props.clickOut();
  }

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
    const { isOpen, target, agents } = this.props;

    return (
      <Detached
        isOpen={isOpen}
        positionTarget={target}
        positionMy="center-17 top-2"
      >
        <ClickOut onClickOut={this.clickOut}>
          <div className="ui popup im center bottom">
            <div className="header">Agent IM</div>
            <div className="im add group">
              <Segment vertical>
                <Header
                  level={5}
                  className="group add"
                  content={<span><i className="fa fa-users" />&nbsp;Create group</span>}
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
              <button className="ui create-group primary button" onClick={this.createGroup}>
                Create group
              </button>
            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}

export default GroupAddDrawer;
