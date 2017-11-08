import PropTypes from 'prop-types';
import React from 'react';

export class Password extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

  onChangeValue = (event) => {
    this.props.onChange(event.target.value, this.props.value.second);
  };

  onChangeConfirmValue = (event) => {
    this.props.onChange(this.props.value.first, event.target.value);
  };

  open = () => {
    this.setState({
      expanded: true
    });
  };

  renderButton() {
    return (
      <a href="#" className="button button-secondary button-short" onClick={this.open}>Change Password</a>
    );
  }

  renderFields() {
    const { first, second } = this.props.value;

    return (
      <div>
        <input
          type="password"
          placeholder="Password"
          value={first}
          onChange={this.onChangeValue}
        />
        <input
          type="password"
          placeholder="Confirm password"
          value={second}
          onChange={this.onChangeConfirmValue}
        />
      </div>
    );
  }

  render() {
    return (
      <div className="bucket-column">
        {this.state.expanded ? this.renderFields() : this.renderButton()}
      </div>
    );
  }
}
