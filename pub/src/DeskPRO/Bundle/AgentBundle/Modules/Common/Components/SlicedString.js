import React, { Component, PropTypes } from 'react';

export class SlicedString extends Component {

  static propTypes = {
    string: PropTypes.string.isRequired,
    length: PropTypes.number.isRequired
  };

  render() {
    const { string, length = 40 } = this.props;
    let content = string.substr(0, 40);
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
