import React, { PropTypes } from 'react';

export class Password extends React.Component {

  static propTypes = {
    value: PropTypes.string,
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
    const { value } = this.props;

    return (
      <div>
          <input type="password"
                 placeholder="Password"
                 value={value.first}
                 onChange={this.onChangeValue} />
          <input type="password"
                 placeholder="Confirm password"
                 value={value.second}
                 onChange={this.onChangeConfirmValue} />
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
