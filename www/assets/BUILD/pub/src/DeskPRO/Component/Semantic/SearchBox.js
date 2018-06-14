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
    icon:         PropTypes.node,
    children:     PropTypes.node,
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
    this.textInput.value = '';
    this.props.onUserInput(
      this.textInput.value
    );
    if (this.props.onClearInput) {
      this.props.onClearInput();
    }
  };

  render() {
    const { placeholder, text, onFocus, onBlur, children } = this.props;
    let childrenWithProps;
    const props = {
      placeholder
    };
    if (!children) {
      childrenWithProps = (<input
        type="search"
        ref={(c) => { this.textInput = c; }}
        onChange={this.handleChange}
        onFocus={onFocus}
        onBlur={onBlur}
        value={text}
        required="required"
        {...props}
      />);
    } else {
      childrenWithProps = React.Children.map(children, child =>
        React.cloneElement(child, props)
      );
    }
    return (
      <div className="ui input left icon search">
        {this.getIcon()}
        { childrenWithProps }
        <i onClick={this.clearInput} className="remove circle icon right" />
      </div>
    );
  }
}
export default SearchBox;
