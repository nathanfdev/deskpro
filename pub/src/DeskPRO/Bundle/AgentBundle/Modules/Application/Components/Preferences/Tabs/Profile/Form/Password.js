import React, { PropTypes } from 'react';

export class Password extends React.Component {

  static propTypes = {
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired
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
    return (
      <div>
        <p>
          <input type="text" placeholder="Password" />
        </p>
        <p>
          <input type="text" placeholder="Confirm password" />
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
