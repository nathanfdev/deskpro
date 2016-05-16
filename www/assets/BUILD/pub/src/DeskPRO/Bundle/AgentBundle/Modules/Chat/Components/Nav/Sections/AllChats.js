import React, { Component, PropTypes } from 'react';
import {
  Section, SectionHeader
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from '../ListItemContainer';
import { NavGroupingPopup } from '../NavGroupingPopup';

export class AllChats extends Component {
  static propTypes = {
    all: PropTypes.object.isRequired
  };

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
    const label   = item.get('title');

    return (
      <ListItemContainer
        groupBy={groupBy}
        count={count}
        label={label}
        key={index}
        listOptions={{ navItem: { [groupBy]: group } }}
      />
    );
  };

  render() {
    const { all } = this.props;

    return (
      <Section ref="allSection">
        <SectionHeader>
          All Chats
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" href="#" onClick={this.toggle}>
              <span>&nbsp;</span>
              <i className="fa fa-angle-down" />
            </a>
            <a className="list-counter active" href="#">{all.get('count')}</a>
          </div>
        </SectionHeader>

        <ul>
          {all.get('nested').map((item, index) => this.renderItem(item, index))}
        </ul>
        <NavGroupingPopup
          attachTo={this.refs.allSection}
          content="all"
          visible={this.state.expanded}
          closeGroupingVisibility={this.close}
          groupedBy={all.get('grouped_by')}
        />
      </Section>
    );
  }
}
