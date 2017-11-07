import PropTypes from 'prop-types';
import React from 'react';
import $ from 'jquery';

class BlurInput extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      originalValue: '',
      tempValue:     ''
    };
  }

  componentWillMount() {
    this.setState({
      originalValue: this.props.value || '',
      tempValue:     this.props.value || ''
    });
  }

  componentDidMount() {
    const $input = $(this.input);

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
      tempValue: event.currentTarget.value
    });
  };

  onBlur = () => {
    const { onChange } = this.props;
    const { originalValue, tempValue } = this.state;

    if (tempValue !== originalValue) {
      onChange(tempValue);
      this.setState({
        originalValue: tempValue
      });
    }
  };

  render() {
    return (
      <input
        ref={(c) => { this.input = c; }}
        {...this.props}
        value={this.state.tempValue}
        onChange={this.onChange}
      />
    );
  }
}

export default BlurInput;
