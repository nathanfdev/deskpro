import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import { NavGroupingPopup } from '../NavGroupingPopup';

export class AllChats extends Component {
  static propTypes = {
    all: PropTypes.object.isRequired
  };

  /** @namespace this.refs.allSection */

  componentWillMount() {
    this.state = { expanded: false };
  }

  getAttachTarget = () => this.refs.allSection;

  close = () => {
    this.setState({ expanded: false });
  };

  toggle = () => this.setState({ expanded: !this.state.expanded });

  renderItem = (item, index) => {
    const groupBy = item.get('type');
    const group   = item.get('id');
    const count   = item.get('count');
    const label   = item.get('title') || '-';

    return (
      <ListItemContainer
        label={label}
        key={index}
        content="all"
        listOptions={{ navItem: { [groupBy]: group } }}
      >
        <ListItem count={count} label={label} />
      </ListItemContainer>
    );
  };

  render() {
    const { all } = this.props;

    return (
      <Section ref="allSection">
        <SectionHeader callback={this.toggle} count={all.get('count')}>
          All Chats
        </SectionHeader>

        <ul>
          {all.get('nested').map((item, index) => this.renderItem(item, index))}
        </ul>
        <NavGroupingPopup
          attachTo={this.getAttachTarget}
          content="all"
          visible={this.state.expanded}
          closeGroupingVisibility={this.close}
          groupedBy={all.get('grouped_by')}
        />
      </Section>
    );
  }
}
