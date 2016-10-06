import React, { PropTypes } from 'react';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder: PropTypes.string,
    text:        PropTypes.string,
    onUserInput: PropTypes.func,
    onFocus:     PropTypes.func,
    onBlur:      PropTypes.func
  };
  static defaultProps = {
    onUserInput() {},
    onFocus() {},
    onBlur() {}
  };

  handleChange = () => {
    this.props.onUserInput(
      this.textInput.value
    );
  };

  clearInput = () => {
    this.textInput.value = '';
  };

  render() {
    const { placeholder, text, onFocus, onBlur } = this.props;
    return (
      <div className="ui input left icon search">
        <i className="search icon" />
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
