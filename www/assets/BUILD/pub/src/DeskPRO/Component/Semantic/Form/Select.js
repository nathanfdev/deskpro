import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { MenuItem } from '../Menu';

class Select extends React.Component {
  static propTypes = {
    filter:      PropTypes.bool,
    disabled:    PropTypes.bool,
    onChange:    PropTypes.func,
    name:        PropTypes.string,
    placeholder: PropTypes.string,
    value:       PropTypes.string,
    className:   PropTypes.string,
    title:       PropTypes.string,
    options:     PropTypes.oneOfType([
      PropTypes.array,
      PropTypes.object
    ]).isRequired
  };
  static defaultProps = {
    onChange() {},
    title: '',
  };

  constructor(props) {
    super(props);
    this.state = {
      value:         null,
      isOpen:        false,
      inputValue:    '',
      focusedIndex:  -1,
      focusedOption: null
    };
  }

  componentWillMount = () => {
    const { value } = this.props;
    if (value) {
      this.selectValueFromProps(value);
    }
  };

  componentWillReceiveProps = (newProps) => {
    if ((!this.state.value && newProps.value)
      || (this.state.value && (newProps.value !== this.state.value.value))) {
      this.selectValueFromProps(newProps.value);
    }
  };

  componentDidUpdate = () => {
    if (this.focusedOption && this.state.isOpen && !this.hasScrolledToOption) {
      this.menu.scrollTop      = this.focusedOption.node.offsetTop;
      this.hasScrolledToOption = true;
    } else if (!this.state.isOpen) {
      this.hasScrolledToOption = false;
    }
  };

  getInput = () => {
    if (this.props.filter) {
      return (<input
        className="search"
        autoComplete="off"
        ref={(c) => { this.filterInput = c; }}
        value={this.state.inputValue}
        onChange={this.updateFilter}
        onFocus={this.openSelect}
      />);
    }
    return null;
  };

  filterOptions = () => {
    const { inputValue } = this.state;
    return this.props.options
      .filter((option) => {
        if (inputValue) {
          if (option.text) {
            return option.text.toLowerCase().startsWith(inputValue.toLowerCase());
          }
          return option.label.toLowerCase().startsWith(inputValue.toLowerCase());
        }
        return true;
      });
  };

  handleKeyDown = (event) => {
    switch (event.keyCode) {
      case 9: // tab
        if (event.shiftKey || !this.state.isOpen) {
          return;
        }
        this.selectFocusedOption();
        return;
      case 13: // enter
        if (!this.state.isOpen) return;
        event.stopPropagation();
        this.selectFocusedOption();
        break;
      case 27: // escape
        if (this.state.isOpen) {
          this.closeSelect();
          event.stopPropagation();
        }
        break;
      case 38: // up
        this.focusPreviousOption();
        break;
      case 40: // down
        this.focusNextOption();
        break;
      default: return;
    }
    event.preventDefault();
  };

  focusNextOption = () => {
    this.focusAdjacentOption('next');
  };

  focusPreviousOption = () => {
    this.focusAdjacentOption('previous');
  };

  focusAdjacentOption = (dir) => {
    const { focusedOption } = this.state;
    const options = this.visibleOptions
      .map((option, index) => ({ option, index }))
      .filter(option => !option.option.disabled);
    this.hasScrolledToOption = false;
    if (!this.state.isOpen) {
      this.setState({
        isOpen:        true,
        inputValue:    '',
        focusedOption: focusedOption || options[dir === 'next' ? 0 : options.length - 1].option
      });
      return;
    }
    if (!options.length) return;
    let focusedIndex = -1;
    for (let i = 0; i < options.length; i += 1) {
      if (focusedOption === options[i].option) {
        focusedIndex = i;
        break;
      }
    }
    if (dir === 'next' && focusedIndex !== -1) {
      focusedIndex = (focusedIndex + 1) % options.length;
    } else if (dir === 'previous') {
      if (focusedIndex > 0) {
        focusedIndex -= 1;
      } else {
        focusedIndex = options.length - 1;
      }
    } else if (dir === 'start') {
      focusedIndex = 0;
    } else if (dir === 'end') {
      focusedIndex = options.length - 1;
    }

    if (focusedIndex === -1) {
      focusedIndex = 0;
    }

    this.setState({
      focusedIndex:  options[focusedIndex].index,
      focusedOption: options[focusedIndex].option
    });
  };

  selectFocusedOption = () => {
    this.selectValue(this.state.focusedOption);
    this.filterInput.blur();
  };

  selectValueFromProps = (value) => {
    const currentValue = this.props.options.find(option => option.value === value);
    if (currentValue) {
      this.setState({
        value: currentValue
      });
    }
  };

  toggleSelect = () => {
    const { isOpen } = this.state;
    this.setState({
      isOpen: !isOpen
    });
  };

  closeSelect = () => {
    this.setState({
      inputValue: '',
      isOpen:     false
    });
  };

  openSelect = () => {
    if (!this.state.isOpen) {
      this.setState({
        isOpen: true
      });
    }
  };

  selectValue = (value) => {
    if (this.props.onChange(value.value) !== false) {
      this.setState({
        value,
        inputValue: '',
        isOpen:     false
      });
    } else {
      this.setState({
        inputValue: '',
        isOpen:     false
      });
    }
  };

  updateFilter = (event) => {
    this.setState({
      inputValue:   event.target.value,
      focusedIndex: 0,
      isOpen:       true
    });
  };

  renderOptions = (options) => {
    const { focusedIndex } = this.state;
    return options.map((option, index) => {
      const active = (option === this.state.value);
      return (<MenuItem
        key={option.value}
        onClick={() => this.selectValue(option)}
        ref={(node) => {
          if (index === focusedIndex) {
            this.focusedOption = node;
          }
        }}
        className={classNames({ active, selected: index === focusedIndex })}
      >
        {option.label}
      </MenuItem>);
    });
  };

  render() {
    const { value, isOpen, inputValue } = this.state;
    const { placeholder, filter, name, className, disabled, title } = this.props;
    const text = value ? value.label : placeholder;
    const options = this.visibleOptions = this.filterOptions();

    const select = (<div
      className={classNames('ui selection dropdown', className, { active: isOpen, visible: isOpen, search: filter, disabled })}
      onClick={this.openSelect}
      onKeyDown={this.handleKeyDown}
      title={title}
    >
      <input ref={(c) => { this.input = c; }} type="hidden" name={name} value={this.props.value} />
      <i className="dropdown icon" />
      {this.getInput()}
      <div className={classNames('text', { default: !value, filtered: inputValue })}>
        {text}
      </div>
      <div className={classNames('menu transition', { visible: isOpen })} ref={(c) => { this.menu = c; }}>
        {this.renderOptions(options)}
      </div>
    </div>);
    if (isOpen) {
      return (
        <ClickOut onClickOut={this.closeSelect}>{select}</ClickOut>
      );
    }
    return select;
  }
}
export default Select;
