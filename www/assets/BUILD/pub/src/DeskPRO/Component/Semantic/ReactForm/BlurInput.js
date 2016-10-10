import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';

class BlurInput extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      value: props.value || ''
    };
  }

  componentDidMount() {
    const $input = $(ReactDOM.findDOMNode(this.input));

    $($input).on('blur', this.onBlur);
    $($input).on('keydown', (event) => {
      const code = event.keyCode || event.which;

      if (code === 13) {
        event.preventDefault();
        this.onBlur();
      }
    });
  }

  onChange = (event) => {
    this.setState({
      value: event.currentTarget.value
    });
  };

  onBlur = () => {
    const { value, onChange } = this.props;

    if (this.state.value !== (value || '')) {
      onChange(this.state.value);
    }
  };

  render() {
    return (
      <input
        ref={(c) => { this.input = c; }}
        {...this.props}
        value={this.state.value}
        onChange={this.onChange}
      />
    );
  }
}

export default BlurInput;
