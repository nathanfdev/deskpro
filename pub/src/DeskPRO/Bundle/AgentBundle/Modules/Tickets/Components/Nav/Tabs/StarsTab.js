import React, { Component, PropTypes } from 'react';
import { SectionHeader, NestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class StarsTab extends Component {
  static propTypes = {
    starsCount: PropTypes.object.isRequired,
    starNames: PropTypes.object.isRequired
  };

  render() {
    const { starsCount, starNames } = this.props;

    return (
      <div>
        <SectionHeader>Stars</SectionHeader>

        <NestedList
          items={starsCount.toJS()}
          groups={starNames.toJS()}
          onClick={() => alert(1)}
        />
      </div>
    );
  }
}
