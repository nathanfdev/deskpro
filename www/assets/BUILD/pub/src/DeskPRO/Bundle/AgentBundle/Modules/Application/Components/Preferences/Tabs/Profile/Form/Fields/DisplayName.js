import PropTypes from 'prop-types';
import React from 'react';

export class DisplayName extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: !!this.props.value
    };
  }

  onToggle = () => {
    if (this.props.value) {
      this.props.onChange(null);
    }

    this.setState({
      checked: !this.state.checked
    });
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  renderInput() {
    return (
      <div className="bucket-column">
        <input type="text"
          placeholder="Your display name"
          value={this.props.value}
          onChange={this.onChange}
        />
      </div>
    );
  }

  render() {
    return (
      <div>
        <div className="bucket short">
          <label className="simple-label">
            <input type="checkbox"
              checked={this.state.checked}
              onChange={this.onToggle}
            />

            Override default name?
          </label>
          <span className="small">(will be displayed to users instead of your real name.)</span>
        </div>
        {this.state.checked ? this.renderInput() : null}
      </div>
    );
  }
}
