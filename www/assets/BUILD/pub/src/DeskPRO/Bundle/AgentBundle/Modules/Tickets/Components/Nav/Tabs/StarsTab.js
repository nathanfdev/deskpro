import React, { Component, PropTypes } from 'react';
import { SectionHeader, NestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class StarsTab extends Component {
  static propTypes = {
    starsCount: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <SectionHeader>Stars</SectionHeader>

        <NestedList
          items={this.props.starsCount.toJS()}
          onClick={() => alert(1)}
        />
      </div>
    );
  }
}
