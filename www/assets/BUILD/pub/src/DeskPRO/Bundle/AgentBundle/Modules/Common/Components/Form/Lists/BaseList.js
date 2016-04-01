import React, { PropTypes } from 'react';
import { RadioList } from './RadioList';

export class BaseList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired
  };

  getKeyword() {
    return '';
  }

  renderLabel(value) {
    return value;
  }

  render() {
    return (
      <RadioList {...this.props} renderLabel={this.renderLabel}
                                 getKeyword={this.getKeyword}/>
    );
  }
}

