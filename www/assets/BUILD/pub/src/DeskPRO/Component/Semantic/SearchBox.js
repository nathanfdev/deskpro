import PropTypes from 'prop-types';
import React from 'react';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder:  PropTypes.string,
    text:         PropTypes.string,
    onUserInput:  PropTypes.func,
    onFocus:      PropTypes.func,
    onBlur:       PropTypes.func,
    onClearInput: PropTypes.func,
    focusOnMount: PropTypes.bool,
    icon:         PropTypes.node
  };
  static defaultProps = {
    onUserInput() {},
    onFocus() {},
    onBlur() {},
    onClearInput() {},
    focusOnMount: false
  };

  componentDidMount() {
    if (this.props.focusOnMount) {
      this.textInput.focus();
    }
  }

  getIcon = () => {
    if (this.props.icon) {
      return this.props.icon;
    }
    return <i className="search icon" />;
  };

  handleChange = () => {
    this.props.onUserInput(
      this.textInput.value
    );
  };

  clearInput = () => {
    console.log('clear input');
    this.textInput.value = '';
    this.props.onUserInput(
      this.textInput.value
    );
    if (this.props.onClearInput) {
      this.props.onClearInput();
    }
  };

  render() {
    const { placeholder, text, onFocus, onBlur } = this.props;
    return (
      <div className="ui input left icon search">
        {this.getIcon()}
        <input
          type="search"
          placeholder={placeholder}
          ref={(c) => { this.textInput = c; }}
          onChange={this.handleChange}
          onFocus={onFocus}
          onBlur={onBlur}
          value={text}
          required="required"
        />
        <i onClick={this.clearInput} className="remove circle icon right" />
      </div>
    );
  }
}
export default SearchBox;
