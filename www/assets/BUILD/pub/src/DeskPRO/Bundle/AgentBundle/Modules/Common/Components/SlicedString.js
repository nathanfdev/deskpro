import PropTypes from 'prop-types';
import React, { Component } from 'react';

export class SlicedString extends Component {

  static propTypes = {
    string: PropTypes.string.isRequired,
    length: PropTypes.number
  };

  render() {
    const { string, length = 40 } = this.props;
    let content = string.substr(0, length);
    if (string.length > length) {
      content += '...';
    }

    return (
      <span>
        {content}
      </span>
    );
  }

}
