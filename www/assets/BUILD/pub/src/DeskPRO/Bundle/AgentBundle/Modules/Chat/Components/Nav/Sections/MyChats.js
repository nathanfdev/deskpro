import React from 'react';
import {
  Section, SectionHeader
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import { NavGroupingPopup } from '../NavGroupingPopup';

const { Component, PropTypes } = React;

export class MyChats extends Component {
  static propTypes = {
    my: PropTypes.object.isRequired
  };

  /** @namespace this.refs */
  /** @namespace this.refs.mySection */

  componentWillMount() {
    this.state = { expanded: false };
  }

  close = () => {
    this.setState({ expanded: false });
  };

  toggle = (event) => {
    event.preventDefault();
    this.setState({ expanded: !this.state.expanded });
  };

  renderItem = (item, index) => {
    const groupBy = item.get('type');
    const group   = item.get('id');
    const count   = item.get('count');
    const label   = item.get('title') || '-';

    return (
      <ListItemContainer
        count={count}
        key={index}
        label={label}
        listOptions={{ navItem: { [groupBy]: group }, agent: 'me' }}
      />
    );
  };

  getAttachTarget = () => this.refs.mySection;

  render() {
    const { my } = this.props;

    return (
      <Section ref="mySection">
        <SectionHeader>
          My Chats
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" href="#" onClick={this.toggle}>
              <span>&nbsp;</span>
              <i className="fa fa-angle-down" />
            </a>
            <a className="list-counter active" href="#">{my.get('count')}</a>
          </div>
        </SectionHeader>

        <ul>
          {my.get('nested').entrySeq().map(([index, item]) => this.renderItem(item, index))}
        </ul>
        <NavGroupingPopup
          attachTo={this.getAttachTarget}
          content="my"
          visible={this.state.expanded}
          closeGroupingVisibility={this.close}
          groupedBy={my.get('grouped_by')}
        />
      </Section>
    );
  }
}
