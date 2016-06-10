import React from 'react';
const { Component, PropTypes } = React;

import {
  SectionsPane, Section, SectionGroupedHeader
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from '../NestedList';
import { NavGroupingPopup } from '../NavGroupingPopup';

export class DownloadsTab extends Component {

  static propTypes = {
    downloads:                PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility:  PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  getAttachTarget = () => this.refs.downloads;

  render = () => {
    const { downloads, toggleGroupingVisibility, closeGroupingVisibility } = this.props;
    const toggle = toggleGroupingVisibility.bind(this);
    const close  = closeGroupingVisibility.bind(this);

    return (
      <SectionsPane>
        <Section>
          <SectionGroupedHeader label="Downloads" ref="downloads" count={downloads.get('count')} callback={toggle} />
          <NestedList content="downloads" items={downloads.get('nested').toJS()} />
          <NavGroupingPopup
            attachTo={this.getAttachTarget}
            content="downloads"
            visible={this.state.expanded}
            closeGroupingVisibility={close}
            groupedBy={downloads.get('grouped_by')}
          />
        </Section>
      </SectionsPane>
    );
  }
}
