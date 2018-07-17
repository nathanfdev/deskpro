import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder:  PropTypes.string,
    text:         PropTypes.string,
    className:    PropTypes.string,
    onUserInput:  PropTypes.func,
    onFocus:      PropTypes.func,
    onBlur:       PropTypes.func,
    onClearInput: PropTypes.func,
    focusOnMount: PropTypes.bool,
    icon:         PropTypes.node,
    children:     PropTypes.node,
    clear:        PropTypes.bool,
  };
  static defaultProps = {
    onUserInput() {},
    onFocus() {},
    onBlur() {},
    onClearInput() {},
    className:    '',
    focusOnMount: false,
    clear:        true,
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
    const { placeholder, text, onFocus, onBlur, children, className, clear } = this.props;
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
      <div className={classNames('ui input left icon search', className)}>
        {this.getIcon()}
        { childrenWithProps }
        { clear ?
          <i onClick={this.clearInput} className="remove circle icon right" />
          : null
        }
      </div>
    );
  }
}
export default SearchBox;
