import React, { PropTypes } from 'react';

export class Email extends React.Component {

  static propTypes = {
    value: PropTypes.string.isRequired,
    onChangeValue: PropTypes.func.isRequired
  };

  render() {
    const { value, onChangeValue} = this.props;

    return (
      <div className="bucket-column">
        <input type="text" placeholder="Your email" value={value} onChange={onChangeValue} />
      </div>
    );
  }
}
