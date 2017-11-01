import PropTypes from 'prop-types';
import React from 'react';

export class RadioListItem extends React.Component {

  static propTypes = {
    value:   PropTypes.any,
    checked: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: !!props.checked
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      checked: !!props.checked
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.checked !== state.checked;
  }

  render() {
    const { checked } = this.state;
    const className = `dpwd-radio-button${checked ? ' active' : ''}`;

    return (
      <a className={className}>
        <span className="dpwd-radio-button-disc"></span>
        <span className="radio-button-title">
          {this.props.value}
        </span>
      </a>
    );
  }
}
