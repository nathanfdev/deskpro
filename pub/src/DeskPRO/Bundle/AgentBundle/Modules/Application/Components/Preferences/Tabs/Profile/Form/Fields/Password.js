import React, { PropTypes } from 'react';

export class Password extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    confirmValue: PropTypes.string,
    onChangeValue: PropTypes.func.isRequired,
    onChangeConfirmValue: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

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
    const { value, confirmValue, onChangeValue, onChangeConfirmValue} = this.props;

    return (
      <div>
        <p>
          <input type="password" placeholder="Password" value={value} onChange={onChangeValue} />
        </p>
        <p>
          <input type="password" placeholder="Confirm password" value={confirmValue} onChange={onChangeConfirmValue} />
        </p>
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
