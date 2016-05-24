import React, { Component, PropTypes } from 'react';
import { SectionHeader, NestedList } from '../../../../Common/Components/NavFrame';

export class StarsTab extends Component {
  static propTypes = {
    starsCount:  PropTypes.object.isRequired,
    onStarClick: PropTypes.func.isRequired
  };

  render() {
    const { starsCount, onStarClick } = this.props;

    return (
      <div>
        <SectionHeader>Stars</SectionHeader>

        <NestedList
          items={starsCount.toJS()}
          onClick={onStarClick}
        />
      </div>
    );
  }
}
