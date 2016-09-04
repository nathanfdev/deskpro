import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { AvatarHelper } from '../IMTabs/AvatarHelper';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

export class GroupAddDrawer extends React.Component
{
  static propTypes = {
    me:     PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    target: PropTypes.node.isRequired,
    isOpen: PropTypes.bool.isRequired
  };

  renderAgent(agent) {
    const classes = ['im', 'agent'];
    if (!agent.get('online')) {
      classes.push('offline');
    }
    return (
      <ListElement
        key={agent.get('id')}
        classes={classes}
        imageNode={AvatarHelper.renderAgentAvatar(agent)}
      >
        <div className="content agent add-in-group">
          <div className="header">{agent.get('name')}</div>
        </div>
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
        <ClickOut onClickOut={this.closePopup}>
          <div className="ui popup im center bottom">
            <div className="header">Agent IM</div>
            <div className="im add group">
              <Segment>
                <Header size={4} classes={['group-list']} content="im groups" />
                <List classes={['im', 'middle', 'aligned', 'selection']}>
                  {agents.map((agent) => this.renderAgent(agent))}
                </List>
              </Segment>

            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}
